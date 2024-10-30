<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_Product_Search
 */
class  Iwd_Connector_Subscription extends Iwd_Connector_Api
{

    /**
     * Register route
     *
     * @return mixed|void
     */
    public function registerRoute()
    {
        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'iwd-checkout',
                    'subscription-notify',
                    array(
                        'methods' => 'POST',
                        'callback' => array($this, 'notify'),
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
                    'subscription-notify-order',
                    array(
                        'methods' => 'POST',
                        'callback' => array($this, 'notifyOrder'),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );
    }

    /**
     * @param WP_REST_Request $request
     */
    public function notify(WP_REST_Request $request)
    {
        $data = json_decode($request->get_body());

        if (!empty($data)) {
            global $wpdb;

            $result = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}iwd_subscription WHERE sku = '%s'", (string)$data->sku ) );
            if(empty($result)){
                $wpdb->insert(
                    $wpdb->prefix . 'iwd_subscription',
                    array(
                        'plan_id' => $data->plan_id,
                        'product_id' => $data->product_id,
                        'sku' => (string)$data->sku,
                        'merchant_id' => $data->merchant_id,
                        'checkout_instance_id' => $data->checkout_instance_id,
                        'environment_id' => $data->environment_id,
                        'env' => $data->env,
                        'client_id' => $data->client_id,
                        'quantity_supported' => $data->quantity_supported,
                        'active' => $data->active,
                    )
                );
            }else{
                $wpdb->update(
                    $wpdb->prefix . 'iwd_subscription',
                    array(
                        'plan_id' => $data->plan_id,
                        'product_id' => $data->product_id,
                        'merchant_id' => $data->merchant_id,
                        'checkout_instance_id' => $data->checkout_instance_id,
                        'environment_id' => $data->environment_id,
                        'env' => $data->env,
                        'client_id' => $data->client_id,
                        'quantity_supported' => $data->quantity_supported,
                        'active' => $data->active
                    ),
                    array(  'sku' => (string)$data->sku)
                );
            }


            return ['notify' => 1];
        }


        return 'error';
    }

    /**
     * @param WP_REST_Request $request
     */
    public function notifyOrder(WP_REST_Request $request)
    {
        $data =  json_decode($request->get_body());
        if(!empty($data)){

            $order = wc_create_order();
            $order->set_billing_email( $data->subscriber->email_address);
            $order->set_billing_first_name( $data->subscriber->name->given_name);
            $order->set_billing_last_name( $data->subscriber->name->surname );
            $order->set_billing_address_1($data->subscriber->shipping_address->address->address_line_1);
            $order->set_billing_country( $data->subscriber->shipping_address->address->country_code );
            $order->set_billing_state( $data->subscriber->shipping_address->address->admin_area_1 );
            $order->set_billing_city( $data->subscriber->shipping_address->address->admin_area_2);
            $order->set_billing_postcode( $data->subscriber->shipping_address->address->postal_code );

            $id = wc_get_product_id_by_sku( $data->sku );
            $product  = wc_get_product( $id );
            $product->set_price($data->grand_total);
            $order->add_product($product, 1 );
            $order->set_shipping_first_name( $data->subscriber->name->given_name);
            $order->set_shipping_last_name( $data->subscriber->name->surname );
            $order->set_shipping_address_1($data->subscriber->shipping_address->address->address_line_1);
            $order->set_shipping_country( $data->subscriber->shipping_address->address->country_code );
            $order->set_shipping_state( $data->subscriber->shipping_address->address->admin_area_1 );
            $order->set_shipping_city( $data->subscriber->shipping_address->address->admin_area_2);
            $order->set_shipping_postcode( $data->subscriber->shipping_address->address->postal_code );

            $order->add_order_note( sprintf( __( 'Transaction ID: "%1$s"' ),  $data->transaction_id  ? $data->transaction_id : '') );
            $order->add_order_note( sprintf( __( 'Payer ID: "%1$s"' ),  $data->subscriber->payer_id) );

            $order->set_payment_method( 'iwd_gateway_pay' );
            $order->payment_complete( $data->transaction_id );

            $order->set_total( $data->grand_total );
            $order->set_currency( $data->currency );

            $order->payment_complete();
            $order->save();

            return array('id' => $order->get_id(), 'status' => $order->get_status());
        }

        return 'error';
    }

}

