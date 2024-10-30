<?php

/**
 * Class Iwd_Connector_CartSession
 */
class Iwd_Connector_CartSession extends WC_Session_Handler {

	/**
	 * Update data in database
	 *
	 * @param $old_session_key
	 */
	public function changeData( $old_session_key ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}woocommerce_sessions SET `session_value`=%s, `session_expiry`=%d WHERE session_key=%s",
				maybe_serialize( $this->_data ),
				$this->_session_expiration,
				$old_session_key
			)
		);

        $this->destroy_session();
	}

}
