<?php 

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

add_action('save_post_product', 'product_published', 10, 3);

function product_published($post_id, $post, $update){
    if(isset($_POST['offices'])){
        update_post_meta($post_id, 'offices', $_POST['offices']);
    }
}


/*
 * Tab
 */
add_filter('woocommerce_product_data_tabs', 'wooDelivery_product_settings_tabs' );
function wooDelivery_product_settings_tabs( $tabs ){
 
	$tabs['wooDelivery'] = array(
		'label'    => 'Woo Delivery',
		'target'   => 'wooDelivery_product_data',
		'priority' => 200,
    );
	return $tabs;
}
 
/*
 * Tab content
 */
add_action( 'woocommerce_product_data_panels', 'wooDelivery_product_panels' );
function wooDelivery_product_panels(){
    require_once 'services/branch-office-service.php';
    $service = new BranchOfficeService();
    $offices = $service->getList();
    $office_options = [];
    $office_options[''] = "--Seleccione Sucursal--";
    if(isset($offices)){
        foreach($offices as $office){
            $office_options[$office->id] = $office->name;
        }
    }
    ?>
    
	<div id="wooDelivery_product_data" class="panel woocommerce_options_panel hidden">
 
    <?php
	woocommerce_wp_select( array(
		'id'          => 'branch_office',
		'value'       => '',
		'label'       => 'Sucursales',
		'options'     => $office_options,
        'style'       => 'width:75%',
        'desc_tip' => true, 
        'description' => 'El producto se mostrará solo en las sucursales seleccionadas.'
	) );

    $update = [];
    if($_GET["post"]){
        $update = get_post_meta($_GET["post"], "offices");
    }
    foreach($update as $child){
        foreach($child as $index => $office){
            if(isset($office["office_id"]) && $office["office_id"]){
                require 'woocommerce-admin-manager.php';
            }
        }
    }
    $office = ['office_id' => null, 'price' => null, 'amount' => null];
    require 'woocommerce-admin-manager.php';
?>
        <!-- <a onclick="showAddress()">Buscar dirección</a>
        <div id="map_canvas" style="width: 516px; height: 387px; margin-bottom:30px; border:1px solid #999;"></div> -->
	</div>
    
    <style>
        .center{
            margin: 0 10px;
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery("#model-office").hide();
            jQuery("#branch_office").change(function(ele){
                if(jQuery("#branch_office").val() && jQuery("#office_"+ jQuery("#branch_office").val()).length == 0){
                    var office = jQuery("#model-office").clone();
                    var index = jQuery(".father").length - 1;

                    jQuery(office).prop("id","office_"+ jQuery("#branch_office").val()).attr("data-index", index);
                    jQuery(office).find("#title").text(jQuery("#branch_office option:selected").text());
                    jQuery(office).find(".office").val(jQuery("#branch_office").val());
                    jQuery(office).appendTo("#wooDelivery_product_data");
                    jQuery(office).show();

                    jQuery(office).find(".office").prop("name","offices["+index+"][office_id]");
                    jQuery(office).find(".price").prop("name","offices["+index+"][price]");
                    jQuery(office).find(".amount").prop("name","offices["+index+"][amount]");
                }
            });
        });

        function addSchedule(element){
            if(jQuery(element).val() && jQuery(element).parents(".father").find(".schedule.item-"+jQuery(element).val()).length == 0){
                var $schedules = jQuery(element).parents(".father").find(".schedules");
                var index = parseInt(jQuery(element).parents(".father").data("index"));
                var scheIndex = jQuery(element).parents(".father").find(".schedule").length;
                $schedules.append('<div class="center schedule item-'+jQuery(element).val()+'"><input type="hidden" name="offices['+index+'][schedule]['+scheIndex+']" value="'+jQuery(element).val()+'"/><span>'+jQuery(element).find("option:selected").text()+'</span><span class="dashicons dashicons-no-alt" onclick="removeSchedule(this)"></span></div>');
            }
        }

        function removeSchedule(element){
            jQuery(element).parents('.schedule').remove();
        }
    </script>
 <?php
}
?>