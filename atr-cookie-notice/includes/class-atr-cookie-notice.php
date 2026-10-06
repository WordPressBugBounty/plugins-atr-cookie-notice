<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/includes
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Cookie_Notice {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Atr_Cookie_Notice_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;


	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Shared utilities instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Atr_Cookie_Notice_Utils $utils Utilities helper.
	 */
	protected $utils;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ATR_COOKIE_NOTICE_VERSION' ) ) {
			$this->version = ATR_COOKIE_NOTICE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'atr-cookie-notice';

		$this->atr_cookie_notice_load_dependencies();

		// Initialize shared utilities once (after dependencies are loaded).
		$this->utils = new Atr_Cookie_Notice_Utils( $this->plugin_name, $this->version );
		$this->atr_cookie_notice_set_locale();
		$this->atr_cookie_notice_define_admin_hooks();
		$this->atr_cookie_notice_define_public_hooks();
		// Forms integration removed

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Atr_Cookie_Notice_Loader. Orchestrates the hooks of the plugin.
	 * - Atr_Cookie_Notice_i18n. Defines internationalization functionality.
	 * - Atr_Cookie_Notice_Admin. Defines all hooks for the admin area.
	 * - Atr_Cookie_Notice_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function atr_cookie_notice_load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-atr-cookie-notice-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-atr-cookie-notice-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-atr-cookie-notice-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-atr-cookie-notice-public.php';

		/**
		 * Utilities shared across the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-atr-cookie-notice-utils.php';

		$this->loader = new Atr_Cookie_Notice_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Atr_Cookie_Notice_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function atr_cookie_notice_set_locale() {

		$plugin_i18n = new Atr_Cookie_Notice_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'atr_cookie_notice_load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function atr_cookie_notice_define_admin_hooks() {

		$plugin_admin = new Atr_Cookie_Notice_Admin( $this->atr_cookie_notice_get_plugin_name(), $this->atr_cookie_notice_get_version(), $this->utils );

		// Ensure color picker is available early for any scripts that assume it exists
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'atr_cookie_notice_enqueue_colorpicker_early', 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'atr_cookie_notice_enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'atr_cookie_notice_enqueue_scripts' );
        // Review notice scheduling and dismissal handled within Admin class via admin_init hooks

		$plugin_settings = new Atr_Cookie_Notice_Admin_Settings($this->plugin_name, 'atr-cookie-notice', $this->version);
        $this->loader->add_action('admin_menu', $plugin_settings, 'add_submenu_item');
		$plugin_basename = $this->plugin_name . '/' . 'atr-cookie-notice.php';
		$this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_settings, 'add_action_links');

		// Row meta links on Plugins screen (Documentation, Support, Review)
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'atr_cookie_notice_row_meta', 10, 2 );

        // Review notice schedule and dismissal
        $this->loader->add_action( 'admin_init', $plugin_admin, 'atr_cookie_notice_maybe_schedule_review_notice' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'atr_cookie_notice_handle_review_dismiss' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function atr_cookie_notice_define_public_hooks() {

		$plugin_public = new Atr_Cookie_Notice_Public( $this->atr_cookie_notice_get_plugin_name(), $this->atr_cookie_notice_get_version(), $this->utils );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'atr_cookie_notice_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'atr_cookie_notice_enqueue_scripts' );

		$this->loader->add_action('wp_head', $plugin_public, 'block_tracking_scripts', 1);
		$this->loader->add_action('wp_footer', $plugin_public, 'block_tracking_scripts', 1);
		$this->loader->add_action('wp_footer', $plugin_public, 'inject_banner_html');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function atr_cookie_notice_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Atr_Cookie_Notice_Loader    Orchestrates the hooks of the plugin.
	 */
	public function atr_cookie_notice_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function atr_cookie_notice_get_version() {
		return $this->version;
	}


}
