<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_Config_Update
 */
class Iwd_Connector_Config_Update extends Iwd_Connector_Api {


	/**
	 * Register route for update config paypal buttons
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'update-config',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'update_config' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Save new config
	 *
	 * @param WP_REST_Request $request
	 * @return string|WP_Error
	 */
	public function update_config( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		foreach ( $api->data->paypal as $key => $option ) {
			add_option( $key );
			update_option( $key, $option );
		}

        if($api->data->offline_payments){
            foreach ($api->data->offline_payments as $payment_type => $payment ){
                add_option( 'iwd_gateway_'.$payment_type );
                update_option( 'iwd_gateway_'.$payment_type, json_encode($payment) );
            }
        }


		return 'Success!';
	}


}
