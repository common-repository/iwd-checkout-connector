<?php

/**
 * Add payment method to $gateways
 *
 * @param $gateways
 * @return mixed
 */
function iwd_pay_add_to_gateways( $gateways ) {
	$gateways[] = 'Iwd_Connector_Gateway_Pay';
	return $gateways;
}
if( $this->is_plugin_enabled ) {
    add_filter('woocommerce_payment_gateways', 'iwd_pay_add_to_gateways');
}

/**
 * Class Iwd_Connector_Gateway_Pay
 */
class Iwd_Connector_Gateway_Pay extends WC_Payment_Gateway {

	const AMOUNT                  = 'amount';
	const ORDER_ID                = 'order_id';
	const INTEGRATION_KEY         = 'integration_key';
	const INTEGRATION_SECRET      = 'integration_secret';
	const TRANSACTION_ID          = 'txn_id';
	const CURRENCY                = 'currency';
	const REFUND_URL              = 'platform/refund';
	const VOID_URL                = 'platform/void';
	const CAPTURE_URL             = 'platform/capture';
	const TRANSACTION_STATUS      = '_iwd_transaction_status';
	const TRANSACTION_CAPTURED    = 'capture';
	const TRANSACTION_VOIDED      = 'void';
	const TRANSACTION_ID_RESPONSE = 'transaction_id';
	const PAYMENT_INFO            = '_iwd_payment_info';
    const COMMENT = 'comment';

	/**
	 * Iwd_Connector_Gateway_Pay constructor.
	 */
	public function __construct() {
		global $post;
		$order = wc_get_order( $post->ID  ?? '');
		$title = !empty($order) ? $order->get_payment_method_title() : 'Dominate Checkout Pay';

		$this->id                 = 'iwd_gateway_pay';
		$this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
		$this->has_fields         = false;
		$this->method_title       = $title;
		$this->method_description = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_pay' );
		$this->title              = $title;
		$this->description        = __( 'Allow customers to conveniently checkout with '.$title, 'iwd_gateway_pay' );
		$this->supports           = array( 'products', 'refunds' );
        $this->enabled            = 'yes';

		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Refund
	 *
	 * @param int    $order_id
	 * @param null   $amount
	 * @param string $reason
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order = wc_get_order( $order_id );

		if ( ! $this->can_refund_order( $order ) ) {
			return new WP_Error( 'error', __( 'Refund failed.' ) );
		}

		if ( $order->get_meta( self::TRANSACTION_STATUS ) == 'authorize' ) {
			return new WP_Error( 'error', __( 'Cannot refund a transaction unless it is settled. ' ) );
		}

		if ( $order->get_meta( self::TRANSACTION_STATUS ) == 'void' ) {
			return new WP_Error( 'error', __( 'Cannot refund a voided transaction. ' ) );
		}

		$body = array(
			self::TRANSACTION_ID     => $order->get_transaction_id(),
			self::AMOUNT             => $amount,
			self::ORDER_ID           => $order->get_meta('_order_number'),
			self::INTEGRATION_KEY    => IWD_CONNECTOR()->helper->getApiKey(),
			self::INTEGRATION_SECRET => IWD_CONNECTOR()->helper->getSecretKey(),
			self::CURRENCY           => $order->get_currency(),
		);

		$raw_response = wp_safe_remote_post(
			IWD_CONNECTOR()->helper->getAppUrl() . self::REFUND_URL,
			$this->buildRequest( $body )
		);

		$response = json_decode( $raw_response['body'], true );

		if ( $this->checkResultCode( $response ) ) {
			/* translators: %s: search term */
			$order->add_order_note( sprintf( __( 'Refunded %1$s - Refund Transaction ID: "%2$s"' ), $amount, $response[ self::TRANSACTION_ID_RESPONSE ] ) );

            if (isset($response[self::COMMENT])) {
                $order->add_order_note( sprintf( $response[self::COMMENT] ) , 1,true);
            }

			return true;
		}

		return false;
	}

	/**
	 * Void
	 *
	 * @param $order
	 * @return bool|WP_Error
	 */
	public function process_void( $order ) {

		if ( ! $this->can_refund_order( $order ) ) {
			return new WP_Error( 'error', __( 'Void failed.' ) );
		}

		if ( $order->get_meta( self::TRANSACTION_STATUS ) == 'capture' || $order->get_meta( self::TRANSACTION_STATUS ) == 'auth_and_capture' ) {
			return new WP_Error( 'error', __( 'You cannot void a fully captured authorization.' ) );
		}

		$body = array(
			self::TRANSACTION_ID     => $order->get_transaction_id(),
			self::ORDER_ID           => $order->get_id(),
			self::INTEGRATION_KEY    => IWD_CONNECTOR()->helper->getApiKey(),
			self::INTEGRATION_SECRET => IWD_CONNECTOR()->helper->getSecretKey(),
		);

		$raw_response = wp_safe_remote_post(
			IWD_CONNECTOR()->helper->getAppUrl() . self::VOID_URL,
			$this->buildRequest( $body )
		);

		$response = json_decode( $raw_response['body'], true );

		if ( $this->checkResultCode( $response ) ) {
			$order->add_meta_data( self::TRANSACTION_STATUS, self::TRANSACTION_VOIDED, true );
			/* translators: %s: search term */
			$order->add_order_note( sprintf( __( 'Transaction %1$s has been voided in PayPal.' ), $order->get_transaction_id() ) );
			$order->update_status( 'cancelled' );
			return true;
		}

		return new WP_Error( 'error', __( $response['errorMsg'] ) );
	}

	/**
	 * Capture
	 *
	 * @param $order
	 * @return bool|WP_Error
	 */
	public function process_capture( $order ) {

		if ( $order->get_meta( self::TRANSACTION_STATUS ) !== 'authorize' ) {
			return new WP_Error( 'error', __( 'You cannot captured transaction.' ) );
		}

		$body = array(
			self::AMOUNT             => $order->get_total(),
			self::ORDER_ID           => $order->get_id(),
			self::TRANSACTION_ID     => $order->get_transaction_id(),
			self::INTEGRATION_KEY    => IWD_CONNECTOR()->helper->getApiKey(),
			self::INTEGRATION_SECRET => IWD_CONNECTOR()->helper->getSecretKey(),
			self::CURRENCY           => $order->get_currency(),
		);

		$raw_response = wp_safe_remote_post(
			IWD_CONNECTOR()->helper->getAppUrl() . self::CAPTURE_URL,
			$this->buildRequest( $body )
		);

		$response = json_decode( $raw_response['body'], true );

		if ( $this->checkResultCode( $response ) ) {
			$order->add_meta_data( self::TRANSACTION_STATUS, self::TRANSACTION_CAPTURED, true );
			$order->set_transaction_id( $response[ self::TRANSACTION_ID_RESPONSE ] );
			/* translators: %s: search term */
			$order->add_order_note( sprintf( __( 'Captured amount of %1$s online. Transaction ID: "%2$s"' ), $order->get_total(), $response[ self::TRANSACTION_ID_RESPONSE ] ) );
			$order->save();
			return true;
		}

		return new WP_Error( 'error', __( $response['errorMsg'] ) );
	}

	/**
	 * Headers for request
	 *
	 * @param array $body
	 * @return array
	 */
	private function buildRequest( array $body ) {
		return array(
			'method'      => 'POST',
			'body'        => $body,
			'timeout'     => 70,
			'httpversion' => '1.1',
		);

	}

	/**
	 * Check response
	 *
	 * @param $response
	 * @return bool
	 */
	private function checkResultCode( $response ) {
		if ( isset( $response['resultCode'] ) && 1 == $response['resultCode']  ) {
			return true;
		}
		return false;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		$info = json_decode($order->get_meta( self::PAYMENT_INFO ));

		if (!$sent_to_admin && $info) {
			$tdStyle = 'width: 50%; padding: 2px 2px 2px 0;';

			$html = '<h2 style="margin-top: 30px">'.__('Payment Information', 'woocommerce').'</h2>';
			$html .= '<table style="width: 100%">';

			foreach($info as $title => $value) {
				$html .= '<tr>';
				$html .= '<td style="'.$tdStyle.'"><b>'.__($title, 'woocommerce').'</b></td>';
				$html .= '<td style="'.$tdStyle.'">'.$value.'</td>';
				$html .= '</tr>';

				if ($title === 'Payment Reference') {
					$html .= '<tr><td style="padding-bottom: 20px"></td></tr>';
				}
			}

			$html .= '</table>';

			$message = 'Please also note that our company has assigned the due purchase price claim from your order '
			           .'including any ancillary claims to Ratepay GmBH. The owner of the claim is thus Ratepay GmBH. '
			           .'A debt-discharging service is only possible to Ratepay GmBH, stating the purpose of use. '
			           .'The additional terms and conditions and the data protection notice of Ratepay GmBH apply:';

			$html .= '<p style="margin-top: 20px; font-size: 11px">';
			$html .= __($message);
			$html .= '<br><a href="https://www.retepay.com/legal/" target="_blank">https://www.retepay.com/legal/</a>';
			$html .= '</p>';

			$html .= '<h3 style="margin: 30px 0 10px;">'.__('Questions About Payment', 'woocommerce').'</h3>';
			$html .= '<table style="width: 100%; margin-bottom: 30px">';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Online', 'woocommerce').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="https://myratepay.com" target="_blank">myratepay.com</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Email', 'woocommerce').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="mailto:payment@ratepay.com">payment@ratepay.com</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Telephone', 'woocommerce').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="tel:+4930983208620">+49 30 9832086 20</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Monday - Friday', 'woocommerce').'</td>';
			$html .= '<td style="'.$tdStyle.'">08:00 - 19:00 UHR</td>';
			$html .= '</tr>';
			$html .= '</table>';

			echo $html;
		}
	}
}
