<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_Order_Update
 */
class Iwd_Connector_Order_Update extends Iwd_Connector_Api {


	/**
	 * Register route for update order via webhook
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'order-update',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'order_update' ),
                        'permission_callback' => '__return_true',
					)
				);
			}
		);

        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'iwd-checkout',
                    'success-page',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'success_page' ),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );

	}

	/**
	 * Order update
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 * @throws WC_Data_Exception
	 */
	public function order_update( WP_REST_Request $request ) {
		$api = json_decode( $request->get_body() );

		if ( $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$orderId = $api->data->order_id;
		$order   = wc_get_order( $orderId );

		if ( $order->get_id() ) {
			// Set Transactions for order
			$paymentAction = $api->data->payment_action;
			$transaction   = $api->data->transaction;

			if ('capture' === $paymentAction) {
				$capturedTxn = $transaction->capture;

				/* translators: %s: search term */
				$order->add_order_note(sprintf(__('Captured amount of %1$s online. Transaction ID: "%2$s"'),
					$capturedTxn->amount, $capturedTxn->id));
				$order->add_meta_data(Iwd_Connector_Gateway_Pay::TRANSACTION_STATUS,
					Iwd_Connector_Gateway_Pay::TRANSACTION_CAPTURED, true);
				$order->payment_complete($capturedTxn->id);

				if (isset($capturedTxn->additional_info) && $capturedTxn->additional_info) {
					$order->add_meta_data(Iwd_Connector_Gateway_Pay::PAYMENT_INFO,
						json_encode($capturedTxn->additional_info), true);

					$additionalInfoNote = '<u><b>Payment Information</b></u><br>';

					foreach ($capturedTxn->additional_info as $title => $value) {
						$additionalInfoNote .= '<b>'.$title.'</b>: '.$value.'<br>';
					}

					$order->add_order_note($additionalInfoNote);
				}

				$order->save();

				/* send invoice email on capture */
				WC()->mailer()->emails['WC_Email_Customer_Invoice']->trigger($order->get_id(), $order);
			} elseif ('refund' === $paymentAction) {
				$refundedTxn = $transaction->refund;

				/* translators: %s: search term */
				$order->add_order_note(sprintf(__('Refunded %1$s - Refund Transaction ID: %2$s'), $refundedTxn->amount,
					$refundedTxn->id));
				$order->update_status('refunded');
			} elseif ('void' === $paymentAction) {
				$order->add_meta_data(Iwd_Connector_Gateway_Pay::TRANSACTION_STATUS,
					Iwd_Connector_Gateway_Pay::TRANSACTION_VOIDED, true);

				/* translators: %s: search term */
				$order->add_order_note(sprintf(__('Transaction %1$s has been voided in PayPal.'),
					$transaction->void->id));
				$order->update_status('cancelled');
			} elseif ('hold' === $paymentAction) {
				if ((property_exists($transaction, 'dispute'))) {
					$order->add_order_note('The Order was put on Hold because the Dispute with ID: '.$transaction->dispute->id
					                       .' has been opened. Please access your Braintree account for more information.');
				} else {
					$order->add_order_note('Order was put on Hold.');
				}

				$order->update_status('on-hold');
			}

			$order->save();

			$result = array(
				'error'        => 0,
				'order_status' => $order->get_status(),
			);
		} else {
			$result = array(
				'error'        => 1,
				'order_status' => 'not_found',
			);
		}

		return $result;
	}

    public function success_page( WP_REST_Request $request){
        global $wpdb;
        $bodyParams = $request->get_body_params();

        if(isset($bodyParams['order_increment_id']) && !empty($bodyParams['order_increment_id'])){

            $result = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_order_number' AND meta_value = '%s'", (string)$bodyParams['order_increment_id']) );

            $order   = wc_get_order( $result[0]->post_id );

            return apply_filters(
                'woocommerce_checkout_no_payment_needed_redirect',
                $order->get_checkout_order_received_url(), $order
            );
        }
    }
}
