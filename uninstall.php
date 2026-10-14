<?php
/**
 * WooDeliveryLat Uninstall
 *
 * Uninstalling WooDelivert deletes all data of delivery.
 *
 * @package WooDeliveryLat\Uninstaller
 * @version 1.0.0
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


/*
 * Only remove ALL product and page data if WC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {
    
    function woodelat_uninstall(){
        global $wpdb, $wp_version;
        //TODO: Agregar todas las senteción de eliminación de tablas.
    }
    woodelat_uninstall();
}