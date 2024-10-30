<?php

/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_bank_transfer_add_to_gateways( $gateways ) {
    $gateways[] = 'Iwd_Connector_BankTransfer_Gateway';
    return $gateways;
}

if( $this->is_plugin_enabled ){
    add_filter( 'woocommerce_payment_gateways', 'iwd_bank_transfer_add_to_gateways' );
}

class Iwd_Connector_BankTransfer_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $title = IWD_Payments_Helper::getTitle('iwd_gateway_banktransfer') ?? 'IWD Checkout Offline Bank Transfer';
        $this->id                 = 'iwd_gateway_banktransfer';
        $this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( $title, 'iwd-gateway-bank-transfer-payment' );
        $this->method_description = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_bank_transfer' );
        $this->title              = $title;
        $this->description        = $title;
        $this->supports           = array( 'products' );
        $this->enabled            = 'yes';
    }

}
