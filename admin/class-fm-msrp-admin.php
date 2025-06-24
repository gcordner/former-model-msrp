<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://geoffcordner.net
 * @since      1.0.0
 *
 * @package    Fm_Msrp
 * @subpackage Fm_Msrp/admin
 */

namespace FormerModel\MSRP;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fm_Msrp
 * @subpackage Fm_Msrp/admin
 * @author     Geoff Cordner <geoffcordner@gmail.com>
 */
class Fm_Msrp_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fm_Msrp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fm_Msrp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fm-msrp-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'fm-msrp-admin',
			plugin_dir_url( __FILE__ ) . 'js/index.js', // eventually from /build.
			array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
			FM_MSRP_VERSION,
			true
		);
	}
	/**
	 * Register the admin menu for the plugin.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'FM MSRP', 'fm-msrp' ),
			__( 'FM MSRP', 'fm-msrp' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'fm-msrp',
			function () {
				echo '<div id="fm-msrp-settings-root"></div>';
			},
			'dashicons-tag', // icon.
			56 // position (below WooCommerce but above Settings).
		);
	}
}
