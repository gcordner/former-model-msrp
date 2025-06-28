<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://geoffcordner.net
 * @since      1.0.0
 *
 * @package    Fm_Msrp
 * @subpackage Fm_Msrp/includes
 */
namespace FormerModel\MSRP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
 * @package    Fm_Msrp
 * @subpackage Fm_Msrp/includes
 * @author     Geoff Cordner <geoffcordner@gmail.com>
 */

/**
 * Class Fm_Msrp
 *
 * Handles adding and saving the List Price (MSRP) meta field on products.
 *
 * @since 1.0.0
 */
class Fm_Msrp {

	/**
	 * Prevents the simple product field from rendering twice.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var    bool
	 */
	private $rendered = false;

	/**
	 * Constructor.
	 *
	 * Hooks into WooCommerce admin to add and save the List Price fields.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Simple products.
		add_action( 'woocommerce_product_options_pricing', array( $this, 'add_simple_list_price_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_simple_list_price_field' ), 10, 1 );

		// Variable products.
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_variation_list_price_field' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_list_price_field' ), 10, 2 );
		add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_all_variation_list_price_fields' ), 10, 1 );

		// Add List Price to product variations.
		add_action( 'woocommerce_single_product_summary', array( $this, 'output_msrp_above_price' ), 9 );
		add_filter( 'woocommerce_available_variation', array( $this, 'add_list_price_to_variation_data' ), 10, 3 );

		// Enqueue frontend JS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ) );

		// Output custom CSS for List Price.
		add_action( 'wp_head', array( $this, 'output_custom_css' ) );

		// Admin interface setup.
		if ( is_admin() ) {
			$this->load_admin();
		}
	}

	/**
	 * Initialize the feature.
	 *
	 * Called on plugins_loaded via loader.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		// Only run if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// 1) Set up all the existing hooks (admin, frontend, metaboxes, etc.)
		new self();

		// 2) Load REST controller class file so WP can see it on non-admin requests too.
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-fm-msrp-rest-controller.php';

		// 3) Tell WP to register your REST routes at rest_api_init.
		add_action(
			'rest_api_init',
			function () {
				$controller = new \FormerModel\MSRP\Fm_Msrp_REST_Controller();
				$controller->register_routes();
			}
		);
	}

	/**
	 * Add the List Price field to simple products.
	 *
	 * @since 1.0.0
	 */
	public function add_simple_list_price_field() {
		if ( $this->rendered ) {
			return;
		}
		$this->rendered = true;

		// ← Grab the admin‐defined label, fallback to "List Price".
		$label = get_option( 'fm_msrp_label', __( 'List Price', 'fm-msrp' ) );

		woocommerce_wp_text_input(
			array(
				'id'          => '_list_price',
				'label'       => esc_html( $label ),
				'description' => __( 'Manufacturer\'s Suggested Retail Price.', 'fm-msrp' ),
				'desc_tip'    => true,
				'type'        => 'text',
				'data_type'   => 'price',
			)
		);
	}

	/**
	 * Save the List Price for simple products.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The product ID.
	 */
	public function save_simple_list_price_field( $post_id ) {
		// Security & capability checks.
		if ( ! isset( $_POST['woocommerce_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' )
			|| ! current_user_can( 'edit_products' )
		) {
			return;
		}

		if ( isset( $_POST['_list_price'] ) ) {
			$list_price = wc_clean( wp_unslash( $_POST['_list_price'] ) );

			if ( '' !== $list_price ) {
				update_post_meta( $post_id, '_list_price', $list_price );
			} else {
				delete_post_meta( $post_id, '_list_price' );
			}
		}
	}

	/**
	 * Add the List Price field to each variation.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $loop           Variation loop index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Variation post object.
	 */
	public function add_variation_list_price_field( $loop, $variation_data, $variation ) {
		echo '<div class="form-row form-row-full">'; // force full width row.

		// ← Same dynamic label for each variation.
		$label = get_option( 'fm_msrp_label', __( 'List Price', 'fm-msrp' ) );

		woocommerce_wp_text_input(
			array(
				'id'          => "variable_list_price[{$loop}]",
				'name'        => "variable_list_price[{$loop}]",
				'value'       => get_post_meta( $variation->ID, '_list_price', true ),
				'label'       => esc_html( $label ),
				'description' => __( 'Set the MSRP here. Click the “Update” button at the top of the page to save all variation List Prices.', 'fm-msrp' ),
				'desc_tip'    => false,
				'type'        => 'text',
				'data_type'   => 'price',
				'class'       => 'short variation_input',
			)
		);

		echo '</div>';
	}

	/**
	 * Save the List Price for a single variation.
	 *
	 * @since 1.0.0
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $index        Loop index.
	 */
	public function save_variation_list_price_field( $variation_id, $index ) {
		/* Security & capability checks. */
		if ( ! isset( $_POST['woocommerce_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' )
			|| ! current_user_can( 'edit_products' )
		) {
			return;
		}

		if ( isset( $_POST['variable_list_price'][ $index ] ) ) {
			$list_price = wc_clean( wp_unslash( $_POST['variable_list_price'][ $index ] ) );

			if ( '' !== $list_price ) {
				update_post_meta( $variation_id, '_list_price', $list_price );
			} else {
				delete_post_meta( $variation_id, '_list_price' );
			}
		}
	}

	/**
	 * Save List Price for all variations on product save.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The parent product ID.
	 */
	public function save_all_variation_list_price_fields( $post_id ) {
		// Security & capability checks.
		if ( ! isset( $_POST['woocommerce_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' )
			|| ! current_user_can( 'edit_products' )
		) {
			return;
		}

		if ( ! empty( $_POST['variable_list_price'] ) && is_array( $_POST['variable_list_price'] ) ) {
			foreach ( $_POST['variable_list_price'] as $index => $value ) {
				if ( empty( $_POST['variable_post_id'][ $index ] ) ) {
					continue;
				}

				$variation_id = absint( $_POST['variable_post_id'][ $index ] );
				$list_price   = wc_clean( wp_unslash( $value ) );

				if ( '' !== $list_price ) {
					update_post_meta( $variation_id, '_list_price', $list_price );
				} else {
					delete_post_meta( $variation_id, '_list_price' );
				}
			}
		} else {
			// If no variable_list_price data, delete all list prices for this product.
			$variations = $this->get_product_variations( $post_id );
			foreach ( $variations as $variation ) {
				delete_post_meta( $variation->ID, '_list_price' );
			}
		}
	}

	/**
	 * Display the List Price on the front end for simple products.
	 *
	 * @since 1.0.0
	 */
	// public function display_list_price_frontend() {
	// global $product;

	// if ( ! $product instanceof WC_Product || $product->is_type( 'variable' ) ) {
	// return;
	// }

	// $list_price = get_post_meta( $product->get_id(), '_list_price', true );
	// if ( ! $list_price ) {
	// return;
	// }

	// ← Dynamic label again
	// $label = get_option( 'fm_msrp_label', __( 'List Price', 'fm-msrp' ) );

	// echo '<p class="fm-msrp" style="font-size:1rem;color:#000000;margin-bottom:0;">';
	// echo esc_html( $label . ': ' );
	// echo wc_price( $list_price );
	// echo '</p>';


	// }

	public function display_list_price_frontend() {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( $product->is_type( 'variable' ) ) {
			return;
		}

		$list_price = get_post_meta( $product->get_id(), '_list_price', true );

		if ( $list_price ) {
			$label = get_option( 'fm_msrp_label', 'List Price' );

			echo '<p class="fm-msrp">';
			echo esc_html( $label . ': ' );
			echo wc_price( $list_price );
			echo '</p>';
		}
		if ( $product->is_type( 'variable' ) ) {
			$default_attributes   = $product->get_default_attributes();
			$available_variations = $product->get_available_variations();
			$variation_id         = null;

			foreach ( $available_variations as $variation ) {
				$match = true;

				foreach ( $default_attributes as $key => $value ) {
					if ( $variation['attributes'][ 'attribute_' . $key ] !== $value ) {
						$match = false;
						break;
					}
				}

				if ( $match ) {
					$variation_id = $variation['variation_id'];
					break;
				}
			}

			if ( $variation_id ) {
				$list_price = get_post_meta( $variation_id, '_list_price', true );

				if ( $list_price ) {
					$label = get_option( 'fm_msrp_label', 'List Price' );

					echo '<p class="fm-msrp">';
					echo esc_html( $label . ': ' );
					echo wc_price( $list_price );
					echo '</p>';
				}
			}
		}
	}





	/**
	 * Add List Price to variation data for frontend display.
	 *
	 * @param array                $data      The variation data array to be filtered.
	 * @param WC_Product           $product   The parent product object.
	 * @param WC_Product_Variation $variation The variation product object.
	 * @return array The filtered variation data array.
	 */
	public function add_list_price_to_variation_data( $data, $product, $variation ) {
		$list_price = get_post_meta( $variation->get_id(), '_list_price', true );
		if ( $list_price ) {
			$data['fm_msrp'] = $list_price;
		}
		return $data;
	}

	/**
	 * Enqueue the frontend script for displaying List Price.
	 *
	 * @return void
	 */
	public function enqueue_frontend_script() {
		if ( is_singular( 'product' ) ) {
			wp_enqueue_script(
				'fm-msrp-js',
				plugin_dir_url( __DIR__ ) . 'public/js/fm-msrp-public.js',
				array( 'jquery' ),
				FM_MSRP_VERSION,
				true
			);

			// Pull your saved label (defaulting to “List Price” if empty)
			$label = get_option( 'fm_msrp_label', __( 'List Price', 'fm-msrp' ) );

			// Localize it so your JS can read it
			wp_localize_script(
				'fm-msrp-js',
				'fmMsrpParams',
				array(
					'label'           => $label,
					'currency_symbol' => get_woocommerce_currency_symbol(),
				)
			);
		}
	}


	/**
	 * Helper to get all variation posts for a variable product.
	 *
	 * @param int $product_id The ID of the variable product.
	 * @return WP_Post[]
	 */
	private function get_product_variations( $product_id ) {
		return get_children(
			array(
				'post_parent' => $product_id,
				'post_type'   => 'product_variation',
				'post_status' => array( 'publish', 'private' ),
				'numberposts' => -1,
			)
		);
	}

	/**
	 * Load the admin class for managing the plugin's admin interface.
	 *
	 * @return void
	 */
	/**
	 * Load the admin class for managing the plugin's admin interface.
	 *
	 * @return void
	 */
	private function load_admin() {
		// 1) Admin UI
		require_once dirname( __DIR__ ) . '/admin/class-fm-msrp-admin.php';

		// 2) REST API controller
		require_once dirname( __DIR__ ) . '/includes/class-fm-msrp-rest-controller.php';

		// 3) Instantiate admin and hook its methods
		$admin = new \FormerModel\MSRP\Fm_Msrp_Admin( 'fm-msrp', FM_MSRP_VERSION );

		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $admin, 'register_admin_menu' ) );
	}

	/**
	 * Output custom CSS scoped to .fm-msrp.
	 *
	 * @since 1.0.0
	 */
	public function output_custom_css() {
		if ( is_product() ) {
			$raw_css = get_option( 'fm_msrp_custom_css', '' );

			if ( ! empty( $raw_css ) ) {
				$clean_css = wp_strip_all_tags( $raw_css );

				echo '<style id="fm-msrp-custom-css">';
				echo '.fm-msrp { ' . $clean_css . ' }';
				echo '</style>';
			}
		}
	}

	public function output_msrp_above_price() {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		// Simple product only
		if ( ! $product->is_type( 'variable' ) ) {
			$msrp = get_post_meta( $product->get_id(), '_list_price', true );
			if ( ! $msrp ) {
				return;
			}

			$label     = get_option( 'fm_msrp_label', __( 'List Price', 'fm-msrp' ) );
			$formatted = wc_price( $msrp );

			echo '<p class="fm-msrp">' . esc_html( $label ) . ': ' . $formatted . '</p>';
		}
	}
}
