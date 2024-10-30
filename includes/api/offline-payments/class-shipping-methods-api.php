<?php

class IWD_Shipping_Methods {

    public function __construct(){
        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'iwd-checkout',
                    'shipping-methods-step',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'getShipping' ),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );
    }

    public function getShipping()
    {

        $shipping = [];
        $arr = [];
        foreach ( WC_Shipping_Zones::get_zones() as $zone_id )
        {
            // Get the shipping Zone object
            $shipping_zone = new WC_Shipping_Zone($zone_id['id']);

            // Get all shipping method values for the shipping zone
            $shipping_methods = $shipping_zone->get_shipping_methods( true, 'values' );

            // Loop through each shipping methods set for the current shipping zone
            foreach ( $shipping_methods as $instance_id => $shipping_method )
            {
                $price = $shipping_method->cost ? $shipping_method->cost : '0';
                $arr[$shipping_method->id][] = [
                    'value' => $shipping_method->get_rate_id(),
                    'label' => $shipping_method->title. ' '.$price.html_entity_decode( get_woocommerce_currency_symbol() )
                 ];

                $shipping[$shipping_method->id] = [
                    'value' => $arr[$shipping_method->id],
                    'label' => $shipping_method->get_title(),

                ];

            }

        }

        return $shipping;
    }

}