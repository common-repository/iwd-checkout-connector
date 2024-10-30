<?php

/**
 * Class Iwd_Connector_Payment_Action
 */
class Iwd_Connector_Payment_Action {


	/**
	 * Iwd_Connector_Payment_Action constructor.
	 */
	public function __construct() {
		$this->registerRoute();
	}

	/**
	 * Register routes for void and capture actions
	 */
	private function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-order-action',
					'capture',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'capture' ),
                        'permission_callback' => '__return_true',
					)
				);
			}
		);

		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-order-action',
					'void',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'void' ),
                        'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Return capture url
	 *
	 * @return string
	 */
	public function getCaptureUrl() {
		return get_rest_url() . 'iwd-order-action/capture';
	}

	/**
	 * Return void url
	 *
	 * @return string
	 */
	public function getVoidUrl() {
		return get_rest_url() . 'iwd-order-action/void';
	}

	/**
	 * Capture
	 *
	 * @param $request
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function capture( $request ) {
		$response = null;
		$order_id = $request->get_param( 'order_id' );
		$order    = wc_get_order( $order_id );
		$gateway  = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
		$result   = $gateway->process_capture( $order );

		if ( is_wp_error( $result ) ) {
			$result->add_data( array( 'status' => 200 ) );
		}

		$response = rest_ensure_response( $result );
		return $response;
	}

	/**
	 * Void
	 *
	 * @param $request
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function void( $request ) {
		$response = null;
		$order_id = $request->get_param( 'order_id' );

		$order   = wc_get_order( $order_id );
		$gateway = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
		$result  = $gateway->process_void( $order );

		if ( is_wp_error( $result ) ) {
			$result->add_data( array( 'status' => 200 ) );
		}

		$response = rest_ensure_response( $result );
		return $response;
	}


}
