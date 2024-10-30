<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';
require_once IWD_CONNECTOR_PATH . 'includes/model/emails/order-failed-email.php';

add_filter( 'woocommerce_order_number', 'wc_set_order_number', 1, 2);

function wc_set_order_number( $order_id, $order ) {

    $order_number = $order->get_meta('_order_number');

    return empty($order_number) ? $order_id : $order_number;
}

/**
 * Class Iwd_Connector_Place_Order
 */
class Iwd_Connector_Place_Order extends Iwd_Connector_Api {


	const TRANSACTION_STATUS = '_iwd_transaction_status';

	/**
	 * @var array
	 */
	private $customer_data;

	/**
	 * Register route for place order
	 *
	 * @return mixed|void
	 */
	public function registerRoute() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'iwd-checkout',
					'order-create',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'order_create' ),
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
                    'offline-order-create',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'order_create' ),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );

	}

	/**
	 * Order create function
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return array|WP_Error
	 * @throws WC_Data_Exception|Exception
	 */
	public function order_create( WP_REST_Request $request ) {
        global $wpdb;

		$api = json_decode( $request->get_body() );

		if ( empty( $api->quote_id ) || $this->checkAccess( $api->access_tokens ) ) {
			return new WP_Error( 'required_parameter_missing', __( 'Required parameter is missing' ), array( 'status' => 500 ) );
		}

		$this->init();
		$session_data = $this->getSessionData( $api->quote_id );
        $this->customer_data = unserialize( $session_data['customer'] );

		$this->processDominateData($api->data->custom_data->dominate);

        try {
            $result = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT order_id FROM {$wpdb->prefix}iwd_orders WHERE session_id = '%s'", (string)$api->quote_id));

            $order = new WC_Order();
            $order->update_meta_data('_order_number', $result[0]->order_id);

            foreach (unserialize($session_data['cart']) as $item) {
                $args = [];
                $product = wc_get_product($item['product_id']);

                if (!empty($item['variation_id']) && !empty($item['variation'])) {
                    $product = new WC_Product_Variation($item['variation_id']);
                    $args['variation'] = [];
                    foreach ($item['variation'] as $key => $value) {
                        $args['variation'][str_replace('attribute_', '', $key)] = $value;
                    }
                }

                $order->add_product($product, $item['quantity'], $args);
            }
            $order->set_customer_id($this->customer_data['id']);

            // set billing addresses
            $order->set_billing_email($this->customer_data['email']);
            $order->set_billing_first_name($this->customer_data['first_name']);
            $order->set_billing_last_name($this->customer_data['last_name']);
            $order->set_billing_address_1($this->customer_data['address']);
            $order->set_billing_country($this->customer_data['country']);
            $order->set_billing_state($this->customer_data['state']);
            $order->set_billing_city($this->customer_data['city']);
            $order->set_billing_postcode($this->customer_data['postcode']);
            $order->set_billing_phone($this->customer_data['phone']);

            // set shipping addresses
            $order->set_shipping_first_name($this->customer_data['shipping_first_name']);
            $order->set_shipping_last_name($this->customer_data['shipping_last_name']);
            $order->set_shipping_address_1($this->customer_data['shipping_address_1']);
            $order->set_shipping_country($this->customer_data['shipping_country']);
            $order->set_shipping_state($this->customer_data['shipping_state']);
            $order->set_shipping_city($this->customer_data['shipping_city']);
            $order->set_shipping_postcode($this->customer_data['shipping_postcode']);
            $order->set_shipping_phone($this->customer_data['shipping_phone']);

            // apply coupons
            $order->apply_coupon(unserialize($session_data['applied_coupons']) ? maybe_unserialize($session_data['applied_coupons'])[0] : '');

            // set shipping methods
            $shipping_methods = unserialize($session_data['shipping_for_package_0']);

            foreach ($shipping_methods['rates'] as $shipping_rate) {
                if ($shipping_rate->get_id() == unserialize($session_data['chosen_shipping_methods'])[0]) {
                    $order->add_shipping($shipping_rate);
                }
            }

            $order->set_customer_id($this->customer_data['id']);
            $order->set_currency(get_woocommerce_currency());
            $order->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));

	        // set payment method
			$methodCode = $this->parseMethodCode($api->data->payment_method_code);
			$methodTitle = $api->data->payment_method_title;
			$paymentAction = $api->data->payment_action;
			$offlinePayments = [
				'zero', 'purchaseorder', 'custom', 'check_or_money_order', 'cash_on_delivery', 'banktransfer',
				'multiple_offline',
			];

			$order->add_meta_data( self::TRANSACTION_STATUS, $paymentAction, true );
			$order->set_payment_method_title($methodTitle);
			$order->calculate_totals();
	        $this->addAdditionalFields($api->data->custom_data->dominate, $order->get_id() );

	        if ($api->data->comments) {
	            foreach ($api->data->comments as $comments) {
	                $order->add_order_note(sprintf(__($comments)));
	            }
	        }

            $order->save();

            $calculate_tax_args = array(
                'country'  => $this->customer_data['shipping_country'],
                'state'    => $this->customer_data['shipping_state'],
                'postcode' =>  $this->customer_data['postcode'],
                'city'     => $this->customer_data['city'],
            );

            // Save order items first.
            if (function_exists('wc_save_order_items'))
                wc_save_order_items( $order->get_id(), $order->get_items() );

            // Grab the order and recalculate taxes.
            $order = wc_get_order( $order->get_id() );
            $order->calculate_taxes( $calculate_tax_args );
            $order->calculate_totals( false );

            if (in_array($methodCode, $offlinePayments)) {
                $order->set_payment_method('iwd_gateway_' . $methodCode);

                if ($api->data->payment_method_code === 'purchaseorder') {
                    $order->add_order_note(sprintf(__('Purchase Order Number: "%1$s"'), $api->data->po_number));
                }

                if (isset($api->data->multiple_field)) {
                    $order->add_order_note(sprintf(__(IWD_Payments_Helper::getTitle('iwd_gateway_' . $api->data->payment_method_code,
                            'field_name') . ': "%1$s"'), $api->data->multiple_field));
                }

                $order->payment_complete();
            } else {
                $order->set_payment_method('iwd_gateway_pay');

                if ('authorize' === $paymentAction) {
                    $txnId = $api->data->transactions->authorization->id;

                    $order->set_transaction_id($txnId);

                    /* translators: %s: search term */
                    $order->add_order_note(sprintf(__('Order authorized in "%1$s": Transaction ID: "%2$s"'), $methodTitle, $txnId));
                } elseif ('capture' === $paymentAction) {
                    $txnId = $api->data->transactions->capture->id;

                    /* translators: %s: search term */
                    $order->add_order_note(sprintf(__('Order captured in "%1$s": Transaction ID: "%2$s"'), $methodTitle, $txnId));
                    $order->payment_complete($txnId);

                    /* send invoice email on capture */
                    WC()->mailer()->emails['WC_Email_Customer_Invoice']->trigger($order->get_id(), $order);
                }
            }

            $order->save();

            WC()->session->delete_session($api->quote_id);

            $wpdb->delete($wpdb->prefix . 'iwd_orders', array('session_id' => (string)$api->quote_id));

            return array(
                'order_id' => $result[0]->order_id ?? $order->get_id(),
                'order_increment_id' => $result[0]->order_id ?? $order->get_id(),
                'order_status' => $order->get_status(),
                'quote_id' => $api->quote_id,
                'success_page' => apply_filters('woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order),
            );
        } catch (\Throwable $t) {
            $email = new IWD_Fail_Order_Email();
            $email->trigger($session_data, $api->data);
        }
    }

    /**
     * @param $data
     * @param $orderId
     * @return void
     */
    private function addAdditionalFields($data, $orderId)
    {
        if($data->additional){
            foreach ($data->additional as $placement => $data)
            {
                update_post_meta($orderId, $placement, json_encode($data) );
            }
        }
    }

    /**
     * @param $data
     * @param $customer_data
     * @return void
     */
    private function processDominateData($data)
    {
        foreach ( $data as $key => $value) {
            if ($key === 'subscribe_to_newsletter') {
                $this->subcribeToNewsletter();
            } elseif ($key === 'create_customer_account' && !$this->customer_data['id']) {
	            $customer = new Iwd_Connector_Customer();

	            $customer->setData($this->customer_data);
	            $customer->assignCustomerToOrder();

	            $this->customer_data['id'] = $customer->getId();
            }
        }
    }

    /**
     * @return void
     */
    private function subcribeToNewsletter()
    {
        global $wpdb;

        $sql = "INSERT INTO {$wpdb->prefix}dominate_newsletter (email, firstname, lastname, date) VALUES (%s,%s,%s,%s)
                ON DUPLICATE KEY UPDATE firstname = VALUES(`firstname`),lastname = VALUES(`lastname`), date=VALUES(`date`)";

        $sql = $wpdb->prepare(
            $sql,
            $this->customer_data['email'],
            $this->customer_data['first_name'],
            $this->customer_data['last_name'],
            current_time('mysql')
        );

        $wpdb->query($sql);
    }

	/**
	 * @param $code
	 *
	 * @return string
	 */
    public function parseMethodCode($code){
        if (strpos($code, 'multiple_offline') !== false) {
            return 'multiple_offline';
        }

        return $code;
    }
}
