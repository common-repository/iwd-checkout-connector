<?php

if ( ! class_exists( 'IWD_CONNECTOR' ) ) {

	/**
	 * Class IWD_CONNECTOR
	 */
	class IWD_CONNECTOR {

		/**
		 * Enable plugin
		 *
		 * @var bool
		 */
		public $is_plugin_enabled = false;

		/**
		 * Instance
		 *
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * Admin
		 *
		 * @var Iwd_Connector_Admin
		 */
		public $admin = null;

		/**
		 * Frontend
		 *
		 * @var Iwd_Connector_Frontend
		 */
		public $frontend = null;

		/**
		 * Iframe
		 *
		 * @var Iwd_Connector_Iframe
		 */
		public $iframe;

		/**
		 * Helper
		 *
		 * @var Iwd_Connector_Helper
		 */
		public $helper;

		/**
		 * Address step
		 *
		 * @var Iwd_Connector_AddressStep
		 */
		public $api_address_step;

		/**
		 * Delivery step
		 *
		 * @var Iwd_Connector_DeliveryStep
		 */
		public $api_delivery_step;

		/**
		 * Payment step
		 *
		 * @var Iwd_Connector_PaymentStep
		 */
		public $api_payment_step;

		/**
		 * Auth
		 *
		 * @var Iwd_Connector_Authenticate
		 */
		public $authenticate;

		/**
		 * Cart
		 *
		 * @var Iwd_Connector_Cart
		 */
		public $cart;

		/**
		 * Customer
		 *
		 * @var Iwd_Connector_Customer
		 */
		public $customer;

		/**
		 * Country
		 *
		 * @var Iwd_Connector_Countries
		 */
		public $country;

		/**
		 * Apply coupon
		 *
		 * @var Iwd_Connector_ApplyCoupon
		 */
		public $apply_coupon;

		/**
		 * Session
		 *
		 * @var Iwd_Connector_CartSession
		 */
		public $session;

		/**
		 * Shipping
		 *
		 * @var Iwd_Connector_Shipping
		 */
		public $shipping;

		/**
		 * Order
		 *
		 * @var Iwd_Connector_Place_Order
		 */
		public $order;

		/**
		 * Order data
		 *
		 * @var Iwd_Connector_Order_Info
		 */
		public $order_data;

		/**
		 * Payment
		 *
		 * @var Iwd_Connector_Gateway_Pay
		 */
		public $payment;

		/**
		 * Connection
		 *
		 * @var Iwd_Connector_Check_Connection
		 */
		public $check_connection;

		/**
		 * Payment action
		 *
		 * @var Iwd_Connector_Payment_Action
		 */
		public $payments_action;

		/**
		 * Change status
		 *
		 * @var Iwd_Change_Order_Status
		 */
		public $change_order_status;

		/**
		 * Paypal
		 *
		 * @var Iwd_Connector_PayPal_Checkout
		 */
		public $paypal_checkout;

		/**
		 * Paypal buttons
		 *
		 * @var Iwd_Connector_PayPal_Buttons
		 */
		public $paypal_buttons;

		/**
		 * Config
		 *
		 * @var Iwd_Connector_Config_Update
		 */
		public $congig_update;

		/**
		 * Order update
		 *
		 * @var Iwd_Connector_Order_Update
		 */
		public $order_update;

		/**
		 * OPC
		 *
		 * @var Iwd_Connector_OPC
		 */
		public $opc;

        /**
         * @var Iwd_Connector_Zero_Gateway
         */
        private $zero_payment;

        /**
         * @var Iwd_Connector_Product_Search
         */
        private $subscription;

        /**
         * @var IWD_Install
         */
        private $install;

        /**
         * @var Iwd_Connector_Subscription
         */
        private $subscription_notify;

        private $orderStatus;


        private $shippingsMethods;
        /**
         * @var Iwd_Connector_CustomPay_Gateway
         */
        private $custompay;
        /**
         * @var Iwd_Connector_BankTransfer_Gateway
         */
        private $banktransfer;
        private $cashondelivery;
        private $bitcoin;
        private $checkomoney;
        private $purchase;
        /**
         * @var Iwd_Connector_Multiple_Gateway
         */
        private $multiple;

        /**
         * @var IWD_Fail_Order_Email
         */
        private $emails;

        /**
		 * IWD_CONNECTOR constructor.
		 */
		public function __construct() {
			$this->is_plugin_enabled = get_option( 'iwd_connector_enabled' );
			$this->includes();
            $this->install   = new IWD_Install();
			$this->install->create_tables();

			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Instance
		 *
		 * @return IWD_CONNECTOR|null
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Init required classes
		 */
		public function init() {
			if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && 'frontend' == $_REQUEST['context'] ) ) {
				$this->admin   = new Iwd_Connector_Admin();
				$this->payment = new Iwd_Connector_Gateway_Pay();
			} elseif ( $this->is_plugin_enabled ) {
				$this->frontend          = new Iwd_Connector_Frontend();
				$this->iframe            = new Iwd_Connector_Iframe();
				$this->cart              = new Iwd_Connector_Cart();
				$this->customer          = new Iwd_Connector_Customer();
				$this->country           = new Iwd_Connector_Countries();
				$this->api_address_step  = new Iwd_Connector_AddressStep();
				$this->api_delivery_step = new Iwd_Connector_DeliveryStep();
				$this->api_payment_step  = new Iwd_Connector_PaymentStep();
				$this->authenticate      = new Iwd_Connector_Authenticate();
				$this->apply_coupon      = new Iwd_Connector_ApplyCoupon();
				$this->session           = new Iwd_Connector_CartSession();
				$this->shipping          = new Iwd_Connector_Shipping();
				$this->order             = new Iwd_Connector_Place_Order();
				$this->order_data        = new Iwd_Connector_Order_Info();
				$this->paypal_checkout   = new Iwd_Connector_PayPal_Checkout();
				$this->paypal_buttons    = new Iwd_Connector_PayPal_Buttons();
				$this->congig_update     = new Iwd_Connector_Config_Update();
				$this->opc               = new Iwd_Connector_OPC();
                $this->zero_payment      = new Iwd_Connector_Zero_Gateway();
				$this->subscription      = new Iwd_Connector_Product_Search();
				$this->subscription_notify  = new Iwd_Connector_Subscription();
                $this->custompay         = new Iwd_Connector_CustomPay_Gateway();
                $this->multiple          = new Iwd_Connector_Multiple_Gateway();
                $this->banktransfer      = new Iwd_Connector_BankTransfer_Gateway();
                $this->cashondelivery    = new Iwd_Connector_CashOnDelivery_Gateway();
                $this->checkomoney       = new Iwd_Connector_CheckMoney_Gateway();
                $this->purchase          = new Iwd_Connector_PurchaseOrder_Gateway();
			}
			$this->check_connection    = new Iwd_Connector_Check_Connection();
			$this->payments_action     = new Iwd_Connector_Payment_Action();
			$this->change_order_status = new Iwd_Change_Order_Status();
			$this->order_update        = new Iwd_Connector_Order_Update();
			$this->helper              = new Iwd_Connector_Helper();
            $this->orderStatus         = new IWD_Get_Status();
            $this->shippingsMethods    = new IWD_Shipping_Methods();
            $this->emails              = new IWD_Fail_Order_Email();

		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			require_once IWD_CONNECTOR_PATH . 'admin/class-iwd-connector-backend.php';
			require_once IWD_CONNECTOR_PATH . 'public/class-iwd-connector-frontend.php';
			require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-con-iframe.php';
			require_once IWD_CONNECTOR_PATH . 'includes/helper/class-iwd-con-helper.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-address-step.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-delivery-step.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-payment-step.php';
			require_once IWD_CONNECTOR_PATH . 'includes/model/cart/class-iwd-con-cart.php';
			require_once IWD_CONNECTOR_PATH . 'includes/model/customer/class-iwd-con-customer.php';
			require_once IWD_CONNECTOR_PATH . 'includes/model/countries/class-iwd-con-countries.php';
			require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-con-authenticate.php';
			require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-con-apply-coupon.php';
			require_once IWD_CONNECTOR_PATH . 'includes/model/session/class-iwd-con-cart-session.php';
			require_once IWD_CONNECTOR_PATH . 'includes/model/shipping/class-iwd-con-shipping.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-place-order.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-order-data.php';
			require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-payment-gateway.php';
			require_once IWD_CONNECTOR_PATH . 'admin/check-connection/iwd-connector-check-connection.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/order/class-iwd-order-payments-action.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-paypal-checkout.php';
			require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-con-buttons.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-config-update.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-order-update.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-opc.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/order/class-iwd-change-order-status.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/subscription/class-iwd-con-search.php';
			require_once IWD_CONNECTOR_PATH . 'includes/class-iwd-con-install.php';
			require_once IWD_CONNECTOR_PATH . 'includes/api/subscription/class-iwd-con-subscription.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/class-iwd-zero-payment-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/api/offline-payments/class-order-status-api.php';
            require_once IWD_CONNECTOR_PATH . 'includes/api/offline-payments/class-shipping-methods-api.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-custompay-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-multiple-offline-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-banktransfer-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-cashondelivery-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-checkmoney-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/checkout/offline-payments/class-iwd-con-purchaseorder-gateway.php';
            require_once IWD_CONNECTOR_PATH . 'includes/model/emails/order-failed-email.php';

		}

	}

}
