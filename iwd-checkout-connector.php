<?php
/**
 * Plugin Name: Dominate Checkout Suite Saas
 * Plugin URI: https://www.dominate.co/woocommerce
 * Description: Checkout Suite Connector
 * Author: Dominate
 * Version: 1.2.0
 * Author URI: https://www.dominate.co/
 * WC requires at least: 2.6.0
 * WC tested up to: 8.4.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'iwd_connector_init' );

if ( ! function_exists( 'iwd_connector_init' ) ) {

	/**
	 * Initializer.
	 */
	function iwd_connector_init() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'woocommerce_missing_wc_notice' );
		} else {
			/**
			 * Define plugin minimums and constants.
			 */
			define( 'IWD_CONNECTOR_VERSION', '1.2.0' );
			define( 'IWD_CONNECTOR_FILE', __FILE__ );
			define( 'IWD_CONNECTOR_PATH', plugin_dir_path( __FILE__ ) );
			define( 'IWD_CONNECTOR_URL', plugins_url( '/', __FILE__ ) );
			define( 'IWD_CONNECTOR_ASSETS_URL', IWD_CONNECTOR_URL . 'admin/assets/' );
			define( 'IWD_CONNECTOR_ADMIN_TEMPLATES_PATH', IWD_CONNECTOR_PATH . 'admin/templates/' );
			define( 'IWD_CONNECTOR_FRONTEND_TEMPLATES_PATH', IWD_CONNECTOR_PATH . 'public/templates/' );
			define( 'IWD_CONNECTOR_FRONTEND_JS_PATH', IWD_CONNECTOR_URL . 'public/js' );
			/**
			 * Instance main plugin class.
			 */
			IWD_CONNECTOR();
		}
	}
}

if ( ! function_exists( 'IWD_CONNECTOR' ) ) {

	/**
	 * IWD Connector
	 *
	 * @return IWD_CONNECTOR|null
	 */
	function IWD_CONNECTOR() {
		require_once IWD_CONNECTOR_PATH . 'includes/class-iwd-connector.php';
		return IWD_CONNECTOR::instance();
	}
}

/**
 * Print WooCommerce fallback notice.
 *
 * @since 1.0.0
 */
function woocommerce_missing_wc_notice() {
	/* translators: %s: search term */
	echo '<div class="error"><p>' . esc_html( __( 'Dominate Checkout requires WooCommerce to be installed and active. You can download %s here.', 'iwd-woocommerce-checkout-suite' ), '<a href="https://woocommerce.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}

add_action( 'init', 'register_custom_order_statuses' );

if ( ! function_exists( 'register_custom_order_statuses' ) ) {
	/**
	 * @return void
	 */
	function register_custom_order_statuses() {
		register_post_status( 'wc-shipped ', [
			'label'                     => __( 'Shipped', 'woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>',
				'Shipped <span class="count">(%s)</span>' ),
		] );
	}
}

add_filter( 'wc_order_statuses', 'add_custom_order_statuses' );

/**
 * @param $order_statuses
 *
 * @return array
 */
function add_custom_order_statuses($order_statuses) {
	$new_order_statuses = array();

	// add new order status before processing
	foreach ($order_statuses as $key => $status) {
		$new_order_statuses[$key] = $status;

		if ('wc-processing' === $key) {
			$new_order_statuses['wc-shipped'] = __('Shipped', 'woocommerce' );
		}
	}

	return $new_order_statuses;
}