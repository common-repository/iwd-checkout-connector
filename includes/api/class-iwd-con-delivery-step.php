<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_DeliveryStep
 */
class Iwd_Connector_DeliveryStep extends Iwd_Connector_Api {


	/**
	 * Register route for delivery step
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'delivery-step',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'get_delivery_data' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Get data for delivery step
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error
	 * @throws Exception
	 */
	public function get_delivery_data( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$this->init();

		if ( ! empty( (array) $api->data ) ) {
			$this->setSessionData( $api->quote_id );
			$session_data = $this->getSessionData( $api->quote_id );
			$customer     = unserialize( $session_data['customer'] );
			IWD_CONNECTOR()->customer->saveAddresses( $api->data, $customer['id'] );
			$updated_session = $this->updateSessionData( $api->quote_id );
		} else {
			$updated_session = $this->getSessionData( $api->quote_id );
		}

		$data['addresses']              = IWD_CONNECTOR()->customer->getAddresses( $updated_session );
		$data['cart']                   = IWD_CONNECTOR()->cart->getCart( $updated_session );
		$data['cart_items']             = IWD_CONNECTOR()->cart->getCartItems( $updated_session );
		$data['delivery_methods']       = IWD_CONNECTOR()->shipping->getShippingMethods( $updated_session );
		$data['chosen_delivery_method'] = IWD_CONNECTOR()->shipping->getSelectedShipping( $updated_session );

		return $data;
	}

}



