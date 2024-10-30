<?php

/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_multiple_pay_add_to_gateways( $gateways ) {
    $gateways[] = 'Iwd_Connector_Multiple_Gateway';
    return $gateways;
}

if( $this->is_plugin_enabled ){
    add_filter( 'woocommerce_payment_gateways', 'iwd_multiple_pay_add_to_gateways' );
}


class Iwd_Connector_Multiple_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        global $woocommerce, $post;
        $order = wc_get_order( $post->ID ?? '' );

        $title = !empty($order) ? $order->get_payment_method_title() : 'Multiple Offline Payment Method';
        $this->id                 = 'iwd_gateway_multiple_offline';
        $this->icon               = apply_filters( 'iwd_gateway_multiple_offline', '' );
        $this->has_fields         = false;
        $this->method_title       = __( $title, 'iwd_gateway_multiple_offline' );
        $this->method_description = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_multiple_offline' );
        $this->title              = $title;
        $this->description        = $title;
        $this->supports           = array( 'products' );
        $this->enabled            = 'yes';
    }

}
