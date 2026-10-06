<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/admin
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Cookie_Notice_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $utils = null ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->utils = $utils instanceof Atr_Cookie_Notice_Utils ? $utils : null;
		$this->atr_cookie_notice_admin_settings_load_dependencies();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/atr-cookie-notice-admin.css', array(), $this->version, 'all' );
		// WP color picker styles for color inputs
		wp_enqueue_style( 'wp-color-picker' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/atr-cookie-notice-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );

		// Localize data for admin JS (AJAX URL, nonces, and copy)
		wp_localize_script(
			$this->plugin_name,
			'atrCookieNoticeAdmin',
			array(
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'dismissNonce'        => wp_create_nonce( 'atr_cookie_notice_dismiss_notice' ),
				'resetStyleConfirm'   => __( 'Are you sure you want to reset all Styling & Appearance settings to their defaults? This cannot be undone.', 'atr-cookie-notice' ),
			)
		);

	}

	/**
	 * Load the required dependencies for the Admin facing functionality.
	 * Registers the admin settings and page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function atr_cookie_notice_admin_settings_load_dependencies()
	{
		/**
		 * The class responsible for admin settings of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) .  'admin/class-atr-cookie-notice-admin-settings.php';
	}

    /**
     * Enqueue WP color picker as early as possible so other scripts that
     * (incorrectly) assume globals like wpColorPickerL10n exist won't break.
     */
    public function atr_cookie_notice_enqueue_colorpicker_early() {
        if ( function_exists( 'wp_enqueue_style' ) ) {
            wp_enqueue_style( 'wp-color-picker' );
        }
        if ( function_exists( 'wp_enqueue_script' ) ) {
            wp_enqueue_script( 'wp-color-picker' );
        }
    }

    /**
     * Schedule and display a friendly 5-star review notice after 14 days.
     */
    public function atr_cookie_notice_maybe_schedule_review_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( ! get_option( 'atr_cookie_notice_activation_time' ) ) {
            update_option( 'atr_cookie_notice_activation_time', time() );
        }

        $activated = (int) get_option( 'atr_cookie_notice_activation_time' );
        $dismissed = get_option( 'atr_cookie_notice_dismiss_review', '0' ) === '1';
        $wait_seconds = (int) apply_filters( 'atr_cookie_notice_review_wait_seconds', 14 * DAY_IN_SECONDS );
        if ( $dismissed || ! $activated || time() <= $activated + $wait_seconds ) {
            return;
        }

        global $pagenow;
        // Also allow on this plugin's settings/docs pages
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'index.php' === $pagenow || 'plugins.php' === $pagenow || $page === 'atr-cookie-notice' || $page === 'atr-cookie-notice_docs' ) {
            add_action( 'admin_notices', array( $this, 'atr_cookie_notice_render_review_notice' ) );
        }
    }

    /**
     * Render the review request notice.
     */
    public function atr_cookie_notice_render_review_notice() {
        $review_url  = 'https://wordpress.org/support/plugin/atr-cookie-notice/reviews/?filter=5#new-post';
        $dismiss_url = wp_nonce_url( add_query_arg( 'atr_cookie_notice_dismiss_review', '1' ), 'atr_cookie_notice_dismiss_review' );
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php
                echo wp_kses(
                    sprintf(
                        /* translators: %s: Plugin name bold */
                        esc_html__( 'Enjoying %s? If it’s been helpful, please consider a 5‑star review on WordPress.org. Your feedback helps us improve. Thank you!', 'atr-cookie-notice' ),
                        '<strong>ATR Cookie Notice</strong>'
                    ),
                    array( 'strong' => array() )
                );
                ?>
                <br /><br />
                <a class="button button-primary" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $review_url ); ?>"><?php echo esc_html__( 'Leave a 5‑star review', 'atr-cookie-notice' ); ?></a>
                <a class="button button-secondary" href="<?php echo esc_url( $dismiss_url ); ?>"><?php echo esc_html__( 'No, thanks', 'atr-cookie-notice' ); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Handle dismissal of review notice (nonce‑protected).
     */
    public function atr_cookie_notice_handle_review_dismiss() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Using wp_verify_nonce below
        if ( isset( $_GET['atr_cookie_notice_dismiss_review'] ) ) {
            $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
            if ( wp_verify_nonce( $nonce, 'atr_cookie_notice_dismiss_review' ) ) {
                update_option( 'atr_cookie_notice_dismiss_review', '1' );
                wp_safe_redirect( remove_query_arg( array( 'atr_cookie_notice_dismiss_review', '_wpnonce' ) ) );
                exit;
            }
        }
    }

	/**
	 * AJAX handler to dismiss the admin notice for current user.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function atr_cookie_notice_dismiss_notice() {
		check_ajax_referer( 'atr_cookie_notice_dismiss_notice', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'atr_cookie_notice_notice_dismissed', 1 );
		wp_send_json_success();
	}

	/**
	 * Add helpful links in the plugin row meta (Plugins screen) – non-intrusive promotion.
	 *
	 * @since 1.0.0
	 * @param array  $links Existing meta links.
	 * @param string $file  Plugin file basename.
	 * @return array
	 */
	public function atr_cookie_notice_row_meta( $links, $file ) {
		$plugin_basename = 'atr-cookie-notice/atr-cookie-notice.php';
		if ( $file !== $plugin_basename ) {
			return $links;
		}

		$docs_link   = '<a href="' . esc_url( admin_url( 'admin.php?page=atr-cookie-notice_docs' ) ) . '">' . esc_html__( 'Documentation', 'atr-cookie-notice' ) . '</a>';
		$support_link = '<a href="' . esc_url( 'https://atarimtr.co.il/contact/' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Support', 'atr-cookie-notice' ) . '</a>';
		$review_link = '<a href="' . esc_url( 'https://wordpress.org/support/plugin/atr-cookie-notice/reviews/#new-post' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Leave a review ★★★★★', 'atr-cookie-notice' ) . '</a>';

		$links[] = $docs_link;
		$links[] = $support_link;
		$links[] = $review_link;
		return $links;
	}
}
