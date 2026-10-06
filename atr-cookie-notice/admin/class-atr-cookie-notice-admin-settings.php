<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin functionality for the plugin settings.
 *
 * This class handles the admin logic for the ATR Cookie Notice plugin settings.
 *
 * @link       https://atarimtr.co.il
 * @since      2.0.0
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/admin
 */

/**
 * The admin-facing functionality of the plugin settings.
 *
 * @since      2.0.0
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/admin
 */
class Atr_Cookie_Notice_Admin_Settings
{

    /**
     * The ID of this plugin.
     *
     * @since      2.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    2.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The slug of this plugin.
     *
     * @since      2.0.0
     * @access   private
     * @var      string    $plugin_slug    The slug of this plugin.
     */
    private $plugin_slug;

    /**
     * Plugin settings configuration.
     *
     * @var array
     */
    private $settings;

    /**
     * Plugin options.
     *
     * @var array
     */
    private $options;

    /**
     * Documentation directory path.
     *
     * @var string
     */
    private $docs_dir;


    /**
     * Fired during plugins_loaded (very very early),
     * so don't miss-use this, only actions and filters,
     * current ones speak for themselves.
     */
    public function __construct($plugin_name, $plugin_slug, $version)
    {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->docs_dir = plugin_dir_path(__FILE__) . 'docs/';
        // Initialise settings
        add_action('admin_init', array($this, 'init'));
        // Register admin-post handler for targeted cache purge
        add_action('admin_post_atr_cookie_notice_purge_caches', array($this, 'handle_purge_caches'));
        // Reset to defaults handler
        add_action('admin_post_atr_cookie_notice_reset_defaults', array($this, 'handle_reset_defaults'));
        // Reset styling only
        add_action('admin_post_atr_cookie_notice_reset_style', array($this, 'handle_reset_style'));

    }

    /**
     * Initialize settings
     * @return void
     */
    public function init()
    {
        // Always register settings, but only load options on our pages
        $this->settings = $this->settings_fields();
        $this->register_settings();
        
        // Only load options on our plugin settings or docs screens
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only usage for screen detection
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if ($page === $this->plugin_slug || $page === $this->plugin_slug . '_docs') {
            $this->options = $this->get_options();
        }
    }

    /**
     * Add settings page to admin menu
     * @return void
     */
    public function add_submenu_item()
    {
        // Register settings under WordPress "Settings" menu
        add_options_page(
            __('ATR Cookie Notice Settings', 'atr-cookie-notice'),
            __('ATR Cookie Notice', 'atr-cookie-notice'),
            'manage_options',
            $this->plugin_name,
            array($this, 'settings_page')
        );

        // Register documentation page under "Settings" as well
        add_submenu_page(
            'options-general.php',
            __('ATR Cookie Notice Documentation', 'atr-cookie-notice'),
            __('ATR Cookie Notice Documentation', 'atr-cookie-notice'),
            'manage_options',
            $this->plugin_name . '_docs',
            array($this, 'docs_page')
        );

    }

    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_action_links($links)
    {
        // Settings and Docs are under WordPress Settings menu (options-general.php)
        $settings_url = add_query_arg( 'page', $this->plugin_name, admin_url( 'options-general.php' ) );
        $docs_url     = add_query_arg( 'page', $this->plugin_name . '_docs', admin_url( 'options-general.php' ) );
        $links[]      = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'atr-cookie-notice' ) . '</a>';
        $links[]      = '<a href="' . esc_url( $docs_url ) . '">' . esc_html__( 'Docs', 'atr-cookie-notice' ) . '</a>';
        
        $links[] = '<a href="https://atarimtr.com" target="_blank" rel="noopener noreferrer">More plugins by Yehuda Tiram (English)</a>';
        $links[] = '<a href="https://atarimtr.co.il" target="_blank" rel="noopener noreferrer">More plugins by Yehuda Tiram (Hebrew)</a>';
        return $links;
    }

    /**
     * Render simple Markdown to HTML for docs view (basic headings, code blocks, lists)
     */
    private function render_markdown_basic($markdown)
    {
        // Code fences
        $html = preg_replace('/```([\s\S]*?)```/m', '<pre><code>$1</code></pre>', $markdown);
        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        // Headings ###, ##, #
        $html = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $html);
        // Bold and italics
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        // Lists
        $lines = explode("\n", $html);
        $out = '';
        $in_ul = false;
        foreach ($lines as $line) {
            if (preg_match('/^\s*[-*]\s+(.+)/', $line, $m)) {
                if (! $in_ul) { $out .= '<ul>'; $in_ul = true; }
                // Allow basic inline HTML like <code>, <strong>, <em>
                $out .= '<li>' . wp_kses_post($m[1]) . '</li>';
            } else {
                if ($in_ul) { $out .= '</ul>'; $in_ul = false; }
                $out .= '<p>' . wp_kses_post($line) . '</p>';
            }
        }
        if ($in_ul) { $out .= '</ul>'; }
        return $out;
    }

    /**
     * Get privacy policy content based on language setting
     */
    private function get_privacy_policy_content()
    {
        // Detect admin user's locale
        $user_locale = get_user_locale();
        $language = (strpos($user_locale, 'he') === 0) ? 'he' : 'en';
        
        if ($language === 'he') {
            $privacy_file = $this->docs_dir . 'privacy-policy-he.md';
        } else {
            $privacy_file = $this->docs_dir . 'privacy-policy.md';
        }
        $content = $this->read_local_doc_safely($privacy_file);
        return $content !== false ? $content : __('Privacy policy not found.', 'atr-cookie-notice');
    }

    /**
     * Docs page renderer: reads local Markdown docs and displays nicely
     */
    public function docs_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
        }
        // Detect admin user's locale
        $user_locale = get_user_locale();
        $language = (strpos($user_locale, 'he') === 0) ? 'he' : 'en';
        $language_name = ($language === 'he') ? __('Hebrew', 'atr-cookie-notice') : __('English', 'atr-cookie-notice');
        
        // Load documentation based on admin user's language
        if ($language === 'he') {
            $plugin_info = $this->docs_dir . 'PLUGIN-INFO-he.md';
        } else {
            $plugin_info = $this->docs_dir . 'PLUGIN-INFO.md';
        }
        $md1 = $this->read_local_doc_safely($plugin_info);
        $md1 = ($md1 !== false) ? $md1 : __('Plugin information not found.', 'atr-cookie-notice');
        $html1 = $this->render_markdown_basic($md1);
        
        // Get privacy policy content based on admin user's language
        $privacy_content = $this->get_privacy_policy_content();
        $html2 = $privacy_content ? $this->render_markdown_basic($privacy_content) : '';
        
        // Get testing guide content based on admin user's language
        if ($language === 'he') {
            $testing_guide = $this->docs_dir . 'TESTING-GUIDE-he.md';
        } else {
            $testing_guide = $this->docs_dir . 'TESTING-GUIDE.md';
        }
        $testing_content = $this->read_local_doc_safely($testing_guide);
        $testing_content = ($testing_content !== false) ? $testing_content : __('Testing Guide not found.', 'atr-cookie-notice');
        $html3 = $testing_content ? $this->render_markdown_basic($testing_content) : '';
?>
        <div class="wrap atr-scb-docs">
            <h1><?php esc_html_e('Cookie Notice — Documentation', 'atr-cookie-notice'); ?></h1>
            
            <div class="atr-scb-tabs">
                <ul class="atr-scb-tab-nav">
                    <li><a href="#documentation" class="atr-scb-tab-link active"><?php esc_html_e('Documentation', 'atr-cookie-notice'); ?></a></li>
                    <?php if ($html2) : ?>
                    <li><a href="#privacy-policy" class="atr-scb-tab-link"><?php esc_html_e('Privacy Policy', 'atr-cookie-notice'); ?> (<?php echo esc_html($language_name); ?>)</a></li>
                    <?php endif; ?>
                    <?php if ($html3) : ?>
                    <li><a href="#testing-guide" class="atr-scb-tab-link"><?php esc_html_e('Testing Guide', 'atr-cookie-notice'); ?></a></li>
                    <?php endif; ?>
                </ul>
                
                <div id="documentation" class="atr-scb-tab-content active">
                    <div class="card">
                        <?php echo wp_kses_post( $html1 ); ?>
                    </div>
                </div>
                
                <?php if ($html2) : ?>
                <div id="privacy-policy" class="atr-scb-tab-content">
                    <p><em><?php esc_html_e('Language can be changed in the Localization settings.', 'atr-cookie-notice'); ?></em></p>
                    <div class="card">
                        <?php echo wp_kses_post( $html2 ); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($html3) : ?>
                <div id="testing-guide" class="atr-scb-tab-content">
                    <div class="card">
                        <?php echo wp_kses_post( $html3 ); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }


    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields()
    {
        $settings['general'] = array(
            'title'                    => __('General Settings', 'atr-cookie-notice'),
            'description'            => __('Configure the basic behavior of the cookie notice.', 'atr-cookie-notice'),
            'fields'                => array(
                array(
                    'id' => 'enable_banner',
                    'label' => __('Enable Cookie Banner', 'atr-cookie-notice'),
                    'description' => __('Show the cookie notice to visitors.', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => 'on',
                ),
                array(
                    'id' => 'banner_mode',
                    'label' => __('Consent Mode', 'atr-cookie-notice'),
                    'description' => __('Choose between full consent (blocks non-essential until consent) or simple informational notice.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        'full' => __('Full (block non-essential until consent)', 'atr-cookie-notice'),
                        'simple' => __('Simple (informational only, no blocking)', 'atr-cookie-notice'),
                    ),
                    'default' => 'full',
                ),
                array(
                    'id' => 'banner_position',
                    'label' => __('Banner Position', 'atr-cookie-notice'),
                    'description' => __('Choose where the banner appears on the page.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        'bottom' => __('Bottom', 'atr-cookie-notice'),
                        'top' => __('Top', 'atr-cookie-notice'),
                        'overlay' => __('Overlay (Center)', 'atr-cookie-notice'),
                    ),
                    'default' => 'bottom',
                ),
                array(
                    'id' => 'auto_hide_delay',
                    'label' => __('Auto-hide Delay (seconds)', 'atr-cookie-notice'),
                    'description' => __('Automatically hide the banner after this many seconds (0 = no auto-hide).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '0',
                    'placeholder' => '0',
                ),
            )
        );

        $settings['cookies'] = array(
            'title'                    => __('Cookie Categories', 'atr-cookie-notice'),
            'description'            => __('Configure which cookie categories to show and their default states.', 'atr-cookie-notice'),
            'fields'                => array(
                array(
                    'id' => 'show_analytics_cookies',
                    'label' => __('Show Analytics Cookies', 'atr-cookie-notice'),
                    'description' => __('Display the analytics cookies option in the banner.', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => 'on',
                ),
                array(
                    'id' => 'show_marketing_cookies',
                    'label' => __('Show Marketing Cookies', 'atr-cookie-notice'),
                    'description' => __('Display the marketing cookies option in the banner.', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => 'on',
                ),
                array(
                    'id' => 'analytics_cookies_default',
                    'label' => __('Analytics Cookies Default', 'atr-cookie-notice'),
                    'description' => __('Default state for analytics cookies (checked = enabled by default).', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => '',
                ),
                array(
                    'id' => 'marketing_cookies_default',
                    'label' => __('Marketing Cookies Default', 'atr-cookie-notice'),
                    'description' => __('Default state for marketing cookies (checked = enabled by default).', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => '',
                ),
            )
        );

        $settings['styling'] = array(
            'title'                    => __('Styling & Appearance', 'atr-cookie-notice'),
            'description'            => __('Customize the appearance of the cookie notice.', 'atr-cookie-notice'),
            'fields'                => array(
                array(
                    'id' => 'primary_color',
                    'label' => __('Primary Color', 'atr-cookie-notice'),
                    'description' => __('Main color for buttons and highlights (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#0073aa',
                    'placeholder' => '#0073aa',
                ),
                array(
                    'id' => 'secondary_color',
                    'label' => __('Secondary Color', 'atr-cookie-notice'),
                    'description' => __('Secondary/accent color for secondary elements (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#666666',
                    'placeholder' => '#666666',
                ),
                array(
                    'id' => 'text_color',
                    'label' => __('Text Color', 'atr-cookie-notice'),
                    'description' => __('Color for text content (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#333333',
                    'placeholder' => '#333333',
                ),
                array(
                    'id' => 'link_color',
                    'label' => __('Link Color', 'atr-cookie-notice'),
                    'description' => __('Color for links (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#0073aa',
                    'placeholder' => '#0073aa',
                ),
                array(
                    'id' => 'background_color',
                    'label' => __('Background Color', 'atr-cookie-notice'),
                    'description' => __('Background color for the banner (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#ffffff',
                    'placeholder' => '#ffffff',
                ),
                // Typography
                array(
                    'id' => 'font_family',
                    'label' => __('Font Family', 'atr-cookie-notice'),
                    'description' => __('Custom font-family stack (e.g., system-ui, -apple-system, Segoe UI, Roboto, sans-serif).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                    'placeholder' => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                ),
                array(
                    'id' => 'base_font_size',
                    'label' => __('Base Font Size (px)', 'atr-cookie-notice'),
                    'description' => __('Base font size for the banner (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '14',
                    'placeholder' => '14',
                ),
                array(
                    'id' => 'font_weight',
                    'label' => __('Base Font Weight', 'atr-cookie-notice'),
                    'description' => __('Select base font weight.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        '400' => '400',
                        '500' => '500',
                        '600' => '600',
                        '700' => '700',
                    ),
                    'default' => '400',
                ),
                array(
                    'id' => 'button_font_weight',
                    'label' => __('Button Font Weight', 'atr-cookie-notice'),
                    'description' => __('Select button font weight.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        '400' => '400',
                        '500' => '500',
                        '600' => '600',
                        '700' => '700',
                    ),
                    'default' => '600',
                ),
                array(
                    'id' => 'uppercase_buttons',
                    'label' => __('Uppercase Buttons', 'atr-cookie-notice'),
                    'description' => __('Render button labels in uppercase.', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => '',
                ),
                // Buttons color overrides
                array(
                    'id' => 'primary_button_bg_color',
                    'label' => __('Primary Button BG', 'atr-cookie-notice'),
                    'description' => __('Background color for the primary button (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => '#0b74de',
                ),
                array(
                    'id' => 'primary_button_text_color',
                    'label' => __('Primary Button Text', 'atr-cookie-notice'),
                    'description' => __('Text color for the primary button (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => '#ffffff',
                ),
                array(
                    'id' => 'secondary_button_bg_color',
                    'label' => __('Secondary Button BG', 'atr-cookie-notice'),
                    'description' => __('Background color for the secondary button (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => '#f7f7f7',
                ),
                array(
                    'id' => 'secondary_button_text_color',
                    'label' => __('Secondary Button Text', 'atr-cookie-notice'),
                    'description' => __('Text color for the secondary button (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => '#333333',
                ),
                // Layout & spacing
                array(
                    'id' => 'border_radius',
                    'label' => __('Border Radius (px)', 'atr-cookie-notice'),
                    'description' => __('Corner radius for banner and buttons (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '8',
                    'placeholder' => '8',
                ),
                array(
                    'id' => 'banner_max_width',
                    'label' => __('Banner Max Width (px)', 'atr-cookie-notice'),
                    'description' => __('Maximum width for the banner (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '420',
                    'placeholder' => '420',
                ),
                array(
                    'id' => 'modal_max_width',
                    'label' => __('Modal Max Width (px)', 'atr-cookie-notice'),
                    'description' => __('Maximum width for the modal container (Full mode).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '420',
                    'placeholder' => '420',
                ),
                array(
                    'id' => 'container_padding_y',
                    'label' => __('Container Padding Y (px)', 'atr-cookie-notice'),
                    'description' => __('Vertical padding inside the banner (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '16',
                    'placeholder' => '16',
                ),
                array(
                    'id' => 'container_padding_x',
                    'label' => __('Container Padding X (px)', 'atr-cookie-notice'),
                    'description' => __('Horizontal padding inside the banner (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '20',
                    'placeholder' => '20',
                ),
                array(
                    'id' => 'gap_between_elements',
                    'label' => __('Gap Between Elements (px)', 'atr-cookie-notice'),
                    'description' => __('Space between text, buttons and controls (in pixels).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '12',
                    'placeholder' => '12',
                ),
                array(
                    'id' => 'layout_direction',
                    'label' => __('Layout Direction', 'atr-cookie-notice'),
                    'description' => __('Stack elements vertically (column) or horizontally (row).', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        'column' => __('Column', 'atr-cookie-notice'),
                        'row' => __('Row', 'atr-cookie-notice'),
                    ),
                    'default' => 'column',
                ),
                array(
                    'id' => 'content_align',
                    'label' => __('Content Alignment', 'atr-cookie-notice'),
                    'description' => __('Align content left, center, or right.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        'left' => __('Left', 'atr-cookie-notice'),
                        'center' => __('Center', 'atr-cookie-notice'),
                        'right' => __('Right', 'atr-cookie-notice'),
                    ),
                    'default' => 'left',
                ),
                // Overlay & elevation
                array(
                    'id' => 'overlay_color',
                    'label' => __('Overlay Color', 'atr-cookie-notice'),
                    'description' => __('Overlay base color (hex code).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '#000000',
                    'placeholder' => '#000000',
                ),
                array(
                    'id' => 'overlay_opacity',
                    'label' => __('Overlay Opacity (0–1)', 'atr-cookie-notice'),
                    'description' => __('Opacity for overlay background (0 to 1).', 'atr-cookie-notice'),
                    'type' => 'text',
                    'default' => '0.35',
                    'placeholder' => '0.35',
                ),
                array(
                    'id' => 'z_index',
                    'label' => __('Banner Z-Index', 'atr-cookie-notice'),
                    'description' => __('Z-index for the banner (integer).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '9999',
                    'placeholder' => '9999',
                ),
                array(
                    'id' => 'shadow_preset',
                    'label' => __('Shadow Preset', 'atr-cookie-notice'),
                    'description' => __('Preset for modal/banner shadow.', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        'none' => __('None', 'atr-cookie-notice'),
                        'sm'   => __('Small', 'atr-cookie-notice'),
                        'md'   => __('Medium', 'atr-cookie-notice'),
                        'lg'   => __('Large', 'atr-cookie-notice'),
                    ),
                    'default' => 'md',
                ),
                array(
                    'id' => 'custom_css',
                    'label' => __('Custom CSS', 'atr-cookie-notice'),
                    'description' => __('Additional CSS rules to customize the banner appearance.', 'atr-cookie-notice'),
                    'type' => 'textarea',
                    'default' => '',
                    'placeholder' => '/* Add your custom CSS here */',
                ),
            )
        );


        $settings['advanced'] = array(
            'title'                    => __('Advanced Options', 'atr-cookie-notice'),
            'description'            => __('Advanced configuration options for developers.', 'atr-cookie-notice'),
            'fields'                => array(
                array(
                    'id' => 'design_preset',
                    'label' => __('Design Preset', 'atr-cookie-notice'),
                    'description' => __('Quickly apply a preset (applies on Save).', 'atr-cookie-notice'),
                    'type' => 'select',
                    'options' => array(
                        ''         => __('None', 'atr-cookie-notice'),
                        'light'    => __('Light', 'atr-cookie-notice'),
                        'dark'     => __('Dark', 'atr-cookie-notice'),
                        'minimal'  => __('Minimal', 'atr-cookie-notice'),
                        'contrast' => __('High Contrast', 'atr-cookie-notice'),
                    ),
                    'default' => '',
                ),
                array(
                    'id' => 'force_override_theme_styles',
                    'label' => __('Force override theme styles', 'atr-cookie-notice'),
                    'description' => __('When enabled, the banner uses high-specificity rules and !important to win against aggressive theme/optimizer CSS.', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => '',
                ),
                array(
                    'id' => 'enable_debug',
                    'label' => __('Enable Debug Mode', 'atr-cookie-notice'),
                    'description' => __('Show debug information in browser console (for development only).', 'atr-cookie-notice'),
                    'type' => 'checkbox',
                    'default' => '',
                ),
                array(
                    'id' => 'cookie_expiry_days',
                    'label' => __('Cookie Expiry (days)', 'atr-cookie-notice'),
                    'description' => __('How long to remember user consent (in days).', 'atr-cookie-notice'),
                    'type' => 'number',
                    'default' => '365',
                    'placeholder' => '365',
                ),
                // Global Form Integration removed
            )
        );

        // Content customization (safe HTML with tokens)
        $settings['content'] = array(
            'title'       => __('Content', 'atr-cookie-notice'),
            'description' => __('Customize the text and primary actions shown in the banner. Leave empty to use the default (translated) content. Allowed tags: a, button, strong, em, span, br, p. Allowed attributes: href, rel, target, role, class, id, type. Tokens: {site_name}, {privacy_url}, {privacy_link}. Buttons: [ok_button] (simple), [privacy_link] (simple), [accept_all_button], [reject_button], [preferences_button] (full).', 'atr-cookie-notice'),
            'fields'      => array(
                array(
                    'id'          => 'simple_text_html',
                    'label'       => __('Simple Mode — Text HTML', 'atr-cookie-notice'),
                    'description' => __('Banner description area (inside .scb-text). Tokens supported: {site_name}.', 'atr-cookie-notice'),
                    'type'        => 'textarea_html',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'simple_actions_html',
                    'label'       => __('Simple Mode — Actions HTML', 'atr-cookie-notice'),
                    'description' => __('Primary actions area (inside .scb-actions). Tokens supported: {privacy_url}. Buttons: [ok_button], [privacy_link]. If required IDs are missing, defaults will be appended.', 'atr-cookie-notice'),
                    'type'        => 'textarea_html',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'full_text_html',
                    'label'       => __('Full Mode — Text HTML', 'atr-cookie-notice'),
                    'description' => __('Banner description area (inside .scb-text). Tokens supported: {site_name}.', 'atr-cookie-notice'),
                    'type'        => 'textarea_html',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'full_actions_html',
                    'label'       => __('Full Mode — Actions HTML', 'atr-cookie-notice'),
                    'description' => __('Primary actions area (inside .scb-actions). Tokens supported: {privacy_url}. Buttons: [accept_all_button], [reject_button], [preferences_button]. If required IDs are missing, defaults will be appended.', 'atr-cookie-notice'),
                    'type'        => 'textarea_html',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'full_footer_html',
                    'label'       => __('Full Mode — Footer HTML', 'atr-cookie-notice'),
                    'description' => __('Footer area (inside .scb-footer). Tokens supported: {privacy_url}, {privacy_link}. {privacy_link} renders a full anchor with the Privacy Policy page title. Leave empty to use the default Privacy Policy link.', 'atr-cookie-notice'),
                    'type'        => 'textarea_html',
                    'default'     => '',
                    'placeholder' => '',
                ),
            ),
        );

        $settings['tools'] = array(
            'title'       => __('Tools', 'atr-cookie-notice'),
            'description' => __('Use the Tools block at the top for cache purge and reset styling. This tab is kept for compatibility; other plugins may add fields here via filter.', 'atr-cookie-notice'),
            'fields'      => array(),
        );

        $settings = apply_filters('atr_scb_settings_fields', $settings);

        return $settings;
    }

    /**
     * Options getter
     * @return array Options, either saved or default ones.
     */
    public function get_options()
    {
        $options = get_option($this->plugin_slug);

        if (! $options && is_array($this->settings)) {
            $options = [];

            foreach ($this->settings as $section => $data) {
                foreach ($data['fields'] as $field) {
                    // only apply a default if the field actually defines one
                    if (array_key_exists('default', $field)) {
                        $options[$field['id']] = $field['default'];
                    }
                }
            }

            add_option($this->plugin_slug, $options);
        } elseif ($options && is_array($this->settings)) {
            $changed = false;

            foreach ($this->settings as $section => $data) {
                foreach ($data['fields'] as $field) {
                    if (! array_key_exists($field['id'], $options)) {
                        if (array_key_exists('default', $field)) {
                            $options[$field['id']] = $field['default'];
                            $changed = true;
                        }
                        // else: no default to apply, leave it out
                    }
                }
            }

            if ($changed) {
                update_option($this->plugin_slug, $options);
            }
        }

        return $options;
    }

    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings()
    {
        if (is_array($this->settings)) {

            register_setting($this->plugin_slug . '_options', $this->plugin_slug, array($this, 'validate_fields'));

            foreach ($this->settings as $section => $data) {

                add_settings_section($section, $data['title'], array($this, 'settings_section'), $this->plugin_slug);

                foreach ($data['fields'] as $field) {

                    // Add field to page
                    add_settings_field($field['id'], $field['label'], array($this, 'display_field'), $this->plugin_slug, $section, array('field' => $field));
                }
            }
        }
    }

    public function settings_section($section)
    {
        $sec_id = isset($section['id']) ? sanitize_key($section['id']) : '';
        $desc = isset($this->settings[$sec_id]['description']) ? $this->settings[$sec_id]['description'] : '';
        $html = '<p> ' . esc_html($desc) . '</p>' . "\n";
        echo wp_kses($html, $this->get_allowed_html());
    }

    /**
     * Generate HTML for displaying fields
     * @param  array $args Field data
     * @return void
     */
    public function display_field($args)
    {

        $field = $args['field'];

        $html = '';

        $option_name = $this->plugin_slug . "[" . $field['id'] . "]";

        $data = (isset($this->options[$field['id']])) ? $this->options[$field['id']] : '';
        
        // If options not loaded yet, get them
        if (!isset($this->options)) {
            $this->options = $this->get_options();
            $data = (isset($this->options[$field['id']])) ? $this->options[$field['id']] : '';
        }

        switch ($field['type']) {

            case 'text':
            case 'password':
            case 'number':
                $input_class = '';
                // Mark known color fields for WP color picker
                $color_ids = array(
                    'primary_color','secondary_color','text_color','background_color','link_color',
                    'primary_button_bg_color','primary_button_text_color','secondary_button_bg_color','secondary_button_text_color',
                    'overlay_color'
                );
                if (in_array($field['id'], $color_ids, true) && $field['type'] === 'text') {
                    $input_class = ' class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . ' color-picker"';
                } else {
                    $input_class = ' class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '"';
                }
                $default_attr = isset($field['default']) ? ' data-default-color="' . esc_attr($field['default']) . '"' : '';
                $html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . esc_attr($data) . '"' . $input_class . $default_attr . '/>' . "\n";
                break;
            case 'text_secret':
                $html .= '<input type="password" id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="" autocomplete="new-password"/>' . "\n";
                break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" rows="5" cols="50" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '">' . esc_textarea($data) . '</textarea><br/>' . "\n";
                break;
            case 'textarea_html':
                $html .= '<textarea id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" rows="6" cols="60" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '">' . esc_textarea($data) . '</textarea><br/>' . "\n";
                break;

            case 'checkbox':
                $checked = '';
                if ($data && 'on' == $data) {
                    $checked = 'checked="checked"';
                }
                // Add a hidden field to ensure a value is sent even when unchecked
                $html .= '<input type="hidden" name="' . esc_attr($option_name) . '" value="off" />';
                $html .= '<input id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . '/>' . "\n";
                break;

            case 'checkbox_multi':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if (is_array($data) && in_array($k, $data)) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . esc_html($v) . '</label></br> ';
                }
                break;

            case 'radio':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if ($k == $data) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . esc_html($v) . '</label> ';
                }
                break;

            case 'select':
                $html .= '<select name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if ($k == $data) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . esc_html($v) . '</option>';
                }
                $html .= '</select> ';
                break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" class="atr-scb-field atr-scb-field-' . esc_attr($field['id']) . '" multiple="multiple">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if (in_array($k, $data)) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . esc_html($v) . '</option> ';
                }
                $html .= '</select> ';
                break;

            case 'button':
                // Secure admin-post form that triggers purge
                $action_url = admin_url('admin-post.php');
                $html .= '<form method="post" action="' . esc_url($action_url) . '" style="display:inline-block">';
                $html .= '<input type="hidden" name="action" value="atr_cookie_notice_purge_caches" />';
                $html .= wp_nonce_field('atr_cookie_notice_purge_caches', '_wpnonce', true, false);
                $btn_label = isset($field['button_label']) ? $field['button_label'] : __('Purge caches and refresh assets', 'atr-cookie-notice');
                $html .= '<button type="submit" class="button button-secondary">' . esc_html($btn_label) . '</button>';
                $html .= '</form>';
                break;
        }

        switch ($field['type']) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . esc_html( $field['description'] ) . '</span>';
                break;

            default:
                $html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . esc_html( $field['description'] ) . '</span></label>' . "\n";
                break;
        }

        echo wp_kses($html, $this->get_allowed_html());
    }

    /**
     * Validate individual settings field
     * @param  array $data Inputted value
     * @return array       Validated value
     */
    public function validate_fields($data)
    {
        // Security checks - only enforce on actual settings save POST from options.php
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ('POST' === $request_method && isset($_POST['_wpnonce'])) {
            if (!current_user_can('manage_options')) {
                wp_die( esc_html__( 'Insufficient permissions to modify settings.', 'atr-cookie-notice' ) );
            }
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), $this->plugin_slug . '_options-options')) {
                add_settings_error($this->plugin_slug, 'invalid-nonce', __('Security check failed. Please try again.', 'atr-cookie-notice'), 'error');
                // Return existing options to avoid wiping settings on failed nonce
                $existing = get_option($this->plugin_slug, array());
                return is_array($existing) ? $existing : array();
            }
        }
        
        // Field-specific sanitization and normalization
        if (is_array($this->settings)) {
            foreach ($this->settings as $section => $section_data) {
                foreach ($section_data['fields'] as $field) {
                    $id = $field['id'];
                    $type = isset($field['type']) ? $field['type'] : 'text';

                    switch ($type) {
                        case 'checkbox':
                            // If not present, treat as off
                            if (!isset($data[$id])) {
                                $data[$id] = 'off';
                            } else {
                                $data[$id] = ($data[$id] === 'on') ? 'on' : 'off';
                            }
                            break;

                        case 'checkbox_multi':
                            $valid = array_keys(isset($field['options']) ? (array) $field['options'] : array());
                            $submitted = isset($data[$id]) && is_array($data[$id]) ? $data[$id] : array();
                            $data[$id] = array_values(array_intersect($submitted, $valid));
                            break;

                        case 'radio':
                        case 'select':
                            $valid = array_keys(isset($field['options']) ? (array) $field['options'] : array());
                            $val = isset($data[$id]) ? sanitize_text_field($data[$id]) : '';
                            if (!in_array($val, $valid, true)) {
                                $data[$id] = isset($field['default']) ? $field['default'] : '';
                            } else {
                                $data[$id] = $val;
                            }
                            break;

                        case 'select_multi':
                            $valid = array_keys(isset($field['options']) ? (array) $field['options'] : array());
                            $submitted = isset($data[$id]) && is_array($data[$id]) ? array_map('sanitize_text_field', $data[$id]) : array();
                            $data[$id] = array_values(array_intersect($submitted, $valid));
                            break;

                        case 'number':
                            $data[$id] = isset($data[$id]) ? absint($data[$id]) : 0;
                            break;

                        case 'textarea':
                            $data[$id] = isset($data[$id]) ? sanitize_textarea_field($data[$id]) : '';
                            break;
                        case 'textarea_html':
                            $raw = isset($data[$id]) ? (string) $data[$id] : '';
                            // Bound length to avoid absurdly long inputs
                            if (strlen($raw) > 20000) {
                                $raw = substr($raw, 0, 20000);
                            }
                            $data[$id] = wp_kses($raw, $this->get_allowed_custom_html_tags());
                            break;

                        case 'text':
                        case 'password':
                        case 'text_secret':
                        default:
                            $data[$id] = isset($data[$id]) ? sanitize_text_field($data[$id]) : '';
                            break;
                    }
                }
            }
        }

        // Validate color fields
        $color_fields = [
            'primary_color',
            'secondary_color',
            'text_color',
            'background_color',
            'link_color',
            'primary_button_bg_color',
            'primary_button_text_color',
            'secondary_button_bg_color',
            'secondary_button_text_color',
            'overlay_color',
        ];
        foreach ($color_fields as $field) {
            if (!empty($data[$field])) {
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $data[$field])) {
                    /* translators: %s: Hex color like #0073aa */
                    add_settings_error(
                        $this->plugin_slug,
                        'invalid-' . $field,
                        sprintf(
                            /* translators: %s: Hex color like #0073aa */
                            __('%s must be a valid hex color code (e.g., #0073aa).', 'atr-cookie-notice'),
                            ucfirst(str_replace('_', ' ', $field))
                        ),
                        'error'
                    );
                }
            }
        }

        // Validate numeric fields
        if (isset($data['auto_hide_delay'])) {
            $data['auto_hide_delay'] = absint($data['auto_hide_delay']);
        }

        if (isset($data['cookie_expiry_days'])) {
            $data['cookie_expiry_days'] = max(1, absint($data['cookie_expiry_days']));
        }

        // Overlay opacity clamp (0..1)
        if (isset($data['overlay_opacity'])) {
            $val = is_numeric($data['overlay_opacity']) ? (float) $data['overlay_opacity'] : 0.35;
            if ($val < 0) { $val = 0; }
            if ($val > 1) { $val = 1; }
            // Store as string for consistency
            $data['overlay_opacity'] = (string) $val;
        }

        return $data;
    }

    /**
     * Allowed HTML tags/attributes for customizable banner content.
     */
    private function get_allowed_custom_html_tags() {
        return array(
            'a' => array(
                'href'   => array(),
                'rel'    => array(),
                'target' => array(),
                'role'   => array(),
                'class'  => array(),
                'id'     => array(),
            ),
            'button' => array(
                'type'   => array(),
                'class'  => array(),
                'id'     => array(),
                'name'   => array(),
                'value'  => array(),
            ),
            'strong' => array(),
            'em'     => array(),
            'span'   => array(
                'class' => array(),
                'id'    => array(),
                'style' => array(), // allow for loading span default inline style
            ),
            'br'     => array(),
            'p'      => array(
                'class' => array(),
            ),
        );
    }

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
        }
?>
        <div class="wrap atr-scb-settings-wrap" id="<?php echo esc_attr( $this->plugin_slug ); ?>">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) :
                $purge_url = wp_nonce_url( admin_url( 'admin-post.php?action=atr_cookie_notice_purge_caches' ), 'atr_cookie_notice_purge_caches' );
                ?>
            <div class="notice notice-warning atr-scb-cache-reminder is-dismissible" style="border-left-color:#d63638;padding:12px 16px;margin:16px 0;">
                <p style="margin:0 0 10px 0;font-size:14px;">
                    <strong><?php echo esc_html__( 'Important: Refresh cache so your changes take effect.', 'atr-cookie-notice' ); ?></strong>
                </p>
                <p style="margin:0 0 12px 0;">
                    <?php echo esc_html__( 'Settings are saved, but visitors may still see the old banner until caches are cleared. Use the button below or clear your caching plugin / CDN / browser cache.', 'atr-cookie-notice' ); ?>
                </p>
                <p style="margin:0;">
                    <a href="<?php echo esc_url( $purge_url ); ?>" class="button button-primary"><?php echo esc_html__( 'Purge caches and refresh assets', 'atr-cookie-notice' ); ?></a>
                </p>
            </div>
            <?php endif; ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
            if ( isset($_GET['atr_cnpurged']) && $_GET['atr_cnpurged'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Caches purged and assets refreshed.', 'atr-cookie-notice' ); ?></p></div>
            <?php endif; ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
            if ( isset($_GET['atr_cnreset']) && $_GET['atr_cnreset'] === '1' ) : ?>
            <div class="notice notice-warning is-dismissible"><p><?php echo esc_html__( 'Settings have been reset to defaults.', 'atr-cookie-notice' ); ?></p></div>
            <?php endif; ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
            if ( isset($_GET['atr_cnstylereset']) && $_GET['atr_cnstylereset'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Styling & Appearance settings have been reset to defaults.', 'atr-cookie-notice' ); ?></p></div>
            <?php endif; ?>

            <h2><?php echo esc_html__('Cookie Notice Settings', 'atr-cookie-notice'); ?></h2>
            <p><?php echo esc_html__('Configure the cookie notice behavior and appearance.', 'atr-cookie-notice'); ?></p>

            <div class="atr-scb-tools">
                <h2><?php echo esc_html__('Tools', 'atr-cookie-notice'); ?></h2>
                <div class="notice notice-warning" style="margin:10px 0;">
                    <p><strong><?php echo esc_html__( 'Heads up:', 'atr-cookie-notice' ); ?></strong>
                    <?php echo esc_html__( 'Caching/optimization plugins (and some CDNs) may minify, combine, defer, or delay JavaScript. That can prevent the consent banner from loading or cause buttons to stop responding.', 'atr-cookie-notice' ); ?>
                    </p>
                    <p><?php echo esc_html__( 'If the banner does not appear or behave correctly, exclude the following files from any "Minify/Combine", "Defer/Delay/Async", and CDN minification features:', 'atr-cookie-notice' ); ?></p>
                    <ul style="margin-left:18px;list-style:disc;">
                        <li><code>wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-simple.js</code></li>
                        <li><code>wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-public.js</code></li>
                    </ul>
                    <p><?php echo esc_html__( 'After updating cache settings, purge your caches and reload the site in a private window to verify.', 'atr-cookie-notice' ); ?></p>
                </div>
                <p><?php echo esc_html__('Use this to purge common caches and refresh plugin asset versions (bypass stale caches).', 'atr-cookie-notice'); ?></p>
                <?php $purge_url = wp_nonce_url( admin_url('admin-post.php?action=atr_cookie_notice_purge_caches'), 'atr_cookie_notice_purge_caches' ); ?>
                <a href="<?php echo esc_url( $purge_url ); ?>" class="button button-secondary"><?php echo esc_html__('Purge caches and refresh assets', 'atr-cookie-notice'); ?></a>
                <?php $reset_style_url = wp_nonce_url( admin_url('admin-post.php?action=atr_cookie_notice_reset_style'), 'atr_cookie_notice_reset_style' ); ?>
                <a href="<?php echo esc_url( $reset_style_url ); ?>" class="button button-link-delete atr-scb-reset-style-btn" id="atr-scb-reset-style-btn"><?php echo esc_html__('Reset Styling to defaults', 'atr-cookie-notice'); ?></a>
            </div>

            <!-- Live Preview: wrapper contains only the preview block -->
            <div class="atr-scb-live-preview atr-scb-preview-top" style="margin:0 0 20px 0;">
                <h2><?php echo esc_html__('Live Preview', 'atr-cookie-notice'); ?></h2>
                <p class="description"><?php echo esc_html__('This preview reflects Styling & Appearance settings (visuals only). Save changes to apply on the frontend.', 'atr-cookie-notice'); ?></p>
                <style id="atr-scb-preview-style" class="atr-scb-preview-style"></style>
                <div id="atr-scb-preview" class="atr-scb-preview">
                    <div id="scb-banner" class="scb-banner scb-banner-bottom scb-mode-full" style="position:relative;transform:none;opacity:1;visibility:visible;">
                        <div class="scb-modal">
                            <div class="scb-content">
                                <div class="scb-text">
                                    <strong><?php echo esc_html( get_bloginfo('name') ); ?></strong>
                                    <?php echo esc_html__( 'We use cookies to ensure the website functions properly and improve user experience.', 'atr-cookie-notice' ); ?>
                                </div>
                                <div class="scb-actions">
                                    <button class="scb-btn scb-btn-primary scb-btn-accept-all" type="button"><?php echo esc_html__( 'Accept All', 'atr-cookie-notice' ); ?></button>
                                    <button class="scb-btn scb-btn-secondary scb-btn-reject" type="button"><?php echo esc_html__( 'Reject Non-Essential', 'atr-cookie-notice' ); ?></button>
                                    <button class="scb-btn scb-btn-link scb-btn-custom" type="button"><?php echo esc_html__( 'Preferences', 'atr-cookie-notice' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    /* Minimal preview CSS using the same token names */
                    .atr-scb-preview .scb-banner{
                        font-family: var(--scb-font-family, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif);
                        font-size: var(--scb-font-size, 14px);
                        color: var(--scb-text, #333);
                        background: var(--scb-bg, #fff);
                        border-radius: var(--scb-radius, 8px);
                        max-width: var(--scb-modal-max, 420px);
                        box-shadow: var(--scb-shadow, 0 8px 24px rgba(0,0,0,.15));
                        padding: var(--scb-pad-y, 16px) var(--scb-pad-x, 20px);
                    }
                    .atr-scb-preview .scb-content{ display:flex; flex-direction: var(--scb-direction, column); gap: var(--scb-gap, 12px); }
                    .atr-scb-preview .scb-text{ text-align: var(--scb-align, left); }
                    .atr-scb-preview .scb-actions{ display:flex; gap: var(--scb-gap, 12px); flex-wrap: wrap; }
                    .atr-scb-preview .scb-btn{ padding: var(--scb-btn-pad-y, 8px) var(--scb-btn-pad-x, 12px); border-radius: var(--scb-btn-radius, var(--scb-radius, 8px)); border:1px solid #ccc; cursor:pointer; font-weight: var(--scb-btn-weight, 600); }
                    .atr-scb-preview .scb-btn-primary{ background: var(--scb-primary-btn-bg, var(--scb-primary, #0b74de)); color: var(--scb-primary-btn-text, #fff); border-color: transparent; text-transform: var(--scb-btn-transform, none); }
                    .atr-scb-preview .scb-btn-secondary{ background: var(--scb-secondary-btn-bg, #f7f7f7); color: var(--scb-secondary-btn-text, #333); text-transform: var(--scb-btn-transform, none); }
                    .atr-scb-preview .scb-btn-link{ background: transparent; border:none; color: var(--scb-link, #0b74de); text-decoration: underline; text-transform: var(--scb-btn-transform, none); }
                </style>
            </div>

            <!-- Tab navigation starts -->
            <h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
                <?php
                foreach ($this->settings as $section => $data) {
                    $section_id = sanitize_key( $section );
                    $title = isset( $data['title'] ) ? $data['title'] : '';
                    echo '<a href="#' . esc_attr( $section_id ) . '" class="nav-tab">' . esc_html( $title ) . '</a>';
                }
                ?>
            </h2>
            <?php $this->do_script_for_tabbed_nav(); ?>
            <!-- Tab navigation ends -->

            <form action="options.php" method="POST">
                <?php settings_fields($this->plugin_slug . '_options'); ?>
                <div class="settings-container">
                    <?php do_settings_sections($this->plugin_slug); ?>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Print jQuery script for tabbed navigation
     * @return void
     */
    private function do_script_for_tabbed_nav()
    {
        // No inline scripts; behavior handled in enqueued admin JS.
        return;
    }

    /**
     * Handle Reset to Defaults request.
     */
    public function handle_reset_defaults() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
        }
        // Verify nonce
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'atr_cookie_notice_reset_defaults' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'atr-cookie-notice' ) );
        }
        // Delete the options to force re-create with defaults on next load
        delete_option( $this->plugin_slug );
        // Redirect back to Settings > ATR Cookie Notice with success notice
        $redirect = add_query_arg(
            array(
                'page'       => $this->plugin_name,
                'atr_cnreset' => '1',
            ),
            admin_url( 'options-general.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Handle Reset Styling Only.
     */
    public function handle_reset_style() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
        }
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'atr_cookie_notice_reset_style' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'atr-cookie-notice' ) );
        }
        // Load current options
        $options = get_option( $this->plugin_slug, array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }
        // Get styling fields and set them back to defaults (or remove if no default)
        $all_settings = $this->settings_fields();
        if ( isset( $all_settings['styling']['fields'] ) && is_array( $all_settings['styling']['fields'] ) ) {
            foreach ( $all_settings['styling']['fields'] as $field ) {
                if ( empty( $field['id'] ) ) {
                    continue;
                }
                $id = $field['id'];
                if ( array_key_exists( 'default', $field ) ) {
                    $options[ $id ] = $field['default'];
                } else {
                    unset( $options[ $id ] );
                }
            }
        }
        update_option( $this->plugin_slug, $options );
        // Redirect back to Settings > ATR Cookie Notice with notice
        $redirect = add_query_arg(
            array(
                'page'             => $this->plugin_name,
                'atr_cnstylereset' => '1',
            ),
            admin_url( 'options-general.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    private function get_allowed_html()
    {
        return array(
            'div' => array(
                'class' => array(),
                'id' => array(),
            ),
            'input' => array(
                'id' => array(),
                'class' => array(),
                'type' => array(),
                'name' => array(),
                'value' => array(),
                'checked' => array(),
                'placeholder' => array(),
                'multiple' => array(),
                'data-default-color' => array(),
            ),
            'select' => array(
                'id' => array(),
                'class' => array(),
                'name' => array(),
                'multiple' => array(),
            ),
            'option' => array(
                'value' => array(),
                'selected' => array(),
            ),
            'textarea' => array(
                'id' => array(),
                'class' => array(),
                'name' => array(),
                'rows' => array(),
                'cols' => array(),
                'placeholder' => array(),
            ),
            'label' => array(
                'for' => array(),
                'class' => array(),
            ),
            'span' => array(
                'class' => array(),
                'id' => array(),
            ),
            'p' => array(
                'class' => array(),
            ),
            'br' => array(),
            'h2' => array(
                'class' => array(),
                'style' => array(),
            ),
            'h3' => array(
                'class' => array(),
            ),
            'ul' => array(
                'class' => array(),
            ),
            'li' => array(
                'class' => array(),
            ),
            'ol' => array(
                'class' => array(),
            ),
            'a' => array(
                'href'   => array(),
                'class'  => array(),
                'id'     => array(),
                'target' => array(),
                'rel'    => array(),
            ),
            'button' => [
                'type'    => [],
                'class'   => [],
                'id'      => [],
                'onclick' => [],
                'name'    => [],
                'value'   => [],
            ],
        );
    }

    /**
     * Safely read a local documentation file under docs dir.
     * - Prevents path traversal
     * - Enforces extension whitelist
     * - Enforces max size (1MB)
     */
    private function read_local_doc_safely($file_path)
    {
        $docs_root = realpath($this->docs_dir);
        $real = realpath($file_path);

        if (!$docs_root || !$real) {
            return false;
        }

        // Ensure file is inside docs directory
        if (strpos($real, $docs_root) !== 0) {
            return false;
        }

        // Check allowed extensions
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $allowed = array('md', 'txt');
        if (!in_array($ext, $allowed, true)) {
            return false;
        }

        // Check size (<= 1MB)
        if (!file_exists($real) || filesize($real) > 1024 * 1024) {
            return false;
        }

        $contents = file_get_contents($real);
        if ($contents === false) {
            return false;
        }

        return $contents;
    }
}
