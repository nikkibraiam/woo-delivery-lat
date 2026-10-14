<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// [woo_delivery_lat_store_list page="{name}"]
function woo_delivery_lat_store_list_func($atts) {

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript">
        function gotoPage(officeId){
            var form_data = new FormData();
            form_data.append("office", officeId);
            form_data.append("action", "woo_delivery_lat_load_cookie");

            jQuery.ajax({
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                type: 'post',
                contentType: false,
                processData: false,
                data: form_data,
                success: function (response) {
                    window.location.href = "<?php echo esc_js(esc_url($atts["page"] ?? "")); ?>?office=" + officeId;
                },
                error: function (response) {
                    console.log("error:");
                    console.log(response);
                }
            });
        }
    </script>
    <style>
        .p-6.estado_1{
            cursor: pointer;
        }
    </style>

    <div class="container-fluid">
        <div class="container">
        <?php if($offices) { ?>
            <div class="row p-6 estado_1 stores">
        <?php foreach($offices as $office) { ?>
        <div class="col-md-6 bg-white p-4 text-center">
            <?php do_action( 'woo_del_lat_header_store_detail'); ?>
            <h4><?php echo esc_html($office->name); ?></h4>
            <div><?php echo esc_html($office->address); ?></div>
            <div><?php echo esc_html($office->phone); ?></div>
            <a class="wp-block-button__link" href="javascript:void(0)" onclick="gotoPage(<?php echo absint($office->id); ?>)">Ingresar</a>
            <?php do_action( 'woo_del_lat_footer_store_detail'); ?>
        </div>
        <?php } ?>
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
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'woo_delivery_lat_store_list', 'woo_delivery_lat_store_list_func' );