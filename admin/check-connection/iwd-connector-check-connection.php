<?php

/**
 * Class Iwd_Connector_Check_Connection
 */
class Iwd_Connector_Check_Connection {


	const CHECK_CONNECTION = 'checkout/check-connection';

	/**
	 * Iwd_Connector_Check_Connection constructor.
	 */
	public function __construct() {
		$this->registerRoute();
	}

	/**
	 * Register route for check api credentials
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'check-connection',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'check_connection' ),
                        'permission_callback' => '__return_true',
					)
				);
			}
		);
	}

	/**
	 * Check connection
	 *
	 * @param WP_REST_Request $request
	 * @return array|string[]
	 */
	public function check_connection( WP_REST_Request $request ) {
		$api      = json_decode( $request->get_body() );
		$site_url = preg_replace( '#^https?://#', '', rtrim( get_site_url(), '/' ) );

		$raw_response = wp_remote_get(
			IWD_CONNECTOR()->helper->getAppUrl() . self::CHECK_CONNECTION .
			'?api_key=' . $api->api_key .
			'&api_secret=' . $api->api_secret .
			'&platform=Woo&website_url=' . $site_url
		);

		return $this->parseResponse( $raw_response['body'] );
	}

	/**
	 * Return errors
	 *
	 * @param $response
	 * @return mixed|string|void
	 */
	public function getErrorMessage( $response ) {
		if ( isset( $response['ErrorCode'] ) ) {
			switch ( $response['ErrorCode'] ) {
				case 'wrong_api_credentials':
					return __( 'Wrong Integration API Credentials' );
				case 'wrong_website_url':
					return __( 'Wrong Integration Website URL' );
				case 'wrong_platform':
					return __( 'Wrong Integration Platform Type' );
				case 'api_key_empty':
					return __( 'Empty API Key Field' );
				case 'connect_error':
					return __( 'Connection error' );
			}
		}

		return isset( $response['ErrorMessage'] ) ? $response['ErrorMessage'] : 'API Error!';
	}

	/**
	 * Return help text
	 *
	 * @param $response
	 * @return string|void
	 */
	public function getHelpText( $response ) {
		if ( isset( $response['ErrorCode'] ) ) {
			$platform         = 'WooCommerce';
			$iwdSiteUrl       = '<a href="https://www.dominate.co/" target="_blank">Dominate Site</a>';
			$checkoutAdminUrl = '<a href="https://www.dominate.co/account" target="_blank">Dominate Account > Stores</a>';

			switch ( $response['ErrorCode'] ) {
				case 'wrong_api_credentials':
					return __( 'We were unable to locate an Integration with your Api Key & Secret. Please enter valid API Key & Secret from your ' . $checkoutAdminUrl . ' on our ' . $iwdSiteUrl . '.' );
				case 'wrong_website_url':
					return __( 'Your current Store URL differs from the Website URL saved for your Integration. Please go to your ' . $checkoutAdminUrl . ' on our ' . $iwdSiteUrl . ' and change Website URL value for your Integration' );
				case 'wrong_platform':
					return __( 'Your current Platform Type differs from the Platform saved for your Integration. Please go to your ' . $checkoutAdminUrl . ' on our ' . $iwdSiteUrl . ', change Platform to ' . $platform . ' and Save.' );
				case 'api_key_empty':
					return __( 'Please enter the Integration API Key. You can find it after purchasing Dominate Checkout SaaS in ' . $checkoutAdminUrl . ' on our ' . $iwdSiteUrl );
				case 'connect_error':
					return __( 'Could not connect to server API. Please contact our ' . $iwdSiteUrl . ' support' );
			}
		}

		return '';
	}

	/**
	 * Parse response
	 *
	 * @param $response
	 * @return array|string[]
	 */
	private function parseResponse( $response ) {
		if ( empty( $response ) ) {
			return array( 'Error' => 'connect_error' );
		}
		$data = null;
		$data = json_decode( $response, true );
		if ( true === $data['Error'] ) {
			return array(
				'error'     => true,
				'massage'   => $this->getErrorMessage( $data ),
				'help_text' => $this->getHelpText( $data ),
			);
		}

		return array(
			'error'   => false,
			'massage' => __( 'Checkout is Successfully Connected!' ),
		);
	}


}
