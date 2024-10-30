<?php

class IWD_Payments_Helper{

    public static function getTitle($code,  $type ='title'){
        $data = get_option($code);
        $result = json_decode($data, true);
        return $result[$type];
    }
}