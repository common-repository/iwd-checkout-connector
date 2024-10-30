<?php

require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

/**
 * Class Iwd_Connector_Api
 */
abstract class Iwd_Connector_Api {

	/**
	 * Session
	 *
	 * @var WC_Session_Handler
	 */
	protected $session;

	/**
	 * Iwd_Connector_Api constructor.
	 */
	public function __construct() {
		$this->registerRoute();
	}

	/**
	 * Register api routes
	 *
	 * @return mixed
	 */
	abstract public function registerRoute();

	/**
	 * Init
	 */
	public function init() {
		$this->session = new WC_Session_Handler();
		WC()->session  = new Iwd_Connector_CartSession();
		WC()->session->init();
		WC()->customer = new WC_Customer( get_current_user_id(), true );
		WC()->cart     = new WC_Cart();
	}

	/**
	 * Get session data
	 *
	 * @param $quote_id
	 * @return mixed
	 */
	public function getSessionData( $quote_id ) {
		return $this->session->get_session( $quote_id );
	}

    /**
     * Save data to session
     *
     * @param $quote_id
     * @throws Exception
     */
	public function setSessionData( $quote_id ) {
		$session_data = $this->getSessionData( $quote_id );
		$customer     = maybe_unserialize( $session_data['customer'] );
		WC()->session->set( 'cart', maybe_unserialize( $session_data['cart'] ) );
		WC()->session->set( 'cart_totals', maybe_unserialize( $session_data['cart_totals'] ) );
		WC()->session->set( 'applied_coupons', maybe_unserialize( $session_data['applied_coupons'] ) );
		WC()->session->set( 'coupon_discount_totals', maybe_unserialize( $session_data['coupon_discount_totals'] ) );
		WC()->session->set( 'coupon_discount_tax_totals', maybe_unserialize( $session_data['coupon_discount_tax_totals'] ) );
		WC()->session->set( 'removed_cart_contents', maybe_unserialize( $session_data['removed_cart_contents'] ) );

        $this->setCustomerAddress($customer);

		if ( ! empty( $session_data['cart_fees'] ) ) {
			WC()->session->set( 'cart_fees', maybe_unserialize( $session_data['cart_fees'] ) );
		}

        WC()->customer->set_calculated_shipping( true );
        WC()->customer->save();

		WC()->session->save_data();
		WC()->cart->set_session();

		WC()->cart->calculate_shipping();
		WC()->session->set( 'chosen_shipping_methods', maybe_unserialize( $session_data['chosen_shipping_methods'] ) );
	}

	/**
	 * Update session
	 *
	 * @param $quote_id
	 * @return mixed
	 */
	public function updateSessionData( $quote_id ) {
		WC()->cart->get_cart_from_session();
		WC()->cart->calculate_totals();
		WC()->session->changeData( $quote_id );

		return $this->getSessionData( $quote_id );

	}

	/**
	 * Check secret key
	 *
	 * @param $tokens
	 * @return bool|WP_Error
	 */
	public function checkAccess( $tokens ) {
		$integrationApiSecret = IWD_CONNECTOR()->helper->getSecretKey();

		if ( $tokens->secret !== $integrationApiSecret ) {
			return new WP_Error( 'wrong_secret_key', __( 'Secret key is required!' ), array( 'status' => 500 ) );
		}

		return false;
	}

    /**
     * @return void
     * @throws Exception
     *
     */
    private function setCustomerAddress($customer) {
        WC()->customer = new WC_Customer( $customer['id'], true );

        WC()->customer->set_shipping_first_name( $customer['shipping_first_name']  );
        WC()->customer->set_shipping_last_name( $customer['shipping_last_name']  );
        WC()->customer->set_shipping_address( $customer['shipping_address_1']  );
        WC()->customer->set_shipping_country( $customer['shipping_country']  );
        WC()->customer->set_shipping_state( $customer['shipping_state']  );
        WC()->customer->set_shipping_city( $customer['shipping_city']  );
        WC()->customer->set_shipping_postcode( $customer['shipping_postcode'] );
        WC()->customer->set_shipping_phone( $customer['shipping_phone']);

        WC()->customer->set_billing_first_name($customer['first_name']);
        WC()->customer->set_billing_last_name($customer['last_name']);
        WC()->customer->set_billing_address($customer['address']);
        WC()->customer->set_billing_country($customer['country']);
        WC()->customer->set_billing_state( $customer['state']  );
        WC()->customer->set_billing_city( $customer['city'] );
        WC()->customer->set_billing_postcode( $customer['postcode'] );
        WC()->customer->set_billing_phone( $customer['phone']);
        WC()->customer->set_billing_email( $customer['email']  );
    }
}
