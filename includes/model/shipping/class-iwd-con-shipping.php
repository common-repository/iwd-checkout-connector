<?php

/**
 * Class Iwd_Connector_Shipping
 */
class Iwd_Connector_Shipping {

	/**
	 * Return selected shipping methods
	 *
	 * @param $session_data
	 * @return array
	 */
	public function getSelectedShipping( $session_data ) {
		$chosen_shipping  = unserialize( $session_data['chosen_shipping_methods'] );
		$shipping_methods = unserialize( $session_data['shipping_for_package_0'] );

        $data = array(
            'method_code'   => '',
            'carrier_title' => '',
            'method_title'  => '',
            'amount'        => '',
        );

		foreach ( $shipping_methods['rates'] as $shipping_rate ) {

			if ( $shipping_rate->get_id() == $chosen_shipping[0] ) {
				$data = array(
					'method_code'   => $shipping_rate->get_id(),
					'carrier_title' => $shipping_rate->get_label(),
					'method_title'  => '',
					'amount'        => $shipping_rate->get_cost(),
				);
			}
		}

		return $data;

	}

	/**
	 * Return all shipping methods
	 *
	 * @param $session_data
	 * @return array
	 */
	public function getShippingMethods( $session_data ) {
		$shipping_methods = unserialize( $session_data['shipping_for_package_0'] );

		$data = array();
		foreach ( $shipping_methods['rates'] as $shipping_rate ) {
			$data[] = array(
				'method_code'   => $shipping_rate->get_id(),
				'carrier_title' => $shipping_rate->get_label(),
				'method_title'  => '',
				'amount'        => $shipping_rate->get_cost(),
			);

		}

		return $data;
	}

}
