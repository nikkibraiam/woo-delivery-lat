<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

function woolat_product_is_visible( $visible, $product_id ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		$officeId = absint( $_COOKIE['woo_del_lat_office'] ?? $_GET['office'] ?? 0 );
		if ( $visible ) {
			$visible = false;
			$meta    = get_post_meta( $product_id, 'offices' );
			if ( $meta ) {
				$scheduleService = new ScheduleService();
				foreach ( $meta[0] as $office ) {
					if ( $officeId == $office['office_id'] ) {
						if ( isset( $office['schedule'] ) ) {
							$date = new DateTime( 'now', new DateTimeZone( 'America/Bogota' ) );
							foreach ( $office['schedule'] as $schedule ) {
								$sch = $scheduleService->get( $schedule, true );
								if ( $sch->details ) {
									foreach ( $sch->details as $detail ) {
										$day = $date->format( 'w' ) == 0 ? 7 : $date->format( 'w' );
										if ( $day == $detail->week_day ) {
											if ( $date->format( 'H:i' ) >= $detail->start_hour && $date->format( 'H:i' ) <= $detail->end_hour ) {
												return true;
											}
										}
									}
								}
							}
						} else {
							return true;
						}
					}
				}
			}
		}
	}
	return $visible;
}

add_filter( 'woocommerce_product_is_visible', 'woolat_product_is_visible', 10, 2 );

function woocommerce_registe_post( $array ) {
	// print_r($array);
	return $array;
}

add_filter( 'woocommerce_register_post_type_product', 'woocommerce_registe_post', 10, 1 );


// Per-branch price override.
// Simple, grouped and external products
// add_filter('woocommerce_product_get_price', 'custom_price', 99, 2 );
// add_filter('woocommerce_product_get_regular_price', 'custom_price', 99, 2 );
// Variations
add_filter( 'woocommerce_product_variation_get_regular_price', 'custom_price', 99, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'custom_price', 99, 2 );

// Variable (price range)
add_filter( 'woocommerce_variation_prices_price', 'custom_variable_price', 99, 3 );
add_filter( 'woocommerce_variation_prices_regular_price', 'custom_variable_price', 99, 3 );

function custom_price( $price, $product ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		return woolat_change_price( $price, $product );
	}
	return $price;
}

function custom_variable_price( $price, $variation, $product ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		return woolat_change_price( $price, $product );
	}
	return $price;
}

add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );

function add_custom_price( $cart_object ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		$custom_price = woolat_change_price( null, $cart_object );
		if ( $custom_price ) {
			foreach ( $cart_object->cart_contents as $key => $value ) {
				$value['data']->price = $custom_price;
			}
		}
	}
}

function cw_change_product_html( $price_html, $product ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		$unit_price = get_post_meta( $product->id, '_regular_price', true );
		return wc_price( woolat_change_price( $unit_price, $product ) );
	}
	return $price_html;
}
//   add_filter( 'woocommerce_get_price_html', 'cw_change_product_html', 10, 2 );

function cw_change_product_price_cart( $price, $cart_item, $cart_item_key ) {
	if ( isset( $_GET['office'] ) || isset( $_COOKIE['woo_del_lat_office'] ) ) {
		$unit_price = get_post_meta( $cart_item['product_id'], '_regular_price', true );
		$product    = get_post( $cart_item['product_id'] );
		return wc_price( woolat_change_price( $unit_price, $product ) );
	}
	return $price;
}
	add_filter( 'woocommerce_cart_item_price', 'cw_change_product_price_cart', 10, 3 );

function woolat_change_price( $price, $product ) {
	global $metaPos, $posid;
	$officeId = absint( $_COOKIE['woo_del_lat_office'] ?? $_GET['office'] ?? 0 );
	if ( null == $metaPos || $product->id != $posid ) {
		$posid   = $product->id;
		$metaPos = get_post_meta( $product->id, 'offices' );
	}
	if ( $metaPos ) {
		foreach ( $metaPos[0] as $office ) {
			if ( isset( $office['office_id'] ) ) {
				if ( $officeId == $office['office_id'] && $office['price'] > 0 ) {
					return (float) $office['price'];
				}
			}
		}
	}
	return $price;
}

function del_lat_woocommerce_before_shop_loop() {
	if ( isset( $_COOKIE['woo_del_lat_office'] ) ) {
		$officeId      = absint( $_COOKIE['woo_del_lat_office'] );
		$officeService = new BranchOfficeService();
		$office        = $officeId ? $officeService->get( $officeId ) : null;
		if ( null != $office ) { ?>
		<div class="float-office">
			<div class="close-btn" onclick="closeOffice()">
			<span class="dashicons dashicons-no"></span>
			</div>
				<div>
					<div>
						<b><?php echo esc_html( $office->name ); ?></b>
					</div>
				</div>
				<?php
				if ( isset( $_COOKIE['woo_del_lat_address'] ) ) {
					$address = sanitize_text_field( str_replace( '+', ' ', wp_unslash( $_COOKIE['woo_del_lat_address'] ) ) );
					if ( $address ) {
						?>
				<div>Su dirección es <?php echo esc_html( $address ); ?></div>
						<?php
					}
				}
				?>
		</div>
			<?php
		}
	}
	?>
	<script type="text/javascript">
		function closeOffice(){

			var form_data = new FormData();
			form_data.append("action", "woo_delivery_lat_clear_cookie");

			jQuery.ajax({
				url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				type: 'post',
				contentType: false,
				processData: false,
				data: form_data,
				success: function (response) {
					jQuery(".float-office").remove();
					window.location.href = "/";
				},
				error: function (response) {
					console.log("error:");
					console.log(response);
				}
			});
		}
	</script> 
	<link type="text/css" rel="stylesheet" href="<?php echo esc_url( WOO_DEL_LAT_URL . 'public/css/del-lat-styles.css' ); ?>">
	<?php
}

add_action( 'woocommerce_before_shop_loop', 'del_lat_woocommerce_before_shop_loop', 10 );


