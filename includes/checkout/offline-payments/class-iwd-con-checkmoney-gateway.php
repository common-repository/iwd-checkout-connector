<?php

/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_check_money_add_to_gateways( $gateways ) {
    $gateways[] = 'Iwd_Connector_CheckMoney_Gateway';
    return $gateways;
}

if( $this->is_plugin_enabled ){
    add_filter( 'woocommerce_payment_gateways', 'iwd_check_money_add_to_gateways' );
}


class Iwd_Connector_CheckMoney_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $title = IWD_Payments_Helper::getTitle('iwd_gateway_check_or_money_order') ?? 'Check or Money Order';
        $this->id                 = 'iwd_gateway_check_or_money_order';
        $this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( $title, 'iwd-gateway-check-money-payment' );
        $this->method_description = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_check_money' );
        $this->title              = $title;
        $this->description        = $title;
        $this->supports           = array( 'products' );
        $this->enabled            = 'yes';
    }

}
