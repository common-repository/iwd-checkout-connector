<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_OPC
 */
class  Iwd_Connector_OPC extends Iwd_Connector_Api {


	/**
	 * Register route for one page
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'opc',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'get_opc_data' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Get data for one page
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error
	 */
	public function get_opc_data( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$this->init();
		$session_data = $this->getSessionData( $api->quote_id );

		if ( ! IWD_CONNECTOR()->cart->getIsVirtual( $session_data ) ) {
			  $data['delivery_methods']       = IWD_CONNECTOR()->shipping->getShippingMethods( $session_data );
			  $data['chosen_delivery_method'] = IWD_CONNECTOR()->shipping->getSelectedShipping( $session_data );
		}

		$data['addresses']           = IWD_CONNECTOR()->customer->getAddresses( $session_data );
		$data['cart_items']          = IWD_CONNECTOR()->cart->getCartItems( $session_data );
		$data['cart']                = IWD_CONNECTOR()->cart->getCart( $session_data );
		$data['available_countries'] = IWD_CONNECTOR()->country->prepareCountries();
		$data['available_regions']   = IWD_CONNECTOR()->country->prepareStates();

		return $data;
	}

}

