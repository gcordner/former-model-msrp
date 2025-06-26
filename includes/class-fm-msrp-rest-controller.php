<?php
/**
 * REST API controller for FM MSRP plugin settings.
 *
 * @package    Fm_Msrp
 * @subpackage Fm_Msrp/includes
 * @since      1.0.0
 */

namespace FormerModel\MSRP;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_Error;

/**
 * Class Fm_Msrp_REST_Controller
 *
 * Handles GET/POST of plugin settings via the REST API.
 *
 * @since 1.0.0
 */
class Fm_Msrp_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = 'fm-msrp/v1';
		$this->rest_base = 'settings';
	}

	/**
	 * Register routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'label'      => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'custom_css' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'wp_kses_post',
						),
					),
				),
			)
		);
	}

	/**
	 * Permission check.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return true|WP_Error True if the request has access, WP_Error otherwise.
	 */
	public function permissions_check( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return new WP_Error(
			'rest_forbidden',
			__( 'You do not have permission to access these settings.', 'fm-msrp' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Get plugin settings.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return array Settings response.
	 */
	public function get_settings( $request ) {
		$label = get_option( 'fm_msrp_label', 'List Price' );
		$css   = get_option( 'fm_msrp_custom_css', '' );

		return rest_ensure_response(
			array(
				'label'      => $label,
				'custom_css' => $css,
			)
		);
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return array Updated settings response.
	 */
	public function update_settings( $request ) {
		$label = $request->get_param( 'label' );
		$css   = $request->get_param( 'custom_css' );

		if ( isset( $label ) ) {
			update_option( 'fm_msrp_label', $label );
		}

		if ( isset( $css ) ) {
			update_option( 'fm_msrp_custom_css', $css );
		}

		return rest_ensure_response(
			array(
				'label'      => $label,
				'custom_css' => $css,
			)
		);
	}
}
