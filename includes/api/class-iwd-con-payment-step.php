<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_PaymentStep
 */
class Iwd_Connector_PaymentStep extends Iwd_Connector_Api {


	/**
	 * Register route for payment step
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'payment-step',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'get_payment_data' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Return data for payment step
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error
	 */
	public function get_payment_data( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$this->init();
		$this->setSessionData( $api->quote_id );

		WC()->session->set( 'chosen_shipping_methods', array( $api->data->shipping_method ) );
		$updated_session = $this->updateSessionData( $api->quote_id );

		$data['addresses']              = IWD_CONNECTOR()->customer->getAddresses( $updated_session );
		$data['cart']                   = IWD_CONNECTOR()->cart->getCart( $updated_session );
		$data['cart_items']             = IWD_CONNECTOR()->cart->getCartItems( $updated_session );
		$data['chosen_delivery_method'] = IWD_CONNECTOR()->shipping->getSelectedShipping( $updated_session );

		return $data;
	}
}
