<?php
/**
 * WooDeliveryLat Install
 *
 * Creates or upgrades the plugin tables (branch offices, schedules and their relations).
 *
 * @package WooDeliveryLat\Installer
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

global $woodelat_db_version;
$woodelat_db_version = '1.0.0';

function woodel_install() {

	global $wpdb, $woodelat_db_version;

	$installed_ver = get_option( 'woodelat_db_version' );

	if ( $installed_ver != $woodelat_db_version ) {
		load_branch_office( $wpdb );
		load_schedule( $wpdb );
		load_schedule_details( $wpdb );
		load_office_schedule( $wpdb );
		update_option( 'woodelat_db_version', $woodelat_db_version );
	}
}

function load_branch_office( $wpdb ) {
	$i_prefix = 'wdlat_';

	$table_name = $wpdb->prefix . $i_prefix . 'branch_offices';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description varchar(255),
    phone varchar(15),
    address varchar(255) NOT NULL,
    latitude DECIMAL(11, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    kmlfile varchar(300),
    state BIT DEFAULT 1 NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function load_schedule( $wpdb ) {
	$i_prefix = 'wdlat_';

	$table_name = $wpdb->prefix . $i_prefix . 'schedules';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description varchar(255),
    state BIT DEFAULT 1 NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function load_schedule_details( $wpdb ) {
	$i_prefix = 'wdlat_';

	$table_name = $wpdb->prefix . $i_prefix . 'schedules_details';

	$table_name_parent = $wpdb->prefix . $i_prefix . 'schedules';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
    id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    schedule_id MEDIUMINT(9) NOT NULL,
    week_day SMALLINT NOT NULL,
    start_hour TIME NOT NULL,
    end_hour TIME NOT NULL,
    state BIT DEFAULT 1 NOT NULL,
    PRIMARY KEY  (id),
    FOREIGN KEY (schedule_id) REFERENCES $table_name_parent (id) ON DELETE CASCADE
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function load_office_schedule( $wpdb ) {
	$i_prefix        = 'wdlat_';
	$table_name      = $wpdb->prefix . $i_prefix . 'branch_schedule';
	$table_schedule  = $wpdb->prefix . $i_prefix . 'schedules';
	$table_office    = $wpdb->prefix . $i_prefix . 'branch_offices';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT(9) NOT NULL AUTO_INCREMENT,
        schedule_id MEDIUMINT(9) NOT NULL,
        office_id MEDIUMINT(9) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (schedule_id) REFERENCES $table_schedule (id),
        FOREIGN KEY (office_id) REFERENCES $table_office (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function woodelivery_lat_update_db_check() {
	global $woodelat_db_version;
	if ( get_site_option( 'woodelat_db_version' ) != $woodelat_db_version ) {
		woodel_install();
	}
}
add_action( 'plugins_loaded', 'woodelivery_lat_update_db_check' );
