<?php

class Iwd_Change_Order_Status {


	const INTEGRATION_SECRET = 'integration_secret';
	const INTEGRATION_KEY    = 'integration_key';
	const ORDER_STATUS       = 'order_status';
	const ORDER_ID           = 'order_id';
	const SHIPMENT_TRACKER   = 'shipment_tracker';
	const CHANGE_STATUS_URL  = 'order/change-status';

	/**
	 * Iwd_Change_Order_Status constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_changed', array( $this, 'change_status' ), 10, 3 );
	}

	/**
	 * Change status
	 *
	 * @param $id
	 * @param $previous_status
	 * @param $next_status
	 * @return bool|WP_Error
	 */
	public function change_status( $id, $previous_status, $next_status ) {
		$unused = $previous_status;
		unset( $unused );

		$body = array(
			self::ORDER_ID           => $id,
			self::ORDER_STATUS       => $next_status,
			self::INTEGRATION_KEY    => IWD_CONNECTOR()->helper->getApiKey(),
			self::INTEGRATION_SECRET => IWD_CONNECTOR()->helper->getSecretKey(),
			self::SHIPMENT_TRACKER   => $next_status === 'shipped' ? ['status' => 'SHIPPED'] : null
		);

		$raw_response = wp_safe_remote_post(
			IWD_CONNECTOR()->helper->getAppUrl() . self::CHANGE_STATUS_URL,
			$this->buildRequest( $body )
		);

		$response = json_decode( $raw_response['body'], true );

		if ( isset( $response['resultCode'] ) && 0 == $response['resultCode'] ) {
			return new WP_Error( 'error', __( $response['errorMsg'] ) );
		}

		return true;
	}

	/**
	 * Build request
	 *
	 * @param array $body
	 * @return array
	 */
	private function buildRequest( array $body ) {
		return array(
			'method'      => 'POST',
			'body'        => $body,
			'timeout'     => 70,
			'httpversion' => '1.1',
		);

	}

}
