<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 *
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/includes
 *
 * @author     Darpan Kulkarni <plugins@darpankulkarni.in>
 */
class Awb_Cf7 {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Awb_Cf7_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

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
		if ( defined( 'AWB_CF7_VERSION' ) ) {
			$this->version = AWB_CF7_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'awb-cf7';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin. Create an instance of the loader.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-awb-cf7-loader.php';

		// The class responsible for defining internationalization functionality of the plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-awb-cf7-i18n.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-awb-cf7-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-awb-cf7-panel.php';

		// The class responsible for AWeber auth api calls
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-awb-cf7-auth.php';

		// The class responsible for AWeber api calls
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-awb-cf7-api.php';

		// Load HTTP client library
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		$this->loader = new Awb_Cf7_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Awb_Cf7_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Awb_Cf7_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Awb_Cf7_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$awb_cf7_panel = new Awb_Cf7_Panel();
		$this->loader->add_action( 'wpcf7_editor_panels', $awb_cf7_panel, 'awbc_panel_init' );

		$awb_cf7_auth = new Awb_Cf7_Auth();
		$this->loader->add_action( 'wp_ajax_awbc_get_access_token', $awb_cf7_auth, 'awbc_get_access_token' );
		$this->loader->add_action( 'wp_ajax_awbc_revoke_auth', $awb_cf7_auth, 'awbc_revoke_auth' );

		$awb_cf7_api = new Awb_Cf7_Api();
		$this->loader->add_action( 'wp_ajax_awbc_get_accounts', $awb_cf7_api, 'awbc_get_accounts' );
		$this->loader->add_action( 'wp_ajax_awbc_get_lists', $awb_cf7_api, 'awbc_get_lists' );
		$this->loader->add_action( 'wp_ajax_awbc_connect_list', $awb_cf7_api, 'awbc_connect_list' );

		$this->loader->add_action( 'wpcf7_save_contact_form', $awb_cf7_api, 'awbc_save_subscriber' );
		$this->loader->add_action( 'wpcf7_before_send_mail', $awb_cf7_api, 'awbc_save_subscriber_remote' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Awb_Cf7_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
