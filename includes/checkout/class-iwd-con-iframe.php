<?php
if ( ! class_exists( 'Iwd_Connector_Iframe' ) ) {

	/**
	 * Class Iframe
	 */
	class Iwd_Connector_Iframe {

		/**
		 * Params for iframe
		 *
		 * @return string
		 */
		public function getFrameUrl() {
			$session_data = WC()->session->get_session_data();
			$addresses    = IWD_CONNECTOR()->customer->getAddresses( $session_data );

			$params = [
				'lazy'             => true,
				'api_key'          => IWD_CONNECTOR()->helper->getApiKey(),
				'quote_id'         => IWD_CONNECTOR()->helper->getCustomerId(),
				'cart'             => IWD_CONNECTOR()->cart->getCart( $session_data ),
				'cart_items'       => count( IWD_CONNECTOR()->cart->getCartItems( $session_data ) ),
				'shipping_methods' => count( IWD_CONNECTOR()->shipping->getShippingMethods( $session_data ) ),
				'address'          => [
					'saved'             => false,
					'shipping'          => (bool) $addresses['shipping']['address'],
					'billing'           => (bool) $addresses['billing']['address'],
					'ship_bill_to_diff' => (bool) $addresses['ship_bill_to_different_address'] ?? false,
				],
			];

			if ( isset( $_GET['paypal_order_id'] ) && ! empty( $_GET['paypal_order_id'] ) ) {
				$params['paypal_order_id'] = sanitize_text_field( $_GET['paypal_order_id'] );
			}

            if ( isset( $_GET['paypal_funding_source'] ) && ! empty( $_GET['paypal_funding_source'] ) ) {
                $params['paypal_funding_source'] = sanitize_text_field( $_GET['paypal_funding_source'] );
            }

			if ( is_user_logged_in() ) {
				$params['customer_token'] = wp_get_current_user()->ID;
				$params['customer_email'] = wp_get_current_user()->user_email;
			}

			return IWD_CONNECTOR()->helper->getFrameUrl() . '?' . http_build_query( $params );
		}

	}

}
