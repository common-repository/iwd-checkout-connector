<?php

/**
 * Class Iwd_Connector_PayPal_Buttons
 */
class Iwd_Connector_PayPal_Buttons {

	const CONTAINER = 'iwd-paypal-container';
	const CHECKOUT_PAGE_PATH = 'checkout';
	private $client_id = '';
	private $bn_code = 'IWD_SP_PCP';

	/**
	 * Iwd_Connector_PayPal_Buttons constructor.
	 */
	public function __construct() {
		$this->checkConfig();
        add_action( 'wp_head', array( $this, 'add_script' ) );
		add_action('wp_enqueue_scripts', array($this, 'iwd_buttons_register_scripts_styles'));
		add_action('woocommerce_proceed_to_checkout', array($this, 'display_paypal_button'), 20);
		add_action('woocommerce_proceed_to_checkout', array($this, 'display_paypal_credit_msg'), 10);
		add_action('woocommerce_after_mini_cart', array($this, 'display_paypal_button'));
		add_action('woocommerce_widget_shopping_cart_before_buttons', array($this, 'display_paypal_credit_msg'));
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_subscription_button' ), 10, 3);
	}

    /**
     * add script
     */
    public function add_script(){
        echo '<script src="https://unpkg.com/@paypal/paypal-js@4.0.6/dist/iife/paypal-js.min.js"></script>';
    }

	/**
	 * Check for new config
	 */
	private function checkConfig() {
		$this->client_id = get_option('client_id') ? get_option('client_id') : $this->client_id;
		$this->bn_code = get_option('bn_code') ? get_option('bn_code') : $this->bn_code;
	}

	/**
	 * Register scripts and styles for paypal buttons
	 */
	public function iwd_buttons_register_scripts_styles() {
		wp_enqueue_script('iwd-buttons', IWD_CONNECTOR_URL . 'public/js/payment/iwd_paypal_button.js', array('jquery', 'jquery-blockui',), IWD_CONNECTOR_VERSION);
		wp_enqueue_script('iwd-buttons-crdmss', IWD_CONNECTOR_URL . 'public/js/payment/iwd_paypal_credit_msg.js', array('jquery', 'jquery-blockui',), IWD_CONNECTOR_VERSION);
		wp_enqueue_style('iwd-buttons-css', IWD_CONNECTOR_URL . 'public/css/iwd_paypal_button.css', array(), IWD_CONNECTOR_VERSION);
        wp_enqueue_script('iwd-buttons-subscr', IWD_CONNECTOR_URL . 'public/js/payment/iwd_paypal_subscription.js', array('jquery', 'jquery-blockui',), IWD_CONNECTOR_VERSION);
        $this->add_braintree_scripts();
	}

    public function add_braintree_scripts()
    {
        wp_enqueue_script('braintree', 'https://js.braintreegateway.com/web/3.85.1/js/client.min.js', false);
        wp_enqueue_script('applepay', 'https://js.braintreegateway.com/web/3.85.1/js/apple-pay.min.js', false);
        wp_enqueue_script('iwdApplePayDataCol', 'https://js.braintreegateway.com/web/3.85.1/js/data-collector.min.js', false);
        wp_enqueue_script('iwd-braintree-apple-pay', IWD_CONNECTOR_URL . 'public/js/payment/braintree/iwd_braintree_apple_pay.js', array('jquery'), IWD_CONNECTOR_VERSION);
    }
	/**
	 * Buttons for cart and mini cart
	 *
	 * @param $containerId
	 * @param $action
	 */
	public function button( $containerId, $action) {
		$button = "<div id='" . $containerId . "' class='iwd-paypal-wrapper'></div>";
		if ('woocommerce_proceed_to_checkout' == $action) {
			echo ent2ncr($button);
		} else {
			echo ent2ncr("<div class='iwd-woocommerce-mini-cart'>" . ent2ncr($button) . '</div>');
		}

	}

    public function buttomsg( $containerId, $action) {
        $button = "<div class='iwd-paypal-credit-msg'></div>";
        if ('woocommerce_proceed_to_checkout' == $action) {
            echo ent2ncr($button);
        } else {
            echo ent2ncr("<div class='iwd-woocommerce-mini-cart' >" . ent2ncr($button) . '</div>');
        }

    }

	/**
	 * Init paypal scripts
	 */
	public function display_paypal_button() {
		$containerId = self::CONTAINER . wp_rand();

		if (!WC()->cart->is_empty() && !empty($this->client_id) && get_option('status') == 1) {
			$this->button($containerId, current_action());
		}
		?>
		<script>
			jQuery(document).ready(function () {
				IWD.PayPal.params = {
					'containerId': '<?php echo esc_html($containerId); ?>',
					'checkoutPagePath': '<?php echo home_url(self::CHECKOUT_PAGE_PATH); ?>',
					'grandTotalAmount': "<?php echo esc_html(WC()->cart->get_total('edit')); ?>",
					'btnShape': '<?php echo esc_html(get_option('btn_shape')); ?>',
					'btnColor': '<?php echo esc_html(get_option('btn_color')); ?>',
                    'creditStatus': '<?php echo esc_html(get_option('paypal_credit_status')); ?>'
				};

                var cart = '<?php echo !WC()->cart->is_empty() ?>',
                    subscriptions = '<?php echo get_option( 'iwd_subscriptions_enabled' ) ?>',
                    enableFunding = '<?= $this->getEnableFundingParam() ?>',
                    scriptParams = {
                        "client-id": "<?php echo esc_html($this->client_id); ?>",
                        "currency": "<?php echo esc_html(get_woocommerce_currency()); ?>",
                        "commit": "false",
                        "intent": "authorize",
                        "components": "buttons,messages",
                        "vault": "false",
                        "data-partner-attribution-id": "<?php echo esc_html($this->bn_code);?>",
                        "merchant-id": '<?php echo esc_html(get_option('merchant_id')); ?>'
                    };

                if(enableFunding !== "") {
                    scriptParams['enable-funding'] = enableFunding;
                }

                window.paypalLoadScript(scriptParams).then((paypal) => {
                    if(subscriptions === '1'){
                        IWD.PayPalSub.init();
                    }
                    if(cart === '1'){
                        IWD.PayPalMsg.init();
                        IWD.PayPal.init();
                    }
                });
			});
		</script>
		<?php
	}

    /**
     * Init paypal scripts
     */
    public function display_paypal_credit_msg() {
        $containerId = self::CONTAINER . wp_rand();

        if (!WC()->cart->is_empty() && get_option('status') == 1) {
            $this->buttomsg($containerId, current_action());
        }

        ?>
        <script>
            jQuery(document).ready(function () {
                IWD.PayPalMsg.params = {
                    'container_id': '<?php echo esc_html($containerId); ?>',
                    'grand_total_amount': "<?php echo esc_html(WC()->cart->get_total('edit')); ?>",
                    'logo_type': 'primary',
                    'logo_position': 'left',
                    'text_color': 'black',
                    'status': '<?php echo esc_html(get_option('paypal_credit_status')); ?>'
                };
            });
        </script>
        <?php
    }

    function add_subscription_button() {
        $data = $this->getsubscriptionData();
        if($data == false || get_option('status') == 0 || empty(get_option( 'iwd_subscriptions_enabled' ))){
            return;
        }
        ?>
        <div class='paypal-subscribe' id='paypal-button-container-s' ></div>
        <script>
            jQuery(document).ready(function () {
                IWD.PayPalSub.params = {
                'plan_id': '<?= $data->plan_id ?>',
                'logo_type': 'primary',
                'logo_position': 'left',
                'text_color': 'black',
                'color': '<?php echo esc_html(get_option('btn_color')); ?>',
                'shape': '<?php echo esc_html(get_option('btn_shape')); ?>',
                'quantity_supported': '<?php echo $data->quantity_supported; ?>',
                };
            });
        </script>
        <?php
    }

    public function getsubscriptionData(){
        if(is_page( 'cart' ) || is_cart()){
            return null;
        }
        global $wpdb;
        $productID = get_the_ID();
        $_pf = new WC_Product_Factory();
        $_product =$_pf->get_product($productID);
        $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}iwd_subscription WHERE sku = '%s' AND active = 1", $_product->get_sku() ) );
        if(empty($result) || empty(get_option( 'iwd_subscriptions_enabled' ))){
            return null;
        }
        return  $result[0];
    }

    public function getEnableFundingParam() {
        $enableFunding = [];

        if(get_option('paypal_venmo_status') === '1') {
            $enableFunding[] = 'venmo';
        }
        if(get_option('paypal_credit_status') === '1') {
            $enableFunding[] = 'paylater';
        }

        return implode(",", $enableFunding);
    }
}
