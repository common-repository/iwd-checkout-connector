<?php

add_filter( 'woocommerce_email_order_meta_fields', 'add_additional_information_to_email', 10, 3 );
add_filter( 'woocommerce_order_details_after_order_table_items', 'add_additional_information_to_customer_account', 10, 3 );
add_filter( 'woocommerce_order_details_after_customer_details', 'add_additional_personal_information_customer_account', 10, 3 );

/**
 * @param $order
 * @return void
 */
function add_additional_personal_information_customer_account($order)
{
    $fields = json_decode(get_post_meta($order->id, 'personal_details', true), true);

    if (!empty($fields))
        include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/customer_account/personal_information_section.php';
}

/**
 * @param $order
 * @return void
 */
function add_additional_information_to_customer_account($order)
{
    $placement = [
        'personal_details',
        'shipping_information',
        'billing_information',
        'shipping_methods',
        'payment_methods',
        'order_summary'
    ];

    foreach ($placement as $type){
        $data = json_decode(get_post_meta($order->id, $type, true), true);
        if (empty($data)) continue;

        foreach ($data as $field_label => $field_value) {
            if (empty($field_value)) continue;

            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/customer_account_section.php';
        }
    }
}

/**
 * @param $option
 * @return string
 */
function parseOption($option){
    return $option == 'on' ? 'checked' : '';
}

/**
 * @param $fields
 * @param $sent_to_admin
 * @param $order
 * @return mixed
 */
function add_additional_information_to_email( $fields, $sent_to_admin, $order ) {

    $placement = [
        'personal_details',
        'shipping_information',
        'billing_information',
        'shipping_methods',
        'payment_methods',
        'order_summary'
    ];

    $i = 0;
    $s = 0;
    foreach ($placement as $type){
        $data = json_decode(get_post_meta($order->id, $type, true), true);
        if (empty($data)) continue;

        foreach ($data as $label => $value) {
            if (empty($value)) continue;

            if (!empty($label))
                $fields['additional_fields'.$i++] = array(
                    'label' => __( $label ),
                    'value' => is_array($value) ? '  ' : $value,
                );

            if(is_array($value)){
                foreach ($value as  $l => $val){
                    $fields['additional_options'.$s++] = array(
                        'label' => __( $l ),
                        'value' => parseOption($val),
                    );
                }
            }
        }
    }

    if(!empty($fields)){
        echo '<h2>Additional details</h2>';
    }

    return $fields;
}

class Iwd_Additional_Fields{


    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_fields' ), 40, 2 );
    }

    /**
     * @return void
     */
    public function add_fields(){
        add_action( 'woocommerce_admin_order_data_after_billing_address',array( __CLASS__, 'display_billing_information' ) );
        add_action( 'woocommerce_admin_order_data_after_shipping_address',array( __CLASS__, 'display_shipping_information' ) );
        add_action( 'woocommerce_admin_order_items_after_shipping',array( __CLASS__, 'display_shipping_methods' ) );
        add_action( 'woocommerce_admin_order_totals_after_tax',array( __CLASS__, 'display_order_summary' ) );
        add_action( 'woocommerce_admin_order_totals_after_total',array( __CLASS__, 'display_payment_methods' ) );
        add_action( 'woocommerce_admin_order_data_after_order_details',array( __CLASS__, 'display_personal_information' ) );
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_shipping_information($data) {
        $fields = json_decode(get_post_meta($data->id, 'shipping_information', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/address_information_section.php';
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_billing_information($data) {
        $fields = json_decode(get_post_meta($data->id, 'billing_information', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/address_information_section.php';
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_order_summary($data) {
        $fields = json_decode(get_post_meta($data, 'order_summary', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/payment_section.php';
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_payment_methods($data) {
        $fields = json_decode(get_post_meta($data, 'payment_methods', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/payment_section.php';
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_shipping_methods($data) {
        $fields = json_decode(get_post_meta($data, 'shipping_methods', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/shipping_methods_section.php';
    }

    /**
     * @param $data
     * @return void
     */
    public static function display_personal_information($data) {
        $fields = json_decode(get_post_meta($data->id, 'personal_details', true), true);

        if (!empty($fields))
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . '/order/additional-fields/personal_details_section.php';
    }
}