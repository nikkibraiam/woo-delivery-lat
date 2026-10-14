<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
 
function ajax_woo_delivery_lat_load_kml() {
    $file_name = $_POST["supporttitle"];
    $path_file = "wp-content/uploads/woo_delivery_lat/kmz/$file_name";
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $path_file;
    $url = get_site_url();
    move_uploaded_file( $_FILES['file']['tmp_name'], $full_path);
    $element = [
        "url"=>$url,
        "path"=>$path_file
    ];
    echo json_encode($element);
    // Don't forget to stop execution afterward.
    wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_load_kml', 'ajax_woo_delivery_lat_load_kml' );

 
function ajax_woo_delivery_lat_load_cookie() {
    $office = $_POST["office"];
    $address = $_POST["address"];
    $locationlat = $_POST["locationClient_lat"];
    $locationlng = $_POST["locationClient_lng"];
    setcookie("woo_del_lat_office", $office, time()+6000, "/");
    setcookie("woo_del_lat_address", htmlentities($address), time()+6000, "/");
    setcookie("woo_del_lat_locationlat", $locationlat, time()+6000, "/");
    setcookie("woo_del_lat_locationlng", $locationlng, time()+6000, "/");

    echo json_encode("{ ok: true }");
    
    wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_load_cookie', 'ajax_woo_delivery_lat_load_cookie' );
add_action( 'wp_ajax_nopriv_woo_delivery_lat_load_cookie', 'ajax_woo_delivery_lat_load_cookie' );



function ajax_woo_delivery_lat_clear_cookie() {
    setcookie("woo_del_lat_office", "", time()-600, "/");
    setcookie("woo_del_lat_address", "", time()-600, "/");
    setcookie("woo_del_lat_locationlat", "", time()-600, "/");
    setcookie("woo_del_lat_locationlng", "", time()-600, "/");

    echo json_encode("{ ok: true }");
    
    wp_die();
}
add_action( 'wp_ajax_woo_delivery_lat_clear_cookie', 'ajax_woo_delivery_lat_clear_cookie' );
add_action( 'wp_ajax_nopriv_woo_delivery_lat_clear_cookie', 'ajax_woo_delivery_lat_clear_cookie' );
 
function ajax_woo_delivery_lat_add_token() {
    $user = wp_get_current_user();
    $allowed_roles = array('editor', 'administrator', 'author');
    if( array_intersect($allowed_roles, $user->roles ) ) {
        $metaToken = get_user_meta($user->ID, "woo_del_lat_token");
        if($metaToken){
            update_user_meta($user->ID, "woo_del_lat_token", $_POST["token"]);
        }else{
            add_user_meta($user->ID, "woo_del_lat_token", $_POST["token"]);
        }
    }
    echo json_encode("{ ok: true }");
    
    wp_die();
}

add_action( 'wp_ajax_woo_delivery_lat_add_token', 'ajax_woo_delivery_lat_add_token' );