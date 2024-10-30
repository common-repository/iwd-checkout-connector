<?php
require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-payments-helper.php';
/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_purchase_order_add_to_gateways( $gateways ) {
    $gateways[] = 'Iwd_Connector_PurchaseOrder_Gateway';
    return $gateways;
}

if( $this->is_plugin_enabled ){
    add_filter( 'woocommerce_payment_gateways', 'iwd_purchase_order_add_to_gateways' );
}


class Iwd_Connector_PurchaseOrder_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $title = IWD_Payments_Helper::getTitle('iwd_gateway_purchaseorder') ?? 'IWD Purchase Order';
        $this->id                 = 'iwd_gateway_purchaseorder';
        $this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( $title, 'iwd-gateway-purchase-order-payment' );
        $this->method_description = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_purchase_order' );
        $this->title              = $title;
        $this->description        = $title;
        $this->supports           = array( 'products' );
        $this->enabled            = 'yes';
    }

}
