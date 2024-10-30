<?php
/**
 * Fired during plugin activation
 *
 * @link       https://shipsy.io/
 * @since      1.0.0
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/includes
 * @author     shipsyplugins <pradeep.mishra@shipsy.co.in>
 */
class Shipsy_Econnect_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		$this->create_sync_track_order_table();
	}

	/**
	 * Function to create table to store order details.
	 *
	 * @return void
	 */
	public function create_sync_track_order_table() {
		/**
		 * TODO: Cache db calls everywhere.
		 */
		global $wpdb;
		// to create table if the table does not exists.
		// phpcs:ignore
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW tables like %s', $this->wp_sync_track_order() ) ) !== $this->wp_sync_track_order() ) {
			// table generating code while activating plugin.
			// phpcs:ignore
			$sql = "CREATE TABLE `{$this->wp_sync_track_order()}` (
				`orderId` int(11) NOT NULL,
				`shipsy_refno` varchar(100) DEFAULT NULL,
				`track_url` varchar(300) DEFAULT NULL,
				PRIMARY KEY (`orderId`)
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Function to get name of table.
	 *
	 * @return string
	 */
	public function wp_sync_track_order(): string {
		global $wpdb;
		return $wpdb->prefix . 'sync_track_order';
	}
}
