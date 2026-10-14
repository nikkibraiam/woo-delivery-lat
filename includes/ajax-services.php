<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Admin only: upload the KML coverage file of a branch office.
 * Only .kml files are accepted and the file name is generated server side.
 */
function ajax_woo_delivery_lat_load_kml() {
	check_ajax_referer( 'woo_del_lat_kml' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}
	if ( empty( $_FILES['file']['tmp_name'] ) || strtolower( pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ) !== 'kml' ) {
		wp_send_json_error( 'invalid file', 400 );
	}

	$file_name = time() . '-' . wp_generate_password( 6, false ) . '.kml';
	$path_dir  = 'wp-content/uploads/woo_delivery_lat/kmz';
	wp_mkdir_p( ABSPATH . $path_dir );
	$path_file = "$path_dir/$file_name";
	if ( ! move_uploaded_file( $_FILES['file']['tmp_name'], ABSPATH . $path_file ) ) {
		wp_send_json_error( 'upload failed', 500 );
	}
	echo json_encode(
		array(
			'url'  => get_site_url(),
			'path' => $path_file,
		)
	);
	wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_load_kml', 'ajax_woo_delivery_lat_load_kml' );


/**
 * Public: remember the branch office and delivery address chosen by the customer.
 */
function ajax_woo_delivery_lat_load_cookie() {
	$office      = absint( $_POST['office'] ?? 0 );
	$address     = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
	$locationlat = (float) ( $_POST['locationClient_lat'] ?? 0 );
	$locationlng = (float) ( $_POST['locationClient_lng'] ?? 0 );
	setcookie( 'woo_del_lat_office', $office, time() + 6000, '/' );
	setcookie( 'woo_del_lat_address', $address, time() + 6000, '/' );
	setcookie( 'woo_del_lat_locationlat', $locationlat, time() + 6000, '/' );
	setcookie( 'woo_del_lat_locationlng', $locationlng, time() + 6000, '/' );

	echo json_encode( '{ ok: true }' );

	wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_load_cookie', 'ajax_woo_delivery_lat_load_cookie' );
add_action( 'wp_ajax_nopriv_woo_delivery_lat_load_cookie', 'ajax_woo_delivery_lat_load_cookie' );



function ajax_woo_delivery_lat_clear_cookie() {
	setcookie( 'woo_del_lat_office', '', time() - 600, '/' );
	setcookie( 'woo_del_lat_address', '', time() - 600, '/' );
	setcookie( 'woo_del_lat_locationlat', '', time() - 600, '/' );
	setcookie( 'woo_del_lat_locationlng', '', time() - 600, '/' );

	echo json_encode( '{ ok: true }' );

	wp_die();
}
add_action( 'wp_ajax_woo_delivery_lat_clear_cookie', 'ajax_woo_delivery_lat_clear_cookie' );
add_action( 'wp_ajax_nopriv_woo_delivery_lat_clear_cookie', 'ajax_woo_delivery_lat_clear_cookie' );

/**
 * Store the FCM registration token of a staff member's browser so new orders
 * can be pushed to it.
 */
function ajax_woo_delivery_lat_add_token() {
	$user          = wp_get_current_user();
	$allowed_roles = array( 'editor', 'administrator', 'author' );
	if ( array_intersect( $allowed_roles, $user->roles ) ) {
		$token     = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
		$metaToken = get_user_meta( $user->ID, 'woo_del_lat_token' );
		if ( $metaToken ) {
			update_user_meta( $user->ID, 'woo_del_lat_token', $token );
		} else {
			add_user_meta( $user->ID, 'woo_del_lat_token', $token );
		}
	}
	echo json_encode( '{ ok: true }' );

	wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_add_token', 'ajax_woo_delivery_lat_add_token' );
