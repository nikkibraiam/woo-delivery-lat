<?php
/**
 * WooDeliveryLat Uninstall
 *
 * Deletes the plugin tables and options, only when WC_REMOVE_ALL_DATA is true.
 *
 * @package WooDeliveryLat\Uninstaller
 * @version 1.0.0
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


/*
 * Only remove ALL product and page data if WC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {

	function woodelat_uninstall() {
		global $wpdb;
		// Children first because of the foreign keys.
		foreach ( array( 'wdlat_branch_schedule', 'wdlat_schedules_details', 'wdlat_branch_offices', 'wdlat_schedules' ) as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}$table" );
		}
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'woo\\_del\\_lat\\_%' OR option_name = 'woodelat_db_version'" );
		delete_metadata( 'user', 0, 'woo_del_lat_token', '', true );
	}
	woodelat_uninstall();
}
