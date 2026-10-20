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
class Atr_Cookie_Notice_Admin_Settings {


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
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 * current ones speak for themselves.
	 */
	public function __construct( $plugin_name, $plugin_slug, $version ) {
		$this->plugin_slug = $plugin_slug;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		// Initialise settings
		add_action( 'admin_init', array( $this, 'init' ) );
		// Cache purge admin-post handler is registered in the main plugin file
		// Reset to defaults handler
		add_action( 'admin_post_atr_cookie_notice_reset_defaults', array( $this, 'handle_reset_defaults' ) );
		// Reset styling only
		add_action( 'admin_post_atr_cookie_notice_reset_style', array( $this, 'handle_reset_style' ) );
	}

	/**
	 * Initialize settings
	 *
	 * @return void
	 */
	public function init() {
		// Always register settings, but only load options on our pages
		$this->settings = $this->settings_fields();
		$this->register_settings();

		// Only load options on our plugin settings screen
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only usage for screen detection
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( $page === $this->plugin_slug ) {
			$this->options = $this->get_options();
		}
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_submenu_item() {
		// Register settings under WordPress "Settings" menu
		add_options_page(
			__( 'ATR Cookie Notice Settings', 'atr-cookie-notice' ),
			__( 'ATR Cookie Notice', 'atr-cookie-notice' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links
	 * @return array        Modified links
	 */
	public function add_action_links( $links ) {
		// Settings are under WordPress Settings menu (options-general.php); docs are on the author's website
		$settings_url = add_query_arg( 'page', $this->plugin_name, admin_url( 'options-general.php' ) );
		$links[]      = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'atr-cookie-notice' ) . '</a>';
		$links[]      = '<a href="' . esc_url( self::get_docs_url() ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Docs', 'atr-cookie-notice' ) . '</a>';

		$links[] = '<a href="https://atarimtr.com" target="_blank" rel="noopener noreferrer">' . esc_html__( 'More plugins by Yehuda Tiram (English)', 'atr-cookie-notice' ) . '</a>';
		$links[] = '<a href="https://atarimtr.co.il" target="_blank" rel="noopener noreferrer">' . esc_html__( 'More plugins by Yehuda Tiram (Hebrew)', 'atr-cookie-notice' ) . '</a>';
		return $links;
	}

	/**
	 * URL of a documentation page on the author's website, in the admin user's language.
	 *
	 * Hebrew admins get atarimtr.co.il, everyone else atarimtr.com.
	 *
	 * @since 1.3.0
	 * @param string $slug   Page slug on the website.
	 * @param string $anchor Optional section anchor (without #).
	 * @return string
	 */
	public static function get_docs_url( $slug = 'atr-cookie-notice-docs', $anchor = '' ) {
		$base = ( 0 === strpos( get_user_locale(), 'he' ) ) ? 'https://atarimtr.co.il/' : 'https://atarimtr.com/';
		$url  = $base . $slug . '/';
		return '' !== $anchor ? $url . '#' . $anchor : $url;
	}

	/**
	 * Highlighted notice: tracking code printed by the theme or another plugin is not blocked
	 * automatically and must be wrapped with the plugin's consent marker.
	 *
	 * Shown at the top of the Help tab and under the Consent Mode field (Full mode).
	 *
	 * @since 1.3.0
	 * @param string $extra_class Additional CSS classes for the wrapper.
	 * @return string
	 */
	private function render_tracking_code_notice( $extra_class = '' ) {
		$html  = '<div class="notice notice-warning inline atr-scb-help-important ' . esc_attr( $extra_class ) . '">';
		$html .= '<h3>' . esc_html__( 'Important: tracking code added by your theme or another plugin', 'atr-cookie-notice' ) . '</h3>';
		$html .= '<p>' . esc_html__( 'Full mode does not stop Google Tag Manager, Analytics or Pixel code that your theme or another plugin prints into the page (for example in the header). That code runs before the visitor decides. To put it under the banner\'s control, change its opening script tag:', 'atr-cookie-notice' ) . '</p>';
		$html .= '<p><code>&lt;script type="text/plain" data-consent="analytics"&gt;</code></p>';
		$html .= '<p>' . esc_html__( 'Use data-consent="analytics" for Google Tag Manager / Analytics and data-consent="marketing" for Facebook Pixel and advertising. The code runs only after the visitor consents to that category.', 'atr-cookie-notice' ) . ' <a href="' . esc_url( self::get_docs_url( 'atr-cookie-notice-docs', 'block-tracking-code' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'How to wrap tracking code', 'atr-cookie-notice' ) . '</a></p>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * HTML of the Help tab: quick start, FAQ and links to the documentation website.
	 *
	 * @since 1.3.0
	 * @return string
	 */
	private function render_help_tab() {
		$link = function ( $text, $slug, $anchor = '' ) {
			return '<a href="' . esc_url( self::get_docs_url( $slug, $anchor ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $text ) . '</a>';
		};
		$docs = 'atr-cookie-notice-docs';

		// Shown first and highlighted: theme-embedded tracking code is not blocked automatically
		$html  = $this->render_tracking_code_notice();
		$html .= '<h3>' . esc_html__( 'Quick start', 'atr-cookie-notice' ) . '</h3><ol>';
		$html .= '<li>' . esc_html__( 'General tab: make sure the banner is enabled.', 'atr-cookie-notice' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Choose the Consent Mode: Full disables the Google Analytics and Facebook Pixel functions until consent (wrap tracking code from your theme as explained above); Simple only informs visitors.', 'atr-cookie-notice' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Set your Privacy Policy page in Settings → Privacy. The banner links to it.', 'atr-cookie-notice' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Choose the banner position and side, and check the look in the Styling & Appearance preview.', 'atr-cookie-notice' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Save, click "Purge caches and refresh assets", and check the site in a private window.', 'atr-cookie-notice' ) . '</li>';
		$html .= '</ol>';

		$faq   = array(
			'faq-button-colors'       => array( __( 'Button colors look different because of the theme', 'atr-cookie-notice' ), __( 'Enable Advanced Options → "Force override theme styles".', 'atr-cookie-notice' ) ),
			'faq-popup'               => array( __( 'A site popup covers the banner', 'atr-cookie-notice' ), __( 'Raise Styling & Appearance → "Banner Z-Index" (maximum 2147483647).', 'atr-cookie-notice' ) ),
			'faq-chat-widget'         => array( __( 'The banner covers a chat widget', 'atr-cookie-notice' ), __( 'Set General → "Banner Side" to the other side. Visitors can also drag the banner aside.', 'atr-cookie-notice' ) ),
			'faq-hide-on-page'        => array( __( 'No banner wanted on a specific page', 'atr-cookie-notice' ), __( 'Add the page ID to General → "Hide Banner on Pages". Tracking is not blocked there.', 'atr-cookie-notice' ) ),
			'faq-changes-not-showing' => array( __( 'Changes do not appear on the site', 'atr-cookie-notice' ), __( 'Click "Purge caches and refresh assets" and check in a private window.', 'atr-cookie-notice' ) ),
			'faq-banner-not-working'  => array( __( 'The banner does not appear or its buttons do not respond', 'atr-cookie-notice' ), __( 'Exclude the plugin\'s JavaScript files from your cache plugin\'s minify/combine/defer/delay (see Tools at the top of this page).', 'atr-cookie-notice' ) ),
		);
		$html .= '<h3>' . esc_html__( 'Frequently asked questions', 'atr-cookie-notice' ) . '</h3><ul>';
		foreach ( $faq as $anchor => $qa ) {
			$html .= '<li><strong>' . esc_html( $qa[0] ) . ':</strong> ' . esc_html( $qa[1] ) . ' ' . $link( __( 'Full explanation', 'atr-cookie-notice' ), $docs, $anchor ) . '</li>';
		}
		$html .= '</ul>';

		$html .= '<h3>' . esc_html__( 'More on the website', 'atr-cookie-notice' ) . '</h3><ul>';
		$html .= '<li>' . $link( __( 'Full documentation', 'atr-cookie-notice' ), $docs ) . '</li>';
		$html .= '<li>' . $link( __( 'Multilingual sites (WPML / Polylang)', 'atr-cookie-notice' ), $docs, 'multilingual' ) . '</li>';
		$html .= '<li>' . $link( __( 'Sample privacy policy', 'atr-cookie-notice' ), 'atr-cookie-notice-sample-privacy-policy' ) . '</li>';
		$html .= '<li>' . $link( __( 'Testing guide (for developers)', 'atr-cookie-notice' ), 'atr-cookie-notice-testing-guide' ) . '</li>';
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {
		$settings['general'] = array(
			'title'       => __( 'General Settings', 'atr-cookie-notice' ),
			'description' => __( 'Configure the basic behavior of the cookie notice.', 'atr-cookie-notice' ),
			'fields'      => array(
				array(
					'id'          => 'enable_banner',
					'label'       => __( 'Enable Cookie Banner', 'atr-cookie-notice' ),
					'description' => __( 'Show the cookie notice to visitors.', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'banner_mode',
					'label'       => __( 'Consent Mode', 'atr-cookie-notice' ),
					'description' => __( 'Full: disables the Google Analytics and Facebook Pixel functions until consent. Tracking code that your theme or another plugin prints into the page is not blocked automatically; wrap it as explained in the Help tab. Simple: an informational notice, nothing is blocked.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'full'   => __( 'Full (consent before Google Analytics / Facebook Pixel; see Help for theme code)', 'atr-cookie-notice' ),
						'simple' => __( 'Simple (informational only, no blocking)', 'atr-cookie-notice' ),
					),
					'default'     => 'full',
				),
				array(
					'id'          => 'banner_position',
					'label'       => __( 'Banner Position', 'atr-cookie-notice' ),
					'description' => __( 'Choose where the banner appears on the page.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'bottom'  => __( 'Bottom', 'atr-cookie-notice' ),
						'top'     => __( 'Top', 'atr-cookie-notice' ),
						'overlay' => __( 'Overlay (Center)', 'atr-cookie-notice' ),
					),
					'default'     => 'bottom',
				),
				array(
					'id'          => 'banner_side',
					'label'       => __( 'Banner Side', 'atr-cookie-notice' ),
					'description' => __( 'Screen side for the Bottom and Top positions (e.g. to avoid covering a chat widget). Not used in Overlay position.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'right' => __( 'Right', 'atr-cookie-notice' ),
						'left'  => __( 'Left', 'atr-cookie-notice' ),
					),
					'default'     => 'right',
				),
				array(
					'id'          => 'excluded_pages',
					'label'       => __( 'Hide Banner on Pages', 'atr-cookie-notice' ),
					'description' => __( 'Comma-separated post/page IDs (e.g. 12, 345). Warning: on these pages the banner is not shown and tracking scripts are NOT blocked, even in Full mode. Visitors are not informed and no consent is collected there.', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '12, 345',
				),
				array(
					'id'          => 'auto_hide_delay',
					'label'       => __( 'Auto-hide Delay (seconds)', 'atr-cookie-notice' ),
					'description' => __( 'Automatically hide the banner after this many seconds (0 = no auto-hide).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '0',
					'placeholder' => '0',
				),
			),
		);

		$settings['cookies'] = array(
			'title'       => __( 'Cookie Categories', 'atr-cookie-notice' ),
			'description' => __( 'Configure which cookie categories to show and their default states.', 'atr-cookie-notice' ),
			'fields'      => array(
				array(
					'id'          => 'show_analytics_cookies',
					'label'       => __( 'Show Analytics Cookies', 'atr-cookie-notice' ),
					'description' => __( 'Display the analytics cookies option in the banner.', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'show_marketing_cookies',
					'label'       => __( 'Show Marketing Cookies', 'atr-cookie-notice' ),
					'description' => __( 'Display the marketing cookies option in the banner.', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'analytics_cookies_default',
					'label'       => __( 'Analytics Cookies Default', 'atr-cookie-notice' ),
					'description' => __( 'Default state for analytics cookies (checked = enabled by default).', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'marketing_cookies_default',
					'label'       => __( 'Marketing Cookies Default', 'atr-cookie-notice' ),
					'description' => __( 'Default state for marketing cookies (checked = enabled by default).', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
			),
		);

		$settings['styling'] = array(
			'title'       => __( 'Styling & Appearance', 'atr-cookie-notice' ),
			'description' => __( 'Customize the appearance of the cookie notice.', 'atr-cookie-notice' ),
			'fields'      => array(
				array(
					'id'          => 'primary_color',
					'label'       => __( 'Primary Color', 'atr-cookie-notice' ),
					'description' => __( 'Main color for buttons and highlights (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#0073aa',
					'placeholder' => '#0073aa',
				),
				array(
					'id'          => 'secondary_color',
					'label'       => __( 'Secondary Color', 'atr-cookie-notice' ),
					'description' => __( 'Secondary/accent color for secondary elements (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#666666',
					'placeholder' => '#666666',
				),
				array(
					'id'          => 'text_color',
					'label'       => __( 'Text Color', 'atr-cookie-notice' ),
					'description' => __( 'Color for text content (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#333333',
					'placeholder' => '#333333',
				),
				array(
					'id'          => 'link_color',
					'label'       => __( 'Link Color', 'atr-cookie-notice' ),
					'description' => __( 'Color for links (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#0073aa',
					'placeholder' => '#0073aa',
				),
				array(
					'id'          => 'background_color',
					'label'       => __( 'Background Color', 'atr-cookie-notice' ),
					'description' => __( 'Background color for the banner (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#ffffff',
					'placeholder' => '#ffffff',
				),
				// Typography
				array(
					'id'          => 'font_family',
					'label'       => __( 'Font Family', 'atr-cookie-notice' ),
					'description' => __( 'Custom font-family stack (e.g., system-ui, -apple-system, Segoe UI, Roboto, sans-serif).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
					'placeholder' => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
				),
				array(
					'id'          => 'base_font_size',
					'label'       => __( 'Base Font Size (px)', 'atr-cookie-notice' ),
					'description' => __( 'Base font size for the banner (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '14',
					'placeholder' => '14',
				),
				array(
					'id'          => 'font_weight',
					'label'       => __( 'Base Font Weight', 'atr-cookie-notice' ),
					'description' => __( 'Select base font weight.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'400' => '400',
						'500' => '500',
						'600' => '600',
						'700' => '700',
					),
					'default'     => '400',
				),
				array(
					'id'          => 'button_font_weight',
					'label'       => __( 'Button Font Weight', 'atr-cookie-notice' ),
					'description' => __( 'Select button font weight.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'400' => '400',
						'500' => '500',
						'600' => '600',
						'700' => '700',
					),
					'default'     => '600',
				),
				array(
					'id'          => 'uppercase_buttons',
					'label'       => __( 'Uppercase Buttons', 'atr-cookie-notice' ),
					'description' => __( 'Render button labels in uppercase.', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				// Buttons color overrides
				array(
					'id'          => 'primary_button_bg_color',
					'label'       => __( 'Primary Button BG', 'atr-cookie-notice' ),
					'description' => __( 'Background color for the primary button (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '#0b74de',
				),
				array(
					'id'          => 'primary_button_text_color',
					'label'       => __( 'Primary Button Text', 'atr-cookie-notice' ),
					'description' => __( 'Text color for the primary button (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '#ffffff',
				),
				array(
					'id'          => 'secondary_button_bg_color',
					'label'       => __( 'Secondary Button BG', 'atr-cookie-notice' ),
					'description' => __( 'Background color for the secondary button (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '#f7f7f7',
				),
				array(
					'id'          => 'secondary_button_text_color',
					'label'       => __( 'Secondary Button Text', 'atr-cookie-notice' ),
					'description' => __( 'Text color for the secondary button (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '#333333',
				),
				// Layout & spacing
				array(
					'id'          => 'border_radius',
					'label'       => __( 'Border Radius (px)', 'atr-cookie-notice' ),
					'description' => __( 'Corner radius for banner and buttons (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '8',
					'placeholder' => '8',
				),
				array(
					'id'          => 'banner_max_width',
					'label'       => __( 'Banner Max Width (px)', 'atr-cookie-notice' ),
					'description' => __( 'Maximum width for the banner (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '420',
					'placeholder' => '420',
				),
				array(
					'id'          => 'modal_max_width',
					'label'       => __( 'Modal Max Width (px)', 'atr-cookie-notice' ),
					'description' => __( 'Maximum width for the modal container (Full mode).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '420',
					'placeholder' => '420',
				),
				array(
					'id'          => 'container_padding_y',
					'label'       => __( 'Container Padding Y (px)', 'atr-cookie-notice' ),
					'description' => __( 'Vertical padding inside the banner (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '16',
					'placeholder' => '16',
				),
				array(
					'id'          => 'container_padding_x',
					'label'       => __( 'Container Padding X (px)', 'atr-cookie-notice' ),
					'description' => __( 'Horizontal padding inside the banner (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '20',
					'placeholder' => '20',
				),
				array(
					'id'          => 'gap_between_elements',
					'label'       => __( 'Gap Between Elements (px)', 'atr-cookie-notice' ),
					'description' => __( 'Space between text, buttons and controls (in pixels).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '12',
					'placeholder' => '12',
				),
				array(
					'id'          => 'layout_direction',
					'label'       => __( 'Layout Direction', 'atr-cookie-notice' ),
					'description' => __( 'Stack elements vertically (column) or horizontally (row).', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'column' => __( 'Column', 'atr-cookie-notice' ),
						'row'    => __( 'Row', 'atr-cookie-notice' ),
					),
					'default'     => 'column',
				),
				array(
					'id'          => 'content_align',
					'label'       => __( 'Content Alignment', 'atr-cookie-notice' ),
					'description' => __( 'Align content left, center, or right.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'left'   => __( 'Left', 'atr-cookie-notice' ),
						'center' => __( 'Center', 'atr-cookie-notice' ),
						'right'  => __( 'Right', 'atr-cookie-notice' ),
					),
					'default'     => 'left',
				),
				// Overlay & elevation
				array(
					'id'          => 'overlay_color',
					'label'       => __( 'Overlay Color', 'atr-cookie-notice' ),
					'description' => __( 'Overlay base color (hex code).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '#000000',
					'placeholder' => '#000000',
				),
				array(
					'id'          => 'overlay_opacity',
					'label'       => __( 'Overlay Opacity (0–1)', 'atr-cookie-notice' ),
					'description' => __( 'Opacity for overlay background (0 to 1).', 'atr-cookie-notice' ),
					'type'        => 'text',
					'default'     => '0.35',
					'placeholder' => '0.35',
				),
				array(
					'id'          => 'z_index',
					'label'       => __( 'Banner Z-Index', 'atr-cookie-notice' ),
					'description' => __( 'Z-index for the banner (integer).', 'atr-cookie-notice' ) . ' ' . __( 'If a site popup covers the banner, raise this value (maximum 2147483647).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '9999',
					'placeholder' => '9999',
				),
				array(
					'id'          => 'shadow_preset',
					'label'       => __( 'Shadow Preset', 'atr-cookie-notice' ),
					'description' => __( 'Preset for modal/banner shadow.', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						'none' => __( 'None', 'atr-cookie-notice' ),
						'sm'   => __( 'Small', 'atr-cookie-notice' ),
						'md'   => __( 'Medium', 'atr-cookie-notice' ),
						'lg'   => __( 'Large', 'atr-cookie-notice' ),
					),
					'default'     => 'md',
				),
				array(
					'id'          => 'custom_css',
					'label'       => __( 'Custom CSS', 'atr-cookie-notice' ),
					'description' => __( 'Additional CSS rules to customize the banner appearance.', 'atr-cookie-notice' ),
					'type'        => 'textarea',
					'default'     => '',
					'placeholder' => '/* Add your custom CSS here */',
				),
			),
		);

		$settings['advanced'] = array(
			'title'       => __( 'Advanced Options', 'atr-cookie-notice' ),
			'description' => __( 'Advanced configuration options for developers.', 'atr-cookie-notice' ),
			'fields'      => array(
				array(
					'id'          => 'design_preset',
					'label'       => __( 'Design Preset', 'atr-cookie-notice' ),
					'description' => __( 'Quickly apply a preset (applies on Save).', 'atr-cookie-notice' ),
					'type'        => 'select',
					'options'     => array(
						''         => __( 'None', 'atr-cookie-notice' ),
						'light'    => __( 'Light', 'atr-cookie-notice' ),
						'dark'     => __( 'Dark', 'atr-cookie-notice' ),
						'minimal'  => __( 'Minimal', 'atr-cookie-notice' ),
						'contrast' => __( 'High Contrast', 'atr-cookie-notice' ),
					),
					'default'     => '',
				),
				array(
					'id'          => 'force_override_theme_styles',
					'label'       => __( 'Force override theme styles', 'atr-cookie-notice' ),
					'description' => __( 'When enabled, the banner uses high-specificity rules and !important to win against aggressive theme/optimizer CSS.', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'enable_debug',
					'label'       => __( 'Enable Debug Mode', 'atr-cookie-notice' ),
					'description' => __( 'Show debug information in browser console (for development only).', 'atr-cookie-notice' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'cookie_expiry_days',
					'label'       => __( 'Cookie Expiry (days)', 'atr-cookie-notice' ),
					'description' => __( 'How long to remember user consent (in days).', 'atr-cookie-notice' ),
					'type'        => 'number',
					'default'     => '365',
					'placeholder' => '365',
				),
				// Global Form Integration removed
			),
		);

		// Content customization (safe HTML with tokens)
		$settings['content'] = array(
			'title'             => __( 'Content', 'atr-cookie-notice' ),
			'description'       => __( 'Customize the text and primary actions shown in the banner. Leave empty to use the default (translated) content. Allowed tags: a, button, strong, em, span, br, p. Allowed attributes: href, rel, target, role, class, id, type. Tokens: {site_name}, {privacy_url}, {privacy_link}. Buttons: [ok_button] (simple), [privacy_link] (simple), [accept_all_button], [reject_button], [preferences_button] (full).', 'atr-cookie-notice' ) . ' ' . __( 'Multilingual sites (WPML / Polylang): write these fields here in the default language only, then translate them with your multilingual plugin:', 'atr-cookie-notice' ),
			'description_links' => array(
				__( 'WPML: Registering strings from plugin settings (Admin Texts, look for atr-cookie-notice)', 'atr-cookie-notice' ) => 'https://wpml.org/documentation/translating-your-contents/strings/finding-strings-that-dont-appear-on-the-string-translation-page/#register-admin-and-setting-strings-for-translation',
				__( 'Polylang: Strings translation (group plugins/atr-cookie-notice)', 'atr-cookie-notice' ) => 'https://polylang.pro/doc/strings-translation/',
			),
			'fields'            => array(
				array(
					'id'          => 'simple_text_html',
					'label'       => __( 'Simple Mode — Text HTML', 'atr-cookie-notice' ),
					'description' => __( 'Banner description area (inside .scb-text). Tokens supported: {site_name}.', 'atr-cookie-notice' ),
					'type'        => 'textarea_html',
					'default'     => '',
					'placeholder' => '',
				),
				array(
					'id'          => 'simple_actions_html',
					'label'       => __( 'Simple Mode — Actions HTML', 'atr-cookie-notice' ),
					'description' => __( 'Primary actions area (inside .scb-actions). Tokens supported: {privacy_url}. Buttons: [ok_button], [privacy_link]. If required IDs are missing, defaults will be appended.', 'atr-cookie-notice' ),
					'type'        => 'textarea_html',
					'default'     => '',
					'placeholder' => '',
				),
				array(
					'id'          => 'full_text_html',
					'label'       => __( 'Full Mode — Text HTML', 'atr-cookie-notice' ),
					'description' => __( 'Banner description area (inside .scb-text). Tokens supported: {site_name}.', 'atr-cookie-notice' ),
					'type'        => 'textarea_html',
					'default'     => '',
					'placeholder' => '',
				),
				array(
					'id'          => 'full_actions_html',
					'label'       => __( 'Full Mode — Actions HTML', 'atr-cookie-notice' ),
					'description' => __( 'Primary actions area (inside .scb-actions). Tokens supported: {privacy_url}. Buttons: [accept_all_button], [reject_button], [preferences_button]. If required IDs are missing, defaults will be appended.', 'atr-cookie-notice' ),
					'type'        => 'textarea_html',
					'default'     => '',
					'placeholder' => '',
				),
				array(
					'id'          => 'full_footer_html',
					'label'       => __( 'Full Mode — Footer HTML', 'atr-cookie-notice' ),
					'description' => __( 'Footer area (inside .scb-footer). Tokens supported: {privacy_url}, {privacy_link}. {privacy_link} renders a full anchor with the Privacy Policy page title. Leave empty to use the default Privacy Policy link.', 'atr-cookie-notice' ),
					'type'        => 'textarea_html',
					'default'     => '',
					'placeholder' => '',
				),
			),
		);

		$settings['tools'] = array(
			'title'       => __( 'Tools', 'atr-cookie-notice' ),
			'description' => __( 'Use the Tools block at the top for cache purge and reset styling. This tab is kept for compatibility; other plugins may add fields here via filter.', 'atr-cookie-notice' ),
			'fields'      => array(),
		);

		$settings['help'] = array(
			'title'       => __( 'Help', 'atr-cookie-notice' ),
			'description' => __( 'Quick start and answers to common questions. The full documentation is on the author\'s website.', 'atr-cookie-notice' ),
			'fields'      => array(),
		);

		$settings = apply_filters( 'atr_scb_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Options getter
	 *
	 * @return array Options, either saved or default ones.
	 */
	public function get_options() {
		$options = get_option( $this->plugin_slug );

		if ( ! $options && is_array( $this->settings ) ) {
			$options = array();

			foreach ( $this->settings as $section => $data ) {
				foreach ( $data['fields'] as $field ) {
					// only apply a default if the field actually defines one
					if ( array_key_exists( 'default', $field ) ) {
						$options[ $field['id'] ] = $field['default'];
					}
				}
			}

			add_option( $this->plugin_slug, $options );
		} elseif ( $options && is_array( $this->settings ) ) {
			$changed = false;

			foreach ( $this->settings as $section => $data ) {
				foreach ( $data['fields'] as $field ) {
					if ( ! array_key_exists( $field['id'], $options ) ) {
						if ( array_key_exists( 'default', $field ) ) {
							$options[ $field['id'] ] = $field['default'];
							$changed                 = true;
						}
						// else: no default to apply, leave it out
					}
				}
			}

			if ( $changed ) {
				update_option( $this->plugin_slug, $options );
			}
		}

		return $options;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			register_setting( $this->plugin_slug . '_options', $this->plugin_slug, array( $this, 'validate_fields' ) );

			foreach ( $this->settings as $section => $data ) {

				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->plugin_slug );

				foreach ( $data['fields'] as $field ) {

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), $this->plugin_slug, $section, array( 'field' => $field ) );
				}
			}
		}
	}

	public function settings_section( $section ) {
		$sec_id = isset( $section['id'] ) ? sanitize_key( $section['id'] ) : '';
		$desc   = isset( $this->settings[ $sec_id ]['description'] ) ? $this->settings[ $sec_id ]['description'] : '';
		// One wrapper per section, so the tab script can show/hide the whole description with its section
		$html  = '<div class="atr-scb-section-desc">';
		$html .= '<p> ' . esc_html( $desc ) . '</p>' . "\n";
		$links = isset( $this->settings[ $sec_id ]['description_links'] ) ? (array) $this->settings[ $sec_id ]['description_links'] : array();
		if ( ! empty( $links ) ) {
			$html .= '<ul class="atr-scb-section-links">';
			foreach ( $links as $label => $url ) {
				$html .= '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a></li>';
			}
			$html .= '</ul>' . "\n";
		}
		if ( 'help' === $sec_id ) {
			$html .= $this->render_help_tab();
		}
		$html .= '</div>';
		echo wp_kses( $html, $this->get_allowed_html() );
	}

	/**
	 * Generate HTML for displaying fields
	 *
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field( $args ) {

		$field = $args['field'];

		$html = '';

		$option_name = $this->plugin_slug . '[' . $field['id'] . ']';

		$data = ( isset( $this->options[ $field['id'] ] ) ) ? $this->options[ $field['id'] ] : '';

		// If options not loaded yet, get them
		if ( ! isset( $this->options ) ) {
			$this->options = $this->get_options();
			$data          = ( isset( $this->options[ $field['id'] ] ) ) ? $this->options[ $field['id'] ] : '';
		}

		switch ( $field['type'] ) {

			case 'text':
			case 'password':
			case 'number':
				$input_class = '';
				// Mark known color fields for WP color picker
				$color_ids = array(
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
				);
				if ( in_array( $field['id'], $color_ids, true ) && $field['type'] === 'text' ) {
					$input_class = ' class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . ' color-picker"';
				} else {
					$input_class = ' class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '"';
				}
				$default_attr = isset( $field['default'] ) ? ' data-default-color="' . esc_attr( $field['default'] ) . '"' : '';
				$html        .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $input_class . $default_attr . '/>' . "\n";
				break;
			case 'text_secret':
				$html .= '<input type="password" id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" autocomplete="new-password"/>' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . esc_textarea( $data ) . '</textarea><br/>' . "\n";
				break;
			case 'textarea_html':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" rows="6" cols="60" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . esc_textarea( $data ) . '</textarea><br/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				// Add a hidden field to ensure a value is sent even when unchecked
				$html .= '<input type="hidden" name="' . esc_attr( $option_name ) . '" value="off" />';
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
				break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( is_array( $data ) && in_array( (string) $k, array_map( 'strval', $data ), true ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . esc_html( $v ) . '</label></br> ';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . esc_html( $v ) . '</label> ';
				}
				break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" class="atr-scb-field atr-scb-field-' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( (string) $k, array_map( 'strval', (array) $data ), true ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option> ';
				}
				$html .= '</select> ';
				break;

			case 'button':
				// Secure admin-post form that triggers purge
				$action_url = admin_url( 'admin-post.php' );
				$html      .= '<form method="post" action="' . esc_url( $action_url ) . '" style="display:inline-block">';
				$html      .= '<input type="hidden" name="action" value="atr_cookie_notice_purge_caches" />';
				$html      .= wp_nonce_field( 'atr_cookie_notice_purge_caches', '_wpnonce', true, false );
				$btn_label  = isset( $field['button_label'] ) ? $field['button_label'] : __( 'Purge caches and refresh assets', 'atr-cookie-notice' );
				$html      .= '<button type="submit" class="button button-secondary">' . esc_html( $btn_label ) . '</button>';
				$html      .= '</form>';
				break;
		}

		switch ( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . esc_html( $field['description'] ) . '</span>';
				break;

			default:
				$html .= '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . esc_html( $field['description'] ) . '</span></label>' . "\n";
				break;
		}

		// Consent Mode: show the theme tracking code notice while Full is selected (the admin script toggles it on change)
		if ( 'banner_mode' === $field['id'] ) {
			$html .= $this->render_tracking_code_notice( 'atr-scb-mode-full-notice' . ( 'simple' === $data ? ' atr-scb-is-hidden' : '' ) );
		}

		echo wp_kses( $html, $this->get_allowed_html() );
	}

	/**
	 * Validate individual settings field
	 *
	 * @param  array $data Inputted value
	 * @return array       Validated value
	 */
	public function validate_fields( $data ) {
		// Security checks - only enforce on actual settings save POST from options.php
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
		if ( 'POST' === $request_method && isset( $_POST['_wpnonce'] ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions to modify settings.', 'atr-cookie-notice' ) );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), $this->plugin_slug . '_options-options' ) ) {
				add_settings_error( $this->plugin_slug, 'invalid-nonce', __( 'Security check failed. Please try again.', 'atr-cookie-notice' ), 'error' );
				// Return existing options to avoid wiping settings on failed nonce
				$existing = get_option( $this->plugin_slug, array() );
				return is_array( $existing ) ? $existing : array();
			}
		}

		// Field-specific sanitization and normalization
		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $section => $section_data ) {
				foreach ( $section_data['fields'] as $field ) {
					$id   = $field['id'];
					$type = isset( $field['type'] ) ? $field['type'] : 'text';

					switch ( $type ) {
						case 'checkbox':
							// If not present, treat as off
							if ( ! isset( $data[ $id ] ) ) {
								$data[ $id ] = 'off';
							} else {
								$data[ $id ] = ( $data[ $id ] === 'on' ) ? 'on' : 'off';
							}
							break;

						case 'checkbox_multi':
							$valid       = array_keys( isset( $field['options'] ) ? (array) $field['options'] : array() );
							$submitted   = isset( $data[ $id ] ) && is_array( $data[ $id ] ) ? $data[ $id ] : array();
							$data[ $id ] = array_values( array_intersect( $submitted, $valid ) );
							break;

						case 'radio':
						case 'select':
							$valid = array_keys( isset( $field['options'] ) ? (array) $field['options'] : array() );
							$val   = isset( $data[ $id ] ) ? sanitize_text_field( $data[ $id ] ) : '';
							if ( ! in_array( $val, $valid, true ) ) {
								$data[ $id ] = isset( $field['default'] ) ? $field['default'] : '';
							} else {
								$data[ $id ] = $val;
							}
							break;

						case 'select_multi':
							$valid       = array_keys( isset( $field['options'] ) ? (array) $field['options'] : array() );
							$submitted   = isset( $data[ $id ] ) && is_array( $data[ $id ] ) ? array_map( 'sanitize_text_field', $data[ $id ] ) : array();
							$data[ $id ] = array_values( array_intersect( $submitted, $valid ) );
							break;

						case 'number':
							$data[ $id ] = isset( $data[ $id ] ) ? absint( $data[ $id ] ) : 0;
							break;

						case 'textarea':
							$data[ $id ] = isset( $data[ $id ] ) ? sanitize_textarea_field( $data[ $id ] ) : '';
							break;
						case 'textarea_html':
							$raw = isset( $data[ $id ] ) ? (string) $data[ $id ] : '';
							// Bound length to avoid absurdly long inputs
							if ( strlen( $raw ) > 20000 ) {
								$raw = substr( $raw, 0, 20000 );
							}
							$data[ $id ] = wp_kses( $raw, $this->get_allowed_custom_html_tags() );
							break;

						case 'text':
						case 'password':
						case 'text_secret':
						default:
							$data[ $id ] = isset( $data[ $id ] ) ? sanitize_text_field( $data[ $id ] ) : '';
							break;
					}
				}
			}
		}

		// Validate color fields
		$color_fields = array(
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
		);
		$previous     = get_option( $this->plugin_slug, array() );
		foreach ( $color_fields as $field ) {
			if ( ! empty( $data[ $field ] ) ) {
				if ( ! preg_match( '/^#[a-fA-F0-9]{6}$/', $data[ $field ] ) ) {
					// Keep the last valid value instead of saving the invalid one
					$data[ $field ] = ( is_array( $previous ) && isset( $previous[ $field ] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', (string) $previous[ $field ] ) ) ? $previous[ $field ] : '';
					/* translators: %s: Hex color like #0073aa */
					add_settings_error(
						$this->plugin_slug,
						'invalid-' . $field,
						sprintf(
							/* translators: %s: Hex color like #0073aa */
							__( '%s must be a valid hex color code (e.g., #0073aa).', 'atr-cookie-notice' ),
							ucfirst( str_replace( '_', ' ', $field ) )
						),
						'error'
					);
				}
			}
		}

		// Font family is printed inside CSS: strip characters that could end the declaration or rule
		if ( isset( $data['font_family'] ) ) {
			$data['font_family'] = trim( preg_replace( '/[<>{};\\\\]|\/\*|\*\//', '', (string) $data['font_family'] ) );
		}

		// Excluded pages: keep only positive integer IDs, stored as "12, 345"
		if ( isset( $data['excluded_pages'] ) ) {
			$tokens                 = array_filter( array_map( 'trim', explode( ',', (string) $data['excluded_pages'] ) ), 'ctype_digit' );
			$ids                    = array_filter( array_map( 'absint', $tokens ) );
			$data['excluded_pages'] = implode( ', ', array_unique( $ids ) );
		}

		// Z-index: CSS accepts at most a signed 32-bit integer
		if ( isset( $data['z_index'] ) ) {
			$data['z_index'] = min( 2147483647, absint( $data['z_index'] ) );
		}

		// Validate numeric fields
		if ( isset( $data['auto_hide_delay'] ) ) {
			$data['auto_hide_delay'] = absint( $data['auto_hide_delay'] );
		}

		if ( isset( $data['cookie_expiry_days'] ) ) {
			$data['cookie_expiry_days'] = max( 1, absint( $data['cookie_expiry_days'] ) );
		}

		// Overlay opacity clamp (0..1)
		if ( isset( $data['overlay_opacity'] ) ) {
			$val = is_numeric( $data['overlay_opacity'] ) ? (float) $data['overlay_opacity'] : 0.35;
			if ( $val < 0 ) {
				$val = 0; }
			if ( $val > 1 ) {
				$val = 1; }
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
			'a'      => array(
				'href'   => array(),
				'rel'    => array(),
				'target' => array(),
				'role'   => array(),
				'class'  => array(),
				'id'     => array(),
			),
			'button' => array(
				'type'  => array(),
				'class' => array(),
				'id'    => array(),
				'name'  => array(),
				'value' => array(),
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
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
		}
		?>
		<div class="wrap atr-scb-settings-wrap" id="<?php echo esc_attr( $this->plugin_slug ); ?>">
			<?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display
			if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_key( wp_unslash( $_GET['settings-updated'] ) ) ) :
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
			if ( isset( $_GET['atr_cnpurged'] ) && '1' === sanitize_key( wp_unslash( $_GET['atr_cnpurged'] ) ) ) :
				?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Caches purged and assets refreshed.', 'atr-cookie-notice' ); ?></p></div>
			<?php endif; ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
			if ( isset( $_GET['atr_cnreset'] ) && '1' === sanitize_key( wp_unslash( $_GET['atr_cnreset'] ) ) ) :
				?>
			<div class="notice notice-warning is-dismissible"><p><?php echo esc_html__( 'Settings have been reset to defaults.', 'atr-cookie-notice' ); ?></p></div>
			<?php endif; ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only
			if ( isset( $_GET['atr_cnstylereset'] ) && '1' === sanitize_key( wp_unslash( $_GET['atr_cnstylereset'] ) ) ) :
				?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Styling & Appearance settings have been reset to defaults.', 'atr-cookie-notice' ); ?></p></div>
			<?php endif; ?>

			<h2><?php echo esc_html__( 'Cookie Notice Settings', 'atr-cookie-notice' ); ?></h2>
			<p><?php echo esc_html__( 'Configure the cookie notice behavior and appearance.', 'atr-cookie-notice' ); ?></p>

			<div class="atr-scb-tools">
				<h2><?php echo esc_html__( 'Tools', 'atr-cookie-notice' ); ?></h2>
				<div class="notice notice-warning" style="margin:10px 0;">
					<p><strong><?php echo esc_html__( 'Heads up:', 'atr-cookie-notice' ); ?></strong>
					<?php echo esc_html__( 'Caching/optimization plugins (and some CDNs) may minify, combine, defer, or delay JavaScript. That can prevent the consent banner from loading or cause buttons to stop responding.', 'atr-cookie-notice' ); ?>
					</p>
					<p><?php echo esc_html__( 'If the banner does not appear or behave correctly, exclude the following files from any "Minify/Combine", "Defer/Delay/Async", and CDN minification features:', 'atr-cookie-notice' ); ?></p>
					<ul style="margin-left:18px;list-style:disc;">
						<li><code>wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-simple.js</code></li>
						<li><code>wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-public.js</code></li>
						<li><code>wp-content/plugins/atr-cookie-notice/public/js/atr-cookie-notice-drag.js</code></li>
					</ul>
					<p><?php echo esc_html__( 'After updating cache settings, purge your caches and reload the site in a private window to verify.', 'atr-cookie-notice' ); ?></p>
				</div>
				<p><?php echo esc_html__( 'Use this to purge common caches and refresh plugin asset versions (bypass stale caches).', 'atr-cookie-notice' ); ?></p>
				<?php $purge_url = wp_nonce_url( admin_url( 'admin-post.php?action=atr_cookie_notice_purge_caches' ), 'atr_cookie_notice_purge_caches' ); ?>
				<a href="<?php echo esc_url( $purge_url ); ?>" class="button button-primary"><?php echo esc_html__( 'Purge caches and refresh assets', 'atr-cookie-notice' ); ?></a>
				<?php $reset_style_url = wp_nonce_url( admin_url( 'admin-post.php?action=atr_cookie_notice_reset_style' ), 'atr_cookie_notice_reset_style' ); ?>
				<a href="<?php echo esc_url( $reset_style_url ); ?>" class="button button-link-delete atr-scb-reset-style-btn" id="atr-scb-reset-style-btn"><?php echo esc_html__( 'Reset Styling to defaults', 'atr-cookie-notice' ); ?></a>
			</div>

			<!-- Live Preview: wrapper contains only the preview block -->
			<div class="atr-scb-live-preview atr-scb-preview-top" style="margin:0 0 20px 0;">
				<h2><?php echo esc_html__( 'Live Preview', 'atr-cookie-notice' ); ?></h2>
				<p class="description"><?php echo esc_html__( 'This preview reflects Styling & Appearance settings (visuals only). Save changes to apply on the frontend.', 'atr-cookie-notice' ); ?></p>
				<div id="atr-scb-preview" class="atr-scb-preview">
					<div id="scb-banner" class="scb-banner scb-banner-bottom scb-mode-full" style="position:relative;transform:none;opacity:1;visibility:visible;">
						<div class="scb-modal">
							<div class="scb-content">
								<div class="scb-text">
									<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
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
			</div>

			<!-- Tab navigation starts -->
			<h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
				<?php
				foreach ( $this->settings as $section => $data ) {
					$section_id = sanitize_key( $section );
					$title      = isset( $data['title'] ) ? $data['title'] : '';
					echo '<a href="#' . esc_attr( $section_id ) . '" class="nav-tab">' . esc_html( $title ) . '</a>';
				}
				?>
			</h2>
			<?php $this->do_script_for_tabbed_nav(); ?>
			<!-- Tab navigation ends -->

			<form action="options.php" method="POST">
				<?php settings_fields( $this->plugin_slug . '_options' ); ?>
				<div class="settings-container">
					<?php do_settings_sections( $this->plugin_slug ); ?>
				</div>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Print jQuery script for tabbed navigation
	 *
	 * @return void
	 */
	private function do_script_for_tabbed_nav() {
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
				'page'        => $this->plugin_name,
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

	private function get_allowed_html() {
		return array(
			'div'      => array(
				'class' => array(),
				'id'    => array(),
			),
			'input'    => array(
				'id'                 => array(),
				'class'              => array(),
				'type'               => array(),
				'name'               => array(),
				'value'              => array(),
				'checked'            => array(),
				'placeholder'        => array(),
				'multiple'           => array(),
				'data-default-color' => array(),
			),
			'select'   => array(
				'id'       => array(),
				'class'    => array(),
				'name'     => array(),
				'multiple' => array(),
			),
			'option'   => array(
				'value'    => array(),
				'selected' => array(),
			),
			'textarea' => array(
				'id'          => array(),
				'class'       => array(),
				'name'        => array(),
				'rows'        => array(),
				'cols'        => array(),
				'placeholder' => array(),
			),
			'label'    => array(
				'for'   => array(),
				'class' => array(),
			),
			'span'     => array(
				'class' => array(),
				'id'    => array(),
			),
			'p'        => array(
				'class' => array(),
			),
			'br'       => array(),
			'code'     => array(),
			'strong'   => array(),
			'h2'       => array(
				'class' => array(),
				'style' => array(),
			),
			'h3'       => array(
				'class' => array(),
			),
			'ul'       => array(
				'class' => array(),
			),
			'li'       => array(
				'class' => array(),
			),
			'ol'       => array(
				'class' => array(),
			),
			'a'        => array(
				'href'   => array(),
				'class'  => array(),
				'id'     => array(),
				'target' => array(),
				'rel'    => array(),
			),
			'button'   => array(
				'type'  => array(),
				'class' => array(),
				'id'    => array(),
				'name'  => array(),
				'value' => array(),
			),
		);
	}
}
