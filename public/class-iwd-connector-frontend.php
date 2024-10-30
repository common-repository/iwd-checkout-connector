<?php

if ( ! class_exists( 'Iwd_Connector_Frontend' ) ) {

	/**
	 * Class Iwd_Connector_Frontend
	 */
	class Iwd_Connector_Frontend {


		/**
		 * Iwd_Connector_Frontend constructor.
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
			add_filter( 'woocommerce_locate_template', array( $this, 'change_woo_template' ), 10, 3 );
			add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'modify_thank_you_details'), 20, 2 );
			add_filter( 'woocommerce_email_recipient_customer_processing_order', array( $this, 'filter_woocommerce_email_recipient'), 10, 3 );
		}

		/**
		 * Change woo template to iwd
		 *
		 * @param $template
		 * @return string
		 */
		public function change_woo_template( $template ) {
			$re = '/woocommerce\/(templates\/)?(.*)/m';
			preg_match( $re, $template, $matches );
			if (
			    isset( $matches[0] ) && 'woocommerce/templates/checkout/form-checkout.php' == $matches[0] ||
                isset( $matches[0] ) && 'woocommerce/checkout/form-checkout.php' == $matches[0]
            ) {
				$template = IWD_CONNECTOR_FRONTEND_TEMPLATES_PATH . 'iwd-checkout-saas.php';
			}
			return $template;
		}

		/**
		 * Add scripts
		 */
		public function enqueue_scripts() {
			if ( ! is_checkout() ) {
				return;
			}
			wp_register_script( 'connector-resizer', IWD_CONNECTOR_FRONTEND_JS_PATH . '/iframeResizer.js', array( 'jquery' ), IWD_CONNECTOR_VERSION, false );
			wp_enqueue_script( 'connector-resizer' );
		}

		/**
		 * @param $thank_you_title
		 * @param $order
		 *
		 * @return string
		 */
		public function modify_thank_you_details( $thank_you_title ){
            global $wp;

			$html = '<p>'.$thank_you_title.'</p>';
            $order = wc_get_order($wp->query_vars['order-received']);

			if ($order && $order->get_payment_method_title() === 'PayPal - Pay Upon Invoice') {
				$html .= '<p><b>'.__('Important', 'woocommerce').'</b>: ';
				$html .= __('Please review the payment instructions that have been sent to your email.', 'woocommerce').'</p>';
			}

			return $html;
		}

		/**
		 * @param $recipient
		 * @param $order
		 * @param $email
		 *
		 * @return mixed|string
		 */
		public function filter_woocommerce_email_recipient( $recipient, $order, $email ) {
			if ($order->get_payment_method_title() === 'PayPal - Pay Upon Invoice') {
				return null;
			}

			return $recipient;
		}
	}
}
