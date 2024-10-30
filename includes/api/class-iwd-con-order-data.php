<?php

use Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore as OrdersStatsDataStore;

require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_Order_Info
 */
class Iwd_Connector_Order_Info extends Iwd_Connector_Api {


	/**
	 * Register route for check orde data
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'order-data',
					array(
						'methods' => 'POST',
						'callback' => array($this, 'order_data'),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

	}

	/**
	 * Order data
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	public function order_data( WP_REST_Request $request) {
        global $wpdb;
		$api = json_decode($request->get_body());

		if (empty($api->quote_id) || $this->checkAccess($api->access_tokens)) {
			return new WP_Error('required_parameter_missing', __('Required parameter is missing'), array('status' => 500));
		}

        $new_session_obj = new WC_Session_Handler();
		$session_data = $new_session_obj->get_session($api->quote_id);

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT order_id FROM {$wpdb->prefix}iwd_orders WHERE session_id = '%s'", (string)$api->quote_id ) );

        $orderId = isset($result) && !empty($result[0]->order_id) ? $result[0]->order_id : null;

        if(empty($orderId)){
            $order = new WC_Order();
            $order->save();
            error_log('Insert'.$order->get_id());
            $wpdb->insert(
                $wpdb->prefix . 'iwd_orders',
                array(
                    'session_id' => $api->quote_id,
                    'order_id' => $order->get_id(),
                )
            );
            $orderId = $order->get_id();

            wp_delete_post($orderId,true);
        }

		$data['addresses'] = IWD_CONNECTOR()->customer->getAddresses($session_data);
		$data['chosen_delivery_method'] = IWD_CONNECTOR()->shipping->getSelectedShipping($session_data);
		$data['cart_items'] = IWD_CONNECTOR()->cart->getCartItems($session_data);
		$data['cart'] = IWD_CONNECTOR()->cart->getCart($session_data);
        $data['reserved_order_id'] = $orderId;

		return $data;
	}


}
