<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

function wpdocs_register_my_custom_menu_page() {
	add_menu_page(
		__( 'Sucursales', 'woodeliverylat' ),
		'Woo Delivery Lat',
		'manage_options',
		'woo-delivery-lat/admin/branch-office.php',
		'',
		'dashicons-location',
		35
	);
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );


/**
 * Sub menu class
 *
 * @author Mostafa <mostafa.soufi@hotmail.com>
 */
class Sub_menu {

	/**
	 * Autoload method
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'register_sub_menu' ) );
	}

	/**
	 * Register submenu
	 * @return void
	 */
	public function register_sub_menu() {
		add_submenu_page(
			'woo-delivery-lat/admin/branch-office.php',
			__( 'Sucursales', 'woodeliverylat' ),
			'Sucursales',
			'manage_options',
			'woo-delivery-lat/admin/branch-office.php',
			'',
			36
		);

		add_submenu_page(
			'woo-delivery-lat/admin/branch-office.php',
			'Horarios',
			'Horarios',
			'manage_options',
			'woo-delivery-lat/admin/schedule.php',
			'',
			37
		);

		add_submenu_page(
			'woo-delivery-lat/admin/branch-office.php',
			'Configuración',
			'Configuración',
			'manage_options',
			'woo-delivery-lat/admin/configuration-manager.php',
			'',
			38
		);
	}
}

new Sub_menu();
