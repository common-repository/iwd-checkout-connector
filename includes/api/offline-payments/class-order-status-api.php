<?php

class IWD_Get_Status {

    public function __construct(){
        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'iwd-checkout',
                    'order-status-step',
                    array(
                        'methods'  => 'POST',
                        'callback' => array( $this, 'getStatus' ),
                        'permission_callback' => '__return_true',
                    )
                );
            }
        );
    }

    public function getStatus()
    {

        $status = [];

        foreach (wc_get_order_statuses() as $key => $value){
            $status[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $status;

    }

}