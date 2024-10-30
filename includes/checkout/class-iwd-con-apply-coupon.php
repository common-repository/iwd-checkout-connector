<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_ApplyCoupon
 */
class Iwd_Connector_ApplyCoupon extends Iwd_Connector_Api {


	/**
	 * Register route for apply coupon
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'apply-coupon',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'apply_coupon' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Apply coupon
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error
	 */
	public function apply_coupon( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$this->init();
		$this->setSessionData( $api->quote_id );

		$status = true;
		if ( '' == $api->data->coupon_code ) {
			WC()->cart->remove_coupon( WC()->cart->get_applied_coupons()[0] );
		} else {
			$status = WC()->cart->apply_coupon( $api->data->coupon_code );
		}

		$updated_session = $this->updateSessionData( $api->quote_id );

		$data['cart']       = IWD_CONNECTOR()->cart->getCart( $updated_session );
		$data['cart_items'] = IWD_CONNECTOR()->cart->getCartItems( $updated_session );

		if ( ! $status ) {
			$data['error'] = __( 'The coupon code "' . $api->data->coupon_code . '" is not valid.' );
		}

		return $data;
	}
}
