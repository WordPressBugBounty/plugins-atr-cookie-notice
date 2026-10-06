<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/public
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Cookie_Notice_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Utilities helper.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Atr_Cookie_Notice_Utils $utils Utilities helper.
	 */
	private $utils;

	/**
	 * Banner enabled status.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $is_banner_enabled    True if banner is enabled, false otherwise.
	 */
	private $is_banner_enabled;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $utils = null)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->utils = $utils instanceof Atr_Cookie_Notice_Utils ? $utils : new Atr_Cookie_Notice_Utils( $this->plugin_name, $this->version );
		
		// Initialize banner enabled status
		$this->is_banner_enabled = $this->atr_cookie_notice_check_banner_enabled();
	}

	/**
	 * Get plugin settings with defaults
	 *
	 * @since    1.0.0
	 * @return   array    Plugin settings
	 */
	private function atr_cookie_notice_get_plugin_settings()
	{
		$settings = is_object( $this->utils ) ? $this->utils->get_all_options() : array();
		return $settings;
	}

	/**
	 * Determine current consent mode: 'full' or 'simple'.
	 *
	 * @since    2.0.0
	 * @return   string
	 */
	private function atr_cookie_notice_get_mode()
	{
		$settings = $this->atr_cookie_notice_get_plugin_settings();
		$mode = isset( $settings['banner_mode'] ) ? sanitize_text_field( $settings['banner_mode'] ) : 'full';
		return in_array( $mode, array( 'full', 'simple' ), true ) ? $mode : 'full';
	}

	/**
	 * Check if banner is enabled from settings
	 *
	 * @since    1.0.0
	 * @return   bool    True if banner is enabled, false otherwise
	 */
	private function atr_cookie_notice_check_banner_enabled()
	{
		$settings = $this->atr_cookie_notice_get_plugin_settings();
		return isset($settings['enable_banner']) && $settings['enable_banner'] === 'on';
	}

	/**
	 * Get banner position class from settings
	 *
	 * @since    1.0.0
	 * @return   string    CSS class for banner position
	 */
	private function atr_cookie_notice_get_banner_position_class()
	{
		$settings = $this->atr_cookie_notice_get_plugin_settings();
		$position = isset($settings['banner_position']) ? $settings['banner_position'] : 'bottom';
		return 'scb-banner-' . sanitize_html_class($position);
	}

    /**
     * Convert hex color (#rrggbb) to RGB array.
     */
    private function atr_hex_to_rgb($hex)
    {
        $hex = trim((string) $hex);
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }
        if (strlen($hex) !== 6) {
            return array(0,0,0);
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return array($r, $g, $b);
    }

    /**
     * Allowed HTML tags/attributes for customizable banner content.
     */
    private function atr_cookie_notice_get_allowed_frontend_html() {
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
                'style' => array(),
            ),
            'br'     => array(),
            'p'      => array(
                'class' => array(),
            ),
        );
    }

    /**
     * Replace tokens in custom HTML with safe defaults and translatable strings.
     *
     * Supported tokens:
     * - {site_name}, {privacy_url}
     * - [ok_button], [privacy_link] (simple)
     * - [accept_all_button], [reject_button], [preferences_button] (full)
     */
    private function atr_cookie_notice_replace_tokens( $html, $mode ) {
        $site_name   = get_bloginfo('name');
        $privacy_url = get_privacy_policy_url();
        if ( empty( $privacy_url ) ) {
            $privacy_url = '#';
        }
        $privacy_title = '';
        $privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
        if ( $privacy_page_id > 0 ) {
            $privacy_title = get_the_title( $privacy_page_id );
        }
        if ( '' === $privacy_title ) {
            $privacy_title = __( 'Privacy Policy', 'atr-cookie-notice' );
        }
        $privacy_link_full = '<a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener" role="link">' . esc_html( $privacy_title ) . '</a>';

        // Basic replacements
        $html = str_replace('{site_name}', esc_html($site_name), (string) $html);
        $html = str_replace('{privacy_url}', esc_url($privacy_url), $html);
        $html = str_replace('{privacy_link}', $privacy_link_full, $html);

        // Map button/link tokens to safe HTML
        $replacements = array();
        if ( 'simple' === $mode ) {
            $replacements['[ok_button]'] = '<button id="scb-btn-ok" class="scb-btn scb-btn-primary scb-btn-ok" type="button">' . esc_html__( 'OK', 'atr-cookie-notice' ) . '</button>';
            $replacements['[privacy_link]'] = '<a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener" class="scb-btn scb-btn-link">' . esc_html__( 'Privacy Policy', 'atr-cookie-notice' ) . '</a>';
        } else {
            $replacements['[accept_all_button]'] = '<button id="scb-btn-accept-all" class="scb-btn scb-btn-primary scb-btn-accept-all" type="button"><span class="scb-btn-text">' . esc_html__( 'Accept All', 'atr-cookie-notice' ) . '</span><span class="scb-btn-loading" style="display: none;">' . esc_html__( 'Loading...', 'atr-cookie-notice' ) . '</span></button>';
            $replacements['[reject_button]'] = '<button id="scb-btn-reject" class="scb-btn scb-btn-secondary scb-btn-reject" type="button">' . esc_html__( 'Reject Non-Essential', 'atr-cookie-notice' ) . '</button>';
            $replacements['[preferences_button]'] = '<button id="scb-btn-custom" class="scb-btn scb-btn-link scb-btn-custom" type="button">' . esc_html__( 'Preferences', 'atr-cookie-notice' ) . '</button>';
        }

        if ( ! empty( $replacements ) ) {
            $html = strtr( $html, $replacements );
        }

        return $html;
    }

    /**
     * Ensure required buttons exist by ID; if missing, append defaults.
     *
     * Required IDs:
     * - simple: scb-btn-ok
     * - full: scb-btn-accept-all, scb-btn-reject, scb-btn-custom
     */
    private function atr_cookie_notice_actions_requirements_enforce( $html, $mode ) {
        $html = (string) $html;
        if ( 'simple' === $mode ) {
            if ( ! preg_match( '/id\\s*=\\s*["\\\']scb-btn-ok["\\\']/i', $html ) ) {
                $html .= ' ' . '<button id="scb-btn-ok" class="scb-btn scb-btn-primary scb-btn-ok" type="button">' . esc_html__( 'OK', 'atr-cookie-notice' ) . '</button>';
            }
            return $html;
        }

        // full mode
        if ( ! preg_match( '/id\\s*=\\s*["\\\']scb-btn-accept-all["\\\']/i', $html ) ) {
            $html .= ' ' . '<button id="scb-btn-accept-all" class="scb-btn scb-btn-primary scb-btn-accept-all" type="button"><span class="scb-btn-text">' . esc_html__( 'Accept All', 'atr-cookie-notice' ) . '</span><span class="scb-btn-loading" style="display: none;">' . esc_html__( 'Loading...', 'atr-cookie-notice' ) . '</span></button>';
        }
        if ( ! preg_match( '/id\\s*=\\s*["\\\']scb-btn-reject["\\\']/i', $html ) ) {
            $html .= ' ' . '<button id="scb-btn-reject" class="scb-btn scb-btn-secondary scb-btn-reject" type="button">' . esc_html__( 'Reject Non-Essential', 'atr-cookie-notice' ) . '</button>';
        }
        if ( ! preg_match( '/id\\s*=\\s*["\\\']scb-btn-custom["\\\']/i', $html ) ) {
            $html .= ' ' . '<button id="scb-btn-custom" class="scb-btn scb-btn-link scb-btn-custom" type="button">' . esc_html__( 'Preferences', 'atr-cookie-notice' ) . '</button>';
        }
        return $html;
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_enqueue_styles()
	{
		// Only enqueue styles if banner is enabled
		if (!$this->is_banner_enabled) {
			return;
		}

		$settings = $this->atr_cookie_notice_get_plugin_settings();
		$asset_ver = ! empty( $settings['asset_buster'] ) ? $this->version . '-' . absint( $settings['asset_buster'] ) : $this->version;
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/atr-cookie-notice-public.css', array(), $asset_ver, 'all');

		// Apply styling settings via CSS variables
		$primary_color   = isset($settings['primary_color']) ? trim($settings['primary_color']) : '';
		$secondary_color = isset($settings['secondary_color']) ? trim($settings['secondary_color']) : '';
		$text_color      = isset($settings['text_color']) ? trim($settings['text_color']) : '';
		$bg_color        = isset($settings['background_color']) ? trim($settings['background_color']) : '';
		$link_color      = isset($settings['link_color']) ? trim($settings['link_color']) : '';
		$font_family     = isset($settings['font_family']) ? trim($settings['font_family']) : '';
		$base_font_size  = isset($settings['base_font_size']) ? absint($settings['base_font_size']) : 0;
		$font_weight     = isset($settings['font_weight']) ? sanitize_text_field($settings['font_weight']) : '';
		$button_weight   = isset($settings['button_font_weight']) ? sanitize_text_field($settings['button_font_weight']) : '';
		$uppercase       = isset($settings['uppercase_buttons']) && $settings['uppercase_buttons'] === 'on';
		$radius          = isset($settings['border_radius']) ? absint($settings['border_radius']) : 0;
		$modal_max       = isset($settings['modal_max_width']) ? absint($settings['modal_max_width']) : 0;
		$pad_y           = isset($settings['container_padding_y']) ? absint($settings['container_padding_y']) : 0;
		$pad_x           = isset($settings['container_padding_x']) ? absint($settings['container_padding_x']) : 0;
		$gap             = isset($settings['gap_between_elements']) ? absint($settings['gap_between_elements']) : 0;
		$direction       = isset($settings['layout_direction']) ? sanitize_text_field($settings['layout_direction']) : '';
		$z_index         = isset($settings['z_index']) ? absint($settings['z_index']) : 0;
		$overlay_hex     = isset($settings['overlay_color']) ? trim($settings['overlay_color']) : '';
		$overlay_opacity = isset($settings['overlay_opacity']) ? (float) $settings['overlay_opacity'] : 0;
		$pri_btn_bg      = isset($settings['primary_button_bg_color']) ? trim($settings['primary_button_bg_color']) : '';
		$pri_btn_text    = isset($settings['primary_button_text_color']) ? trim($settings['primary_button_text_color']) : '';
		$sec_btn_bg      = isset($settings['secondary_button_bg_color']) ? trim($settings['secondary_button_bg_color']) : '';
		$sec_btn_text    = isset($settings['secondary_button_text_color']) ? trim($settings['secondary_button_text_color']) : '';
		$shadow_preset   = isset($settings['shadow_preset']) ? sanitize_text_field($settings['shadow_preset']) : '';
		$custom_css      = isset($settings['custom_css']) ? sanitize_textarea_field( $settings['custom_css'] ) : '';
		$force_override  = ! empty( $settings['force_override_theme_styles'] ) && $settings['force_override_theme_styles'] === 'on';

		$vars = array();
		if ($primary_color)   { $vars['--scb-primary'] = $primary_color; }
		if ($secondary_color) { $vars['--scb-secondary'] = $secondary_color; }
		if ($text_color)      { $vars['--scb-text'] = $text_color; }
		if ($bg_color)        { $vars['--scb-bg'] = $bg_color; }
		if ($link_color)      { $vars['--scb-link'] = $link_color; }
		if ($font_family)     { $vars['--scb-font-family'] = $font_family; }
		if ($base_font_size)  { $vars['--scb-font-size'] = $base_font_size . 'px'; }
		if ($font_weight)     { $vars['--scb-font-weight'] = $font_weight; }
		if ($button_weight)   { $vars['--scb-btn-weight'] = $button_weight; }
		if ($uppercase)       { $vars['--scb-btn-transform'] = 'uppercase'; }
		if ($radius)          { $vars['--scb-radius'] = $radius . 'px'; $vars['--scb-btn-radius'] = $radius . 'px'; }
		if ($modal_max)       { $vars['--scb-modal-max'] = $modal_max . 'px'; }
		if ($pad_y)           { $vars['--scb-pad-y'] = $pad_y . 'px'; }
		if ($pad_x)           { $vars['--scb-pad-x'] = $pad_x . 'px'; }
		if ($gap)             { $vars['--scb-gap'] = $gap . 'px'; }
		if ($direction)       { $vars['--scb-direction'] = $direction; }
		if ($z_index)         { $vars['--scb-z-index'] = (string) $z_index; }
		if ($pri_btn_bg)      { $vars['--scb-primary-btn-bg'] = $pri_btn_bg; }
		if ($pri_btn_text)    { $vars['--scb-primary-btn-text'] = $pri_btn_text; }
		if ($sec_btn_bg)      { $vars['--scb-secondary-btn-bg'] = $sec_btn_bg; }
		if ($sec_btn_text)    { $vars['--scb-secondary-btn-text'] = $sec_btn_text; }
		// Shadow preset
		if ($shadow_preset) {
			$shadow_map = array(
				'none' => 'none',
				'sm'   => '0 4px 12px rgba(0,0,0,.12)',
				'md'   => '0 8px 24px rgba(0,0,0,.15)',
				'lg'   => '0 12px 36px rgba(0,0,0,.18)',
			);
			if (isset($shadow_map[$shadow_preset])) {
				$vars['--scb-shadow'] = $shadow_map[$shadow_preset];
			}
		}
		// Overlay color to rgb
		if ($overlay_hex) {
			$rgb = $this->atr_hex_to_rgb($overlay_hex);
			$vars['--scb-overlay-rgb'] = implode(',', array_map('intval', $rgb));
		}
		if ($overlay_opacity > 0) {
			$vars['--scb-overlay-opacity'] = (string) max(0, min(1, $overlay_opacity));
		}

		$inline_css = '';
		if (!empty($vars)) {
			$inline_css .= '#scb-banner{';
			foreach ($vars as $k => $v) {
				$inline_css .= $k . ':' . esc_html($v) . ';';
			}
			$inline_css .= '}';
		}
		// Force override high-specificity declarations
		if ($force_override) {
			$inline_css .= '#scb-banner.scb-force .scb-btn.scb-btn-primary{background:var(--scb-primary-btn-bg, var(--scb-primary, #0b74de)) !important;color:var(--scb-primary-btn-text,#fff) !important;border-color:transparent !important;}';
			$inline_css .= '#scb-banner.scb-force .scb-btn.scb-btn-secondary{background:var(--scb-secondary-btn-bg,#f7f7f7) !important;color:var(--scb-secondary-btn-text,#333) !important;}';
			$inline_css .= '#scb-banner.scb-force{background:var(--scb-bg,#fff) !important;color:var(--scb-text,#222) !important;box-shadow:var(--scb-shadow,0 8px 24px rgba(0,0,0,.15)) !important;}';
		}
		if (!empty($custom_css)) {
			$inline_css .= "\n" . $custom_css;
		}
		if ($inline_css) {
			wp_add_inline_style($this->plugin_name, $inline_css);
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_enqueue_scripts()
	{
		// Only enqueue scripts if banner is enabled
		if (!$this->is_banner_enabled) {
			return;
		}

		$mode = $this->atr_cookie_notice_get_mode();
		$settings = $this->atr_cookie_notice_get_plugin_settings();
		$asset_ver = ! empty( $settings['asset_buster'] ) ? $this->version . '-' . absint( $settings['asset_buster'] ) : $this->version;
		if ( 'simple' === $mode ) {
			wp_enqueue_script($this->plugin_name . '-simple', plugin_dir_url(__FILE__) . 'js/atr-cookie-notice-simple.js', array(), $asset_ver, true);
		} else {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/atr-cookie-notice-public.js', array('jquery'), $asset_ver, true);
		}


		// Check if we're on the privacy policy page
		$is_privacy_page = false;
		$privacy_policy_url = get_privacy_policy_url();

		// More robust privacy page detection
		if ($privacy_policy_url) {
			$current_url = get_permalink();
			$privacy_url_parts = wp_parse_url($privacy_policy_url);
			$current_url_parts = wp_parse_url($current_url);

			// Check if current page matches privacy policy URL
			if (
				$current_url === $privacy_policy_url ||
				(isset($privacy_url_parts['path']) && isset($current_url_parts['path']) &&
					$privacy_url_parts['path'] === $current_url_parts['path'])
			) {
				$is_privacy_page = true;
			}
		}

		// Pass settings to JavaScript
		$settings = $this->atr_cookie_notice_get_plugin_settings();

		// Translate plain text and prepend the emoji (keeps emoji out of msgid)
		$privacy_note_text = '💡 ' . __( 'You can read this page while deciding about cookies', 'atr-cookie-notice' );
		$cookie_expiry_days = isset($settings['cookie_expiry_days']) ? absint($settings['cookie_expiry_days']) : 365;
		$auto_hide_delay = isset($settings['auto_hide_delay']) ? absint($settings['auto_hide_delay']) : 0;
		$enable_debug = ! empty( $settings['enable_debug'] ) && $settings['enable_debug'] === 'on';
		$localize_handle = ( 'simple' === $mode ) ? $this->plugin_name . '-simple' : $this->plugin_name;
		wp_localize_script(
			$localize_handle,
			'atrCookieNoticeSettings',
			array(
				'cookieName' => 'atr_cookie_notice_consent',
				'decisionCookieName' => 'atr_cookie_notice_consent_given',
				'expiryDays' => $cookie_expiry_days,
				'autoHideDelay' => $auto_hide_delay,
				'enableDebug' => $enable_debug,
				'siteName' => get_bloginfo('name'),
				'isPrivacyPage' => $is_privacy_page,
				'privacyPolicyUrl' => $privacy_policy_url,
				'privacyNoteText' => $privacy_note_text,
				'mode' => $mode,
			)
		);
	}

	/**
	 * Block tracking scripts before consent is given.
	 *
	 * @since    1.0.0
	 */
	public function block_tracking_scripts()
	{
		// Only block scripts if banner is enabled
		if (!$this->is_banner_enabled) {
			return;
		}

		// In simple mode, never block tracking scripts
		if ( 'simple' === $this->atr_cookie_notice_get_mode() ) {
			return;
		}

		// Check if user has given consent - if yes, don't block anything
		if ($this->has_consent()) {
			return;
		}

		// Block Google Analytics
		$this->block_google_analytics();

		// Block Facebook Pixel
		$this->block_facebook_pixel();

		// Block other common tracking scripts
		$this->block_common_tracking();
	}

	/**
	 * Check if user has given consent for cookies.
	 *
	 * @since    1.0.0
	 * @return bool
	 */
	private function has_consent()
	{

		// Support both current and previous cookie names for compatibility
		$cookie_names = array( 'atr_cookie_notice_consent', 'scb_consent' );
		foreach ( $cookie_names as $cookie_name ) {
			// Read cookie without touching superglobal directly to satisfy WPCS
			$raw_cookie = filter_input( INPUT_COOKIE, $cookie_name, FILTER_UNSAFE_RAW );
			if ( null === $raw_cookie || false === $raw_cookie || '' === $raw_cookie ) {
				continue;
			}
			// Sanitize and bound length (JSON structure validated after decode)
			$raw_cookie = sanitize_textarea_field( (string) $raw_cookie );
			if ( strlen( $raw_cookie ) > 4096 ) {
				continue;
			}
			$consent_data = json_decode( $raw_cookie, true );
			if ( is_array( $consent_data ) && isset( $consent_data['essential'] ) && true === $consent_data['essential'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Block Google Analytics scripts.
	 *
	 * @since    1.0.0
	 */
	private function block_google_analytics()
	{
		// Block gtag
		wp_enqueue_script('scb-block-gtag', '', array(), $this->version, false);
		wp_add_inline_script('scb-block-gtag', '
			window.gtag = function() { return; };
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push = function() { return; };
		');

		// Block Google Analytics
		wp_enqueue_script('scb-block-ga', '', array(), $this->version, false);
		wp_add_inline_script('scb-block-ga', '
			window.ga = function() { return; };
			window._gaq = window._gaq || [];
			window._gaq.push = function() { return; };
		');
	}

	/**
	 * Block Facebook Pixel scripts.
	 *
	 * @since    1.0.0
	 */
	private function block_facebook_pixel()
	{
		wp_enqueue_script('scb-block-fbq', '', array(), $this->version, false);
		wp_add_inline_script('scb-block-fbq', '
			window.fbq = function() { return; };
			window._fbq = window._fbq || [];
			window._fbq.push = function() { return; };
		');
	}

	/**
	 * Block other common tracking scripts.
	 *
	 * @since    1.0.0
	 */
	private function block_common_tracking()
	{
		wp_enqueue_script('scb-block-tracking', '', array(), $this->version, false);
		wp_add_inline_script('scb-block-tracking', '
			window.track = function() { return; };
			window.tracking = function() { return; };
			window.analytics = function() { return; };
		');
	}

	/**
	 * Inject the cookie consent banner HTML.
	 *
	 * @since    1.0.0
	 */
	public function inject_banner_html()
	{
		// Only show banner if enabled
		if (!$this->is_banner_enabled) {
			return;
		}

		// Don't show banner if user already has consent
		if ($this->has_consent()) {
			return;
		}

		$mode = $this->atr_cookie_notice_get_mode();
		if ( 'simple' === $mode ) {
?>
		<div id="scb-banner" class="scb-banner scb-mode-simple <?php echo esc_attr($this->atr_cookie_notice_get_banner_position_class()); ?><?php echo !empty($settings['force_override_theme_styles']) && $settings['force_override_theme_styles']==='on' ? ' scb-force' : ''; ?>">
			<div class="scb-modal">
				<div class="scb-content">
					<div class="scb-text">
<?php
                        $settings   = $this->atr_cookie_notice_get_plugin_settings();
                        $allowed    = $this->atr_cookie_notice_get_allowed_frontend_html();
                        $text_html  = '';
                        if ( ! empty( $settings['simple_text_html'] ) ) {
                            $custom   = $this->atr_cookie_notice_replace_tokens( $settings['simple_text_html'], 'simple' );
                            $text_html = wp_kses( $custom, $allowed );
                        }
                        $text_html = apply_filters( 'atr_cookie_notice_text_html_simple', $text_html, $settings );
                        if ( $text_html ) {
                            echo $text_html; // already sanitized
                        } else {
?>
						<strong><?php echo esc_html(get_bloginfo('name')); ?></strong>
						<?php echo esc_html(__('We use cookies to improve your experience. By continuing, you accept cookies.', 'atr-cookie-notice')); ?>
<?php
                        }
?>
					</div>
					<div class="scb-actions">
<?php
                        $actions_html = '';
                        if ( ! empty( $settings['simple_actions_html'] ) ) {
                            $custom = $this->atr_cookie_notice_replace_tokens( $settings['simple_actions_html'], 'simple' );
                            $custom = $this->atr_cookie_notice_actions_requirements_enforce( $custom, 'simple' );
                            $actions_html = wp_kses( $custom, $allowed );
                        }
                        $actions_html = apply_filters( 'atr_cookie_notice_actions_html_simple', $actions_html, $settings );
                        if ( $actions_html ) {
                            echo $actions_html; // already sanitized
                        } else {
?>
						<button id="scb-btn-ok" class="scb-btn scb-btn-primary scb-btn-ok" type="button"><?php echo esc_html(__('OK', 'atr-cookie-notice')); ?></button>
						<a href="<?php echo esc_url(get_privacy_policy_url() ?: '#'); ?>" target="_blank" rel="noopener" class="scb-btn scb-btn-link"><?php echo esc_html(__('Privacy Policy', 'atr-cookie-notice')); ?></a>
<?php
                        }
?>
					</div>
				</div>
			</div>
		</div>
<?php
			return;
		}

		// Load settings for category visibility and defaults
		$settings = $this->atr_cookie_notice_get_plugin_settings();
		$show_analytics = isset($settings['show_analytics_cookies']) && $settings['show_analytics_cookies'] === 'on';
		$show_marketing = isset($settings['show_marketing_cookies']) && $settings['show_marketing_cookies'] === 'on';
		// Defaults should be unchecked per requirement

?>
		<div id="scb-overlay" class="scb-overlay"></div>
		<div id="scb-banner" class="scb-banner scb-mode-full <?php echo esc_attr($this->atr_cookie_notice_get_banner_position_class()); ?><?php echo !empty($settings['force_override_theme_styles']) && $settings['force_override_theme_styles']==='on' ? ' scb-force' : ''; ?>">
			<div class="scb-modal">
				<div class="scb-header">
					<button type="button" class="scb-close" onclick="scbCloseModal()">&times;</button>
				</div>
				<div class="scb-content">
					<div class="scb-text">
<?php
                        $settings   = $this->atr_cookie_notice_get_plugin_settings();
                        $allowed    = $this->atr_cookie_notice_get_allowed_frontend_html();
                        $text_html  = '';
                        if ( ! empty( $settings['full_text_html'] ) ) {
                            $custom   = $this->atr_cookie_notice_replace_tokens( $settings['full_text_html'], 'full' );
                            $text_html = wp_kses( $custom, $allowed );
                        }
                        $text_html = apply_filters( 'atr_cookie_notice_text_html_full', $text_html, $settings );
                        if ( $text_html ) {
                            echo $text_html; // already sanitized
                        } else {
?>
						<strong><?php echo esc_html(get_bloginfo('name')); ?></strong>
						<?php echo esc_html(__('We use cookies to ensure the website functions properly and improve user experience. You can choose which types of cookies to enable.', 'atr-cookie-notice')); ?>
<?php
                        }
?>
					</div>
					<div class="scb-actions">
<?php
                        $actions_html = '';
                        if ( ! empty( $settings['full_actions_html'] ) ) {
                            $custom = $this->atr_cookie_notice_replace_tokens( $settings['full_actions_html'], 'full' );
                            $custom = $this->atr_cookie_notice_actions_requirements_enforce( $custom, 'full' );
                            $actions_html = wp_kses( $custom, $allowed );
                        }
                        $actions_html = apply_filters( 'atr_cookie_notice_actions_html_full', $actions_html, $settings );
                        if ( $actions_html ) {
                            echo $actions_html; // already sanitized
                        } else {
?>
						<button id="scb-btn-accept-all" class="scb-btn scb-btn-primary scb-btn-accept-all" type="button">
							<span class="scb-btn-text"><?php echo esc_html(__('Accept All', 'atr-cookie-notice')); ?></span>
							<span class="scb-btn-loading" style="display: none;"><?php echo esc_html(__('Loading...', 'atr-cookie-notice')); ?></span>
						</button>
						<button id="scb-btn-reject" class="scb-btn scb-btn-secondary scb-btn-reject" type="button">
							<?php echo esc_html(__('Reject Non-Essential', 'atr-cookie-notice')); ?>
						</button>
						<button id="scb-btn-custom" class="scb-btn scb-btn-link scb-btn-custom" type="button">
							<?php echo esc_html(__('Preferences', 'atr-cookie-notice')); ?>
						</button>
<?php
                        }
?>
					</div>
					<div id="scb-settings" class="scb-settings">
						<form id="scb-form" class="scb-form">
							<fieldset>
								<legend><?php echo esc_html(__('Cookie Selection', 'atr-cookie-notice')); ?></legend>
								<label><input type="checkbox" name="essential" checked disabled> <?php echo esc_html(__('Essential (Required)', 'atr-cookie-notice')); ?></label><br>
								<?php if ( $show_analytics ) : ?>
								<label><input type="checkbox" name="analytics" value="analytics"> <?php echo esc_html(__('Analytics (Google Analytics)', 'atr-cookie-notice')); ?></label><br>
								<?php endif; ?>
								<?php if ( $show_marketing ) : ?>
								<label><input type="checkbox" name="marketing" value="marketing"> <?php echo esc_html(__('Marketing/Advertising (Facebook/Ads)', 'atr-cookie-notice')); ?></label><br>
								<?php endif; ?>
								<div class="scb-actions">
									<button id="scb-btn-save" class="scb-btn scb-btn-primary scb-btn-save" type="submit">
										<?php echo esc_html(__('Save Choices', 'atr-cookie-notice')); ?>
									</button>
									<button id="scb-btn-cancel" class="scb-btn scb-btn-secondary scb-btn-cancel" type="button">
										<?php echo esc_html(__('Cancel', 'atr-cookie-notice')); ?>
									</button>
								</div>
							</fieldset>
						</form>
					</div>
					<div class="scb-footer">
<?php
                        // Customizable footer (full mode)
                        $settings    = $this->atr_cookie_notice_get_plugin_settings();
                        $allowed     = $this->atr_cookie_notice_get_allowed_frontend_html();
                        $footer_html = '';
                        if ( ! empty( $settings['full_footer_html'] ) ) {
                            $custom      = $this->atr_cookie_notice_replace_tokens( $settings['full_footer_html'], 'full' );
                            $footer_html = wp_kses( $custom, $allowed );
                        }
                        $footer_html = apply_filters( 'atr_cookie_notice_footer_html_full', $footer_html, $settings );
                        if ( $footer_html ) {
                            echo $footer_html; // already sanitized
                        } else {
?>
						<div class="scb-more">
							<a href="<?php echo esc_url(get_privacy_policy_url() ?: '#'); ?>" target="_blank" rel="noopener" role="link"><?php echo esc_html(__('Privacy Policy', 'atr-cookie-notice')); ?></a>
						</div>
<?php
                        }
?>
					</div>
				</div>
			</div>
		</div>
<?php
	}
}
