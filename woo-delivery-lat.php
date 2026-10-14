<?php
/**
 * Plugin Name:       Woo Delivery Lat
 * Plugin URI:        https://github.com/nikkibraiam/woo-delivery-lat
 * Description:       Multi-branch delivery for WooCommerce restaurants: KML coverage zones, store pickup, per-branch prices and schedules, and new-order notifications.
 * Version:           1.0.0
 * Author:            Machinesoft (Braiam Patiño)
 * Author URI:        https://machinesoft.tech
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woodeliverylat
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * WC requires at least: 5.0
 *
 * @package WooDeliveryLat
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

define( 'WOO_DEL_LAT_FILE', __FILE__ );
define( 'WOO_DEL_LAT_URL', plugin_dir_url( __FILE__ ) );

require_once 'install-woodel-lat.php';
require_once 'admin/load-menu.php';
require_once 'admin/woocommerce-admin.php';

require_once 'short-code/map-page.php';
require_once 'short-code/store-list-page.php';

require_once 'includes/woo-filter-products.php';
require_once 'includes/woo-notification-control.php';
require_once 'includes/ajax-services.php';

require 'admin/services/branch-office-service.php';
require 'admin/services/schedule-service.php';

register_activation_hook( WOO_DEL_LAT_FILE, 'woodel_install' );
