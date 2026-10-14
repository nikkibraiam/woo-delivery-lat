<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// [woo_delivery_lat_map page="{name}"]
function woo_delivery_lat_map_func($atts) {

    $office_service = new BranchOfficeService();
    $scheduleService = new ScheduleService();
    $officesdb = $office_service->getList();
    $officesFull = [];
    $offices = [];
    if($officesdb){
        foreach($officesdb as $office){
            $officeFull = $office_service->get($office->id, true);
            if($officeFull->schedules){
                array_push($officesFull, $officeFull);
                foreach($officeFull->schedules as $index => $sch){
                    $sch = $scheduleService->get($sch->id, true);
                    $officeFull->schedules[$index]->details = $sch->details;
                    $date = new DateTime("now", new DateTimeZone('America/Bogota') );
                    if($sch->details){
                        foreach($sch->details as $detail){
                            $day = $date->format('w') == 0? 7 : $date->format('w');
                            if($day == $detail->week_day){
                                if($date->format("H:i") >= $detail->start_hour && $date->format("H:i") <=  $detail->end_hour){
                                    array_push($offices, $office);
                                    break;
                                }
                            }
                        }
                    }else{
                        array_push($offices, $office);
                    }
                }
            }else{
                array_push($offices, $office);
            }
        }
    }
    ob_start(); 
    ?>
    <link type="text/css" rel="stylesheet" href="<?php echo esc_url(WOO_DEL_LAT_URL . "public/css/modal.css"); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo esc_attr(get_option("woo_del_lat_googleKey")); ?>"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/geocodezip/geoxml3/polys/geoxml3.js"></script>
    <script type="text/javascript" src="<?php echo esc_url(WOO_DEL_LAT_URL . "public/js/woo-delivery-geolocation.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo esc_url(WOO_DEL_LAT_URL . "public/js/modal.js"); ?>"></script>
    <script type="text/javascript">
        var offices = [];
        var locationdev = {
            latitude: 0,
            longitude: 0
        }
        var url = "<?php echo esc_js(get_site_url()); ?>";
        jQuery(document).ready(function(){
            open_map(false);
            jQuery("#search").click(function(){
                locationdev  = { address : jQuery(this).val() };
                open_map(false, function(){
                    searchAddress(jQuery("#address").val(), null, function(url, nameOffice, point){
                        var office = null;
                        for(var office of offices){
                            if(url.includes(office.kml)){
                                office = office;
                            }
                        }

                        var form_data = new FormData();
                        form_data.append("office", office.id);
                        form_data.append("address", jQuery("#address").val());
                        form_data.append("locationClient_lat", point.lat());
                        form_data.append("locationClient_lng", point.lng());
                        form_data.append("action", "woo_delivery_lat_load_cookie");

                        jQuery.ajax({
                            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                            type: 'post',
                            contentType: false,
                            processData: false,
                            data: form_data,
                            success: function (response) {
                                jQuery(".modal").addClass("show-modal");
                                jQuery(".modal").show();
                                jQuery("#officeName").text(nameOffice);
                                for(var office of offices){
                                    if(url.includes(office.kml)){
                                        jQuery("#gotoproducts").attr("href", jQuery("#gotoproducts").data("url")+"?office=" + office.id);
                                        break;
                                    }
                                }
                            },
                            error: function (response) {
                                console.log("error:");
                                console.log(response);
                            }
                        });
                        
                    });
                });
            });

            jQuery("#gotoproducts").click(function(){
                window.location.href = jQuery(this).attr("href");
            });
        });

        function open_map(reload, callback = null){
            inicialize(locationdev, "map-draw", reload, function(map, locationdev){
                <?php if(isset($offices)) {
                    foreach($offices as $office) {
                        if(isset($office->kmlfile)){ ?>
                            initialize_GeoCoverage(map, url + "/<?php echo esc_js($office->kmlfile)?>", reload, false);
                            offices.push({
                                "id": "<?php echo absint($office->id)?>",
                                "kml": "<?php echo esc_js($office->kmlfile)?>"
                            });
                <?php } } } ?>
                var latlng = new google.maps.LatLng(locationdev.latitude, locationdev.longitude);
                addPointerMap(latlng);
                if(callback){
                    callback();
                }
            });
        }
    </script>
    <div id="woo-delivery-map" class="bg-white">
    <?php if($offices) { ?>
        <div class="content-map">
            <div class="address">
                <?php do_action('woo_del_lat_before_form_map'); ?>
                <label>Ingrese la dirección</label>
                <input type="text" id="address"/>
                <button id="search">Buscar</button>
            </div>
            <div id="map-draw" style="width:100%; height:400px;"></div>
        </div>
        <?php } else { ?> 
        <div>
            <h2>No tenemos sedes disponibles por el momento.</h2> 
            <div>
                <p><b>Nuestras sedes</b></p>
                <?php foreach($officesFull as $office){ ?>
                    <div>
                        <div><b><?php echo esc_html($office->name); ?></b></div>
                        <div><?php echo esc_html($office->address); ?></div>
                        <?php if($office->schedules){ ?>
                            <div>Horario</div>
                            <?php foreach($office->schedules as $schedule){
                                foreach($schedule->details as $detail) ?>
                                <div><?php echo esc_html("Día $detail->week_day desde las $detail->start_hour hasta las $detail->end_hour") ?></div>
                        <?php }
                         } ?>
                    </div>
                    <br/>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    </div> 
    <div class="modal alignfull">
        <div class="modal-content">
            <span class="close-button">×</span>
            <?php do_action('woo_del_lat_before_modal_confirm_store'); ?>
            <div style="width:100%; display:block;">
                <h4>El restaurante mas cercano es <br/><b id="officeName"></b></h4>
                <div style="margin: 0 auto;">
                    <a href="#" data-url="<?php echo esc_url($atts["page"] ?? "")?>" id="gotoproducts" class="button btn" style="margin: 10px;">Ver Productos</a>
                    <a href="#" class="button btn" style="margin: 10px;" onclick="jQuery('.modal').removeClass('show-modal')">Cambiar Dirección</a>
                </div>
            </div>
        </div>
    </div>
    <?php 
	return ob_get_clean();
}
add_shortcode( 'woo_delivery_lat_map', 'woo_delivery_lat_map_func' );