<?php

/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_zero_add_to_gateways( $gateways ) {
    $gateways[] = 'Iwd_Connector_Zero_Gateway';
    return $gateways;
}

if( $this->is_plugin_enabled ){
    add_filter( 'woocommerce_payment_gateways', 'iwd_zero_add_to_gateways' );
}

/**
 * Class Iwd_Connector_Zero_Gateway
 */
class Iwd_Connector_Zero_Gateway extends WC_Payment_Gateway {

    /**
     * Iwd_Connector_Zero_Gateway constructor.
     */
    public function __construct() {
        $this->id                 = 'iwd_gateway_zero_payment';
        $this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( 'Dominate Checkout Zero Subtotal', 'iwd-gateway-zero-payment' );
        $this->method_description = __( 'Allow customers to conveniently checkout with zero subtotal.', 'iwd_gateway_zero_subtotal' );
        $this->title              = 'Zero Subtotal';
        $this->description        = 'Zero Subtotal';
        $this->supports           = array( 'products' );
        $this->enabled            = 'yes';
    }

}
