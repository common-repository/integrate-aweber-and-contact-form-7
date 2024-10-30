<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/admin
 * @author     Darpan Kulkarni <plugins@darpankulkarni.in>
 */
class Awb_Cf7_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// Enqueue plugin style
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/awb-cf7-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Enqueue plugin script
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/awb-cf7-admin.js', array( 'jquery' ), $this->version, false );

		// Localize plugin script
		wp_localize_script( $this->plugin_name, 'awbc', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	}

}
