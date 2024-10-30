<?php
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

/**
 * Class Iwd_Connector_Product_Search
 */
class  Iwd_Connector_Product_Search extends Iwd_Connector_Api
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
                    'product-search',
                    array(
                        'methods' => 'GET',
                        'callback' => array($this, 'get_product'),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );

    }

    /**
     * @param WP_REST_Request $request
     * @return array
     */
    public function get_product(WP_REST_Request $request)
    {

        $args = array(
            'like_name' => $request->get_param( 'name' ),
        );
        add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array($this, 'handle_custom_query_var'), 10,2);
        $result = wc_get_products( $args );


        $product = [];
        foreach ($result as $item){
            $product[] = [
                'name' => $item->get_name(),
                'sku' => $item->get_sku()
            ];
        }


        return ['items' => $product];
    }

    function handle_custom_query_var( $query, $query_vars ) {

        if ( isset( $query_vars['like_name'] ) && ! empty( $query_vars['like_name'] ) ) {
            $query['s'] = esc_attr( $query_vars['like_name'] );
        }

        return $query;
    }
}

