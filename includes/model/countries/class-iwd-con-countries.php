<?php

/**
 * Class Iwd_Connector_Countries
 */
class Iwd_Connector_Countries {


	/**
	 * Country
	 *
	 * @var WC_Countries
	 */
	protected $countries;

	/**
	 * Iwd_Connector_Countries constructor.
	 */
	public function __construct() {
		global $woocommerce;
		$this->countries = new WC_Countries();
	}

	/**
	 * Return country
	 *
	 * @return array
	 */
	public function prepareCountries() {
		$get_countries = WC()->countries->get_shipping_countries();
		$country       = array();
		foreach ( $get_countries as $value => $label ) {
			$country[] = array(
				'value' => $value,
				'label' => html_entity_decode( $label ),
			);
		}

		return $country;
	}

	/**
	 * Return states
	 *
	 * @return array
	 */
	public function prepareStates() {
		$regions = $this->countries->__get( 'states' );
		$states  = array();
		foreach ( $regions as $key => $country ) {
			foreach ( $country as $value => $label ) {
				$states[] = array(
					'value'      => $value,
					'title'      => $label,
					'country_id' => $key,
					'label'      => $label,
				);
			}
		}

		return $states;
	}

}
