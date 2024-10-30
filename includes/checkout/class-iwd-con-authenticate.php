<?php

/**
 * Class Authenticate
 */
class Iwd_Connector_Authenticate {


	/**
	 * Authenticate constructor.
	 */
	public function __construct() {
		$this->registerRoute();
	}

	/**
	 * Register route for ajax login
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'ajax-login',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'authenticate' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Authenticate
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function authenticate( WP_REST_Request $request ) {
		$api                    = json_decode( $request->get_body() );
		$creds['user_login']    = $api->username;
		$creds['user_password'] = $api->password;
		$creds['remember']      = true;

		$user = wp_signon( $creds, false );

		if ( is_wp_error( $user ) ) {
			$error   = true;
			$message = __( 'Invalid login or password!' );
		} else {
			$error   = false;
			$message = __( 'Success' );
		}

		return array(
			'errors'  => $error,
			'message' => $message,
		);
	}

}

