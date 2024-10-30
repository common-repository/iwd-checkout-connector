<?php

class IWD_Install{

    /**
     * @return void
     */
    public function create_tables(){
        $this->subscriptionTable();
        $this->newsletterTable();
        $this->orderTable();
    }

    /**
     * Create subscription table
     * @return void
     */
    public function subscriptionTable(){
        global $wpdb;
        $plugin_name_db_version = '1.0';
        $table_name = $wpdb->prefix . "iwd_subscription";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		  plan_id varchar(100) DEFAULT '' NOT NULL,
		  product_id varchar(100) DEFAULT '' NOT NULL,
		  sku varchar(100) DEFAULT '' NOT NULL ,
		  merchant_id varchar(100) DEFAULT '' NOT NULL,
		  checkout_instance_id varchar(100) DEFAULT '' NOT NULL, 
		  environment_id varchar(100) DEFAULT '' NOT NULL,
		  env varchar(100) NULL default '',
		  client_id varchar(100) DEFAULT '' NOT NULL,
		  quantity_supported varchar(100) DEFAULT '' NOT NULL,
		  active varchar(100) DEFAULT '' NOT NULL
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'plugin_name_db_version', $plugin_name_db_version );
    }

    /**
     * Create newsletter table
     * @return void
     */
    public function newsletterTable(){
        global $wpdb;
        $plugin_name_db_version = '1.0';
        $table_name = $wpdb->prefix . "dominate_newsletter";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		  email varchar(100) DEFAULT '' NOT NULL ,
		  firstname varchar(100) DEFAULT '' NOT NULL,
		  lastname varchar(100) DEFAULT '' NOT NULL,
          date datetime NOT NULL default '0000-00-00 00:00:00',
          UNIQUE KEY email (email)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'plugin_name_db_version', $plugin_name_db_version );
    }

    /**
     * Create order table
     * @return void
     */
    public function orderTable(){
        global $wpdb;
        $plugin_name_db_version = '1.0';
        $table_name = $wpdb->prefix . "iwd_orders";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		  session_id varchar(100) DEFAULT '' NOT NULL,
		  order_id varchar(100) DEFAULT '' NOT NULL
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'plugin_name_db_version', $plugin_name_db_version );
    }
}