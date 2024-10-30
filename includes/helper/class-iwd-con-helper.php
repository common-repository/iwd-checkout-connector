<?php

/**
 * Class Iwd_Connector_Helper
 */
class Iwd_Connector_Helper {

	/**
	 * App url
	 */
    const APP_URL = 'https://checkout.iwdagency.com/';

	/**
	 * Get api key
	 *
	 * @return bool|mixed|void
	 */
	public function getApiKey() {
		return get_option( 'iwd_connector_integration_key' );
	}

	/**
	 * Get secret key
	 *
	 * @return bool|mixed|void
	 */
	public function getSecretKey() {
		return get_option( 'iwd_connector_secret_key' );
	}

	/**
	 * Return application url
	 */
	public function getAppUrl() {
		return self::APP_URL;
	}

	/**
	 * Return application url
	 */
	public function getFrameUrl() {
		return self::APP_URL . 'checkout/opc';
	}

	/**
	 * Get customer id
	 *
	 * @return int
	 */
	public function getCustomerId() {
		return WC()->session->get_customer_id();
	}

	/**
	 * Get login url
	 *
	 * @return string
	 */
	public function getLoginUrl() {
		return get_site_url() . '/wp-json/iwd-checkout/ajax-login';
	}

    /**
     * Get success page url
     *
     * @return string
     */
    public function getSuccessUrl() {
        return get_site_url() . '/wp-json/iwd-checkout/success-page';
    }

	/**
	 * Reset password url
	 *
	 * @return string
	 */
	public function getResetPasswordUrl() {
		return wc_lostpassword_url();
	}

	/**
	 * Get edit cart url
	 *
	 * @return string
	 */
	public function getEditCartUrl() {
		return wc_get_cart_url();
	}


	/**
	 * Get coupon url
	 *
	 * @return string
	 */
	public function getApplyCouponUrl() {
		return get_site_url() . '/wp-json/iwd-checkout/apply-coupon';
	}
}
