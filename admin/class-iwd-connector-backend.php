<?php
require_once IWD_CONNECTOR_PATH . 'admin/additional_fields.php';

if ( ! class_exists( 'Iwd_Connector_Admin' ) ) {

	/**
	 * Class Iwd_Connector_Admin
	 */
	class Iwd_Connector_Admin {


		/**
		 * Admin Panel.
		 */
		const IWD_PANEL = 'iwd_wc_panel';

		/**
		 * Admin Panel Page.
		 */
		const IWD_PANEL_PAGE = 'iwd_connector_opc_panel';

		/**
		 * Admin Panel settings
		 */
		const IWD_PANEL_SETTINGS = 'iwd_connector_settings';

		/**
		 * Iwd_Connector_Admin constructor.
		 */
		public function __construct() {
            new Iwd_Additional_Fields();
            
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'options_update' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 40, 2 );
		}

		/**
		 * Add Admin Menu Page & Subpage.
		 */
		public function add_admin_menu() {
			add_menu_page(
				'Dominate',
				'Dominate',
				'manage_options',
				self::IWD_PANEL,
				array( $this, 'create_admin_page' ),
				IWD_CONNECTOR_ASSETS_URL . '/images/dominate.ico',
				62.32
			);

			add_submenu_page(
				self::IWD_PANEL,
				'IWD Agency - Dominate Checkout Connector',
				'Dominate Checkout',
				'manage_options',
				self::IWD_PANEL_PAGE,
				array( $this, 'create_admin_page' )
			);

            $this->create_csv_file();
			$this->remove_duplicated_submenu();
		}

		/**
		 * Remove duplicated submenu links.
		 */
		public function remove_duplicated_submenu() {
			remove_submenu_page( self::IWD_PANEL, self::IWD_PANEL );
		}

		/**
		 * Update Plugin Options.
		 */
		public function options_update() {
			register_setting( self::IWD_PANEL_SETTINGS, 'iwd_connector_enabled' );
			register_setting( self::IWD_PANEL_SETTINGS, 'iwd_subscriptions_enabled' );
			register_setting( self::IWD_PANEL_SETTINGS, 'iwd_connector_integration_key' );
			register_setting( self::IWD_PANEL_SETTINGS, 'iwd_connector_secret_key' );
			register_setting( self::IWD_PANEL_SETTINGS, 'iwd_notification_enabled' );
		}

        /**
         * Create Admin Page with Plugin Settings.
         */
        public function create_admin_page()
        {
            include IWD_CONNECTOR_ADMIN_TEMPLATES_PATH . 'settings-options.php';
        }

        /**
         * @return void
         */
        public function admin_warning()
        {
            echo '<div class="notice notice-error"><p>You don\'t have newsletter subscribers.</p></div>';
        }

		/**
		 * Create csv file
		 */
        public function create_csv_file()
        {
            if (isset($_GET['action']) && $_GET['action'] == 'dominate_csv_file') {
                global $wpdb;
                $table_name = $wpdb->prefix . "dominate_newsletter";
                $subscribers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY `id` DESC");
                if (empty($subscribers)) {
                    $this->admin_warning();
                    exit;
                }

                $wp_filename = "newsletter_subscribers_" . date("d-m-y") . ".csv";

                $wp_file = fopen($wp_filename, "w");
                $fields = array('Email', 'First name', 'Last name', 'Date');
                fputcsv($wp_file, $fields);

                foreach ($subscribers as $subscriber) {
                    $wp_array = array(
                        "email"     => $subscriber->email,
                        "firstname" => $subscriber->firstname,
                        "lastname"  => $subscriber->lastname,
                        "date"      => $subscriber->date
                    );
                    fputcsv($wp_file, $wp_array);
                }

                fclose($wp_file);

                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=" . $wp_filename);
                header("Content-Type: application/csv;");
                readfile($wp_filename);
                exit;
            }
        }

		/**
		 * Add meta boxes
		 *
		 * @param $post_type
		 * @param $post
		 * set buttons to order page
		 */
		public static function add_meta_boxes( $post_type, $post ) {
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			$order          = wc_get_order( $post->ID );
			$payment_method = $order->get_payment_method();
			if ( $payment_method ) {
				$gateways = WC()->payment_gateways()->payment_gateways();

				$gateway = isset( $gateways[ $payment_method ] ) ? $gateways[ $payment_method ] : null;
				if ( ! is_null( $gateway ) && $gateway instanceof Iwd_Connector_Gateway_Pay && $order->get_meta( '_iwd_transaction_status' ) == 'authorize' ) {
					add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'capture_button' ) );
					add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'void_button' ) );
				}

				if ( $order->has_status( array( 'processing', 'pending' ) ) ) {
					add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'ship_button' ) );
				}
			}
		}

		/**
		 * Include void button template
		 */
		public static function void_button() {
			include 'templates/order/iwd-order-void-button.php';
		}

		/**
		 *  Include capture button template
		 */
		public static function capture_button() {
			include 'templates/order/iwd-order-capture-button.php';
		}

		/**
		 *  Include ship button template
		 */
		public static function ship_button() {
			include 'templates/order/iwd-order-ship-button.php';
		}

		/**
		 * Add style and js to admin panel
		 */
		public function admin_assets() {
			global $post;

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

            wp_enqueue_style('iwd_admin_menu_styles', IWD_CONNECTOR_URL . 'admin/assets/css/admin.css', array(), IWD_CONNECTOR_VERSION);
            if ($screen_id == 'dominate_page_iwd_connector_opc_panel') {
                wp_enqueue_script('iwd_admin_menu_js', IWD_CONNECTOR_URL . 'admin/assets/js/admin.js', array('jquery', 'jquery-blockui',), IWD_CONNECTOR_VERSION, true);
                wp_localize_script(
                    'iwd_admin_menu_js',
                    'iwd_site_url',
	                [get_site_url()]
                );
            }
			if ( 'shop_order' === $screen_id ) {
				$order = wc_get_order( $post->ID );
				wp_enqueue_script( 'iwd_admin_order_js', IWD_CONNECTOR_URL . 'admin/assets/js/order.js', array( 'jquery', 'jquery-blockui' ), IWD_CONNECTOR_VERSION, true );
				wp_localize_script(
					'iwd_admin_order_js',
					'iwd_order_action_params',
					array(
						'order_id' => $order->get_id(),
						'capture'  => array(
							'url'     => IWD_CONNECTOR()->payments_action->getCaptureUrl(),
							'message' => __( 'Please click OK if you wish to Capture this transaction.' ),
						),
						'void'     => array(
							'url'     => IWD_CONNECTOR()->payments_action->getVoidUrl(),
							'message' => __( 'Please click OK if you wish to Void this transaction.' ),
						),

					)
				);
			}

		}

	}

}
