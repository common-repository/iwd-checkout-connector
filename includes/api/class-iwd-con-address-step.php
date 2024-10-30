<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_AddressStep
 */
class  Iwd_Connector_AddressStep extends Iwd_Connector_Api {


	/**
	 * Register route for address step
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'address-step',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'get_address_data' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Get data for address step
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	public function get_address_data( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$new_session_obj = new WC_Session_Handler();
		$session_data    = $new_session_obj->get_session( $api->quote_id );

		$data['cart']                = IWD_CONNECTOR()->cart->getCart( $session_data );
		$data['cart_items']          = IWD_CONNECTOR()->cart->getCartItems( $session_data );
		$data['addresses']           = IWD_CONNECTOR()->customer->getAddresses( $session_data );
		$data['available_countries'] = IWD_CONNECTOR()->country->prepareCountries();
		$data['available_regions']   = IWD_CONNECTOR()->country->prepareStates();

		return $data;
	}

}

