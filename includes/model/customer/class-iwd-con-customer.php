<?php

/**
 * Class Iwd_Connector_Customer
 */
class Iwd_Connector_Customer
{
	const IWD_EMAIL       = 'email';
	const IWD_FIRST_NAME  = 'first_name';
	const IWD_LAST_NAME   = 'last_name';
	const IWD_ADDRESS     = 'address';
	const IWD_COUNTRY     = 'country';
	const IWD_STATE       = 'state';
	const IWD_POSTCODE    = 'postcode';
	const IWD_CITY        = 'city';
	const IWD_REGION_ID   = 'region_id';
	const IWD_REGION_CODE = 'region_code';
	const IWD_PHONE       = 'phone';

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * Get shipping & billing addresses
	 *
	 * @param $session_data
	 * @return mixed
	 */
	public function getAddresses( $session_data ) {
		$customer_data = unserialize( $session_data['customer'] );

		$data['billing']  = $this->getBilling( $customer_data );
		$data['shipping'] = $this->getShipping( $customer_data );
		$data['ship_bill_to_different_address'] = $customer_data['address_2'];

		return $data;
	}

	/**
	 * Format shipping address
	 *
	 * @param $customer_data
	 * @return array
	 */
	public function getBilling( $customer_data ) {

		return array(
			self::IWD_EMAIL       => $customer_data[ self::IWD_EMAIL ],
			self::IWD_FIRST_NAME  => $customer_data[ self::IWD_FIRST_NAME ],
			self::IWD_LAST_NAME   => $customer_data[ self::IWD_LAST_NAME ],
			self::IWD_ADDRESS     => $customer_data['address_1'],
			self::IWD_COUNTRY     => $customer_data[ self::IWD_COUNTRY ],
			self::IWD_STATE       => $customer_data[ self::IWD_STATE ],
			self::IWD_REGION_ID   => $customer_data[ self::IWD_STATE ],
			self::IWD_REGION_CODE => '',
			self::IWD_CITY        => $customer_data[ self::IWD_CITY ],
			self::IWD_POSTCODE    => $customer_data[ self::IWD_POSTCODE ],
			self::IWD_PHONE       => $customer_data[ self::IWD_PHONE ],
		);

	}

	/**
	 * Format billing address
	 *
	 * @param $customer_data
	 * @return array
	 */
	public function getShipping( $customer_data ) {
		return array(
			self::IWD_EMAIL       => $customer_data[ self::IWD_EMAIL ],
			self::IWD_FIRST_NAME  => $customer_data['shipping_first_name'],
			self::IWD_LAST_NAME   => $customer_data['shipping_last_name'],
			self::IWD_ADDRESS     => $customer_data['shipping_address_1'],
			self::IWD_COUNTRY     => $customer_data['shipping_country'],
			self::IWD_STATE       => $customer_data['shipping_state'],
			self::IWD_REGION_ID   => $customer_data['shipping_state'],
			self::IWD_REGION_CODE => '',
			self::IWD_CITY        => $customer_data['shipping_city'],
			self::IWD_POSTCODE    => $customer_data['shipping_postcode'],
			self::IWD_PHONE       => $customer_data['shipping_phone'],
		);
	}

	/**
	 * Save address
	 *
	 * @param $addresses
	 * @param $customer_id
	 * @throws Exception
	 */
	public function saveAddresses( $addresses, $customer_id ) {
		WC()->customer = new WC_Customer( $customer_id, true );
		WC()->customer->set_billing_email( $addresses->email );

		$this->setBilling( $addresses->billing );
		$this->setShipping( $addresses->shipping );
	}

	/**
	 * Save shipping address
	 *
	 * @param $shipping
	 */
	public function setShipping( $shipping ) {
		WC()->customer->set_shipping_first_name( $shipping->first_name );
		WC()->customer->set_shipping_last_name( $shipping->last_name );
		WC()->customer->set_shipping_address( $shipping->address );
		WC()->customer->set_shipping_country( $shipping->country );
		WC()->customer->set_shipping_state( $shipping->region_id ? $shipping->region_id : $shipping->state );
		WC()->customer->set_shipping_city( $shipping->city );
		WC()->customer->set_shipping_postcode( $shipping->postcode );
		WC()->customer->set_shipping_phone( $shipping->phone );

		WC()->customer->save();

	}

	/**
	 * Save billing address
	 *
	 * @param $billing
	 */
	public function setBilling( $billing ) {
		WC()->customer->set_billing_first_name( $billing->first_name );
		WC()->customer->set_billing_last_name( $billing->last_name );
		WC()->customer->set_billing_address( $billing->address );
		WC()->customer->set_billing_country( $billing->country );
		WC()->customer->set_billing_state( $billing->region_id ? $billing->region_id : $billing->state );
		WC()->customer->set_billing_city( $billing->city );
		WC()->customer->set_billing_postcode( $billing->postcode );
		WC()->customer->set_billing_phone( $billing->phone );

		WC()->customer->save();

	}

	/**
	 * @throws Exception
	 */
	public function assignCustomerToOrder()
	{
	    $this->id = $this->data['id'];

        if (email_exists($this->data['email'])) {
            $customer = get_user_by('email', $this->data['email']);

            if ($customer->data && $customer->data->ID) {
	            $this->id = $customer->data->ID;
            }
        } else {
	        $this->id = wc_create_new_customer(
				$this->data['email'],
				$this->data['first_name'] . ' ' . $this->data['last_name'],
				wp_generate_password(12, true)
            );

            $this->updateCustomerAddress();
        }
    }

	/**
	 * @return void
	 * @throws Exception
	 */
    public function updateCustomerAddress()
    {
        $customer = new WC_Customer($this->id);

        $customer->set_email($this->data['email']);
        $customer->set_first_name($this->data['first_name']);
        $customer->set_last_name($this->data['last_name']);
        $customer->set_billing_first_name($this->data['first_name']);
        $customer->set_billing_last_name($this->data['last_name']);
        $customer->set_billing_company($this->data['company']);
        $customer->set_billing_address_1($this->data['address_1']);
        $customer->set_billing_address_2($this->data['address_2']);
        $customer->set_billing_city($this->data['city']);
        $customer->set_billing_postcode($this->data['postcode']);
        $customer->set_billing_country($this->data['country']);
        $customer->set_billing_state($this->data['state']);
        $customer->set_billing_email($this->data['email']);
        $customer->set_billing_phone($this->data['phone']);

        if (isset($this->data['shipping_first_name'])) {
            $customer->set_shipping_first_name($this->data['shipping_first_name']);
            $customer->set_shipping_last_name($this->data['shipping_last_name']);
            $customer->set_shipping_company($this->data['shipping_company']);
            $customer->set_shipping_address_1($this->data['shipping_address_1']);
            $customer->set_shipping_address_2($this->data['shipping_address_2']);
            $customer->set_shipping_city($this->data['shipping_city']);
            $customer->set_shipping_postcode($this->data['shipping_postcode']);
            $customer->set_shipping_country($this->data['shipping_country']);
            $customer->set_shipping_state($this->data['shipping_state']);
            $customer->set_shipping_phone($this->data['shipping_phone']);
        }

        $customer->save();
    }

	/**
	 * @param $data
	 *
	 * @return void
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
}
