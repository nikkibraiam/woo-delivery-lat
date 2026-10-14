<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once 'services/branch-office-service.php';
require_once 'services/schedule-service.php';

$service          = new BranchOfficeService();
$schedulesService = new ScheduleService();
$schedules        = $schedulesService->getList( true );

$unique = (object) array(
	'id'          => null,
	'name'        => null,
	'description' => null,
	'phone'       => null,
	'address'     => null,
	'kmlfile'     => null,
	'state'       => 0,
	'latitude'    => null,
	'longitude'   => null,
);
if ( isset( $_GET['id-object'] ) ) {
	$unique = $service->get( absint( $_GET['id-object'] ), true );
}

?>

<div class="wrap">
	<h1 class="wp-heading-inline">Gestion de sucursal</h1>
	<form action="admin.php?page=woo-delivery-lat/admin/branch-office.php" method="post">
	<?php wp_nonce_field( 'woo_del_lat_save_branch_office' ); ?>
	<input type="hidden" id="id" name="id" value="<?php echo esc_attr( $unique->id ); ?>">
	<table class="form-table" role="presentation">
		<tbody>
		<tr class="name-branch-office">
			<th>
			<label for="name">Nombre</label>
			</th>
			<td>
			<input type="text" name="name" id="name" value="<?php echo esc_attr( $unique->name ); ?>" class="regular-text" required>
			</td>
		</tr>
		<tr class="description-branch-office">
			<th>
			<label for="name">Descripción</label>
			</th>
			<td>
			<input type="text" name="description" id="description" value="<?php echo esc_attr( $unique->description ); ?>" class="regular-text" >
			</td>
		</tr>
		<tr class="phone-branch-office">
			<th>
			<label for="phone">Teléfono</label>
			</th>
			<td>
			<input type="text" name="phone" id="phone" value="<?php echo esc_attr( $unique->phone ); ?>" class="regular-text" required>
			</td>
		</tr>
		<tr class="address-branch-office">
			<th>
			<label for="address">Dirección y ciudad</label>
			</th>
			<td>
			<input type="text" name="address" id="address" value="<?php echo esc_attr( $unique->address ); ?>" class="regular-text" required>
			</td>
		</tr>
		<tr class="kmlfile-branch-office">
			<th>
			<label for="kmlfile">Archivo Kml de Google Maps</label>
			</th>
			<td>
			<input type="hidden" id="kmlfile" name="kmlfile" value="<?php echo esc_attr( $unique->kmlfile ); ?>">
			<?php if ( $unique->kmlfile ) { ?>
				<span><a href="<?php echo esc_url( get_site_url() . '/' . $unique->kmlfile ); ?>"><?php echo esc_html( $unique->kmlfile ); ?></a></span><br>
			<?php } ?>
			<form enctype="multipart/form-data">
				<input type="file" id="kmlfileloader" name="kmlfileloader" accept=".kml">
				<input class="save-support" name="save_support" type="button" value="Subir" onclick="loadKml(this)">
				<img id="upload-loader" src="<?php echo esc_url( WOO_DEL_LAT_URL . 'admin/images/loader.gif' ); ?>" style="width: 22px; height: 22px; margin: -5px 0; display:none;"/>
			</form>
			<br><small>Google maps cuenta con la posibilidad de crear las coberturas de su sucursal usando <a href="https://www.google.com/maps/d/" target="_blank">Mis Mapas Google</a>.</small>
			<br><small>En esa sección podrás dibujar el croquis de cobertura de tus servicios, descargar el archivo Kml y subirlo en esta página.</small>
			<br><small>Si no carga un archivo Kml, sus servicios tendran una cobertura de 10Km a la redonda.</small>
			</td>
		</tr>
		<tr class="show-admin-bar state-front-wrap">
			<th scope="row">Estado</th>
			<td>
			<label for="state">
				<input name="state" type="checkbox" id="state" value="<?php echo esc_attr( $unique->state ); ?>" checked='checked' />
				Activo?</label>
				<br />
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<div>
				<h3>Esta es su ubicación y su cobertura</h3>
				<small>En el mapa muestra la cobertura del archivo KML y la dirección ingresada</small>
				<input type="hidden" name="latitude" id="latitude" value="<?php echo esc_attr( $unique->latitude ); ?>">
				<input type="hidden" name="longitude" id="longitude" value="<?php echo esc_attr( $unique->longitude ); ?>">
				<div id="map-draw" style="width: 500px; height:400px;"></div>
			</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div>
	<h3>Horarios asociados a la sucursal</h3>
		<select id="select_schedules" onchange="addSchedule(this)" style="width:100%">
		<option value="">--Seleccione horario a agregar--</option>
		<?php
		if ( isset( $schedules ) ) {
			foreach ( $schedules as $item ) {
				?>
			<option value="<?php echo absint( $item->id ); ?>"><?php echo esc_html( $item->name ); ?></option>
				<?php
			}
		}
		?>
		</select>
		<ol id="schedules">
		<?php
		if ( isset( $unique->schedules ) ) {
			foreach ( $unique->schedules as $index => $schedule ) {
				?>
			<li>
				<input class="sch-id" type="hidden" name="schedules[<?php echo absint( $index ); ?>][id]" value="<?php echo absint( $schedule->idmain ); ?>"/>
				<input class="sch-schedule_id" type="hidden" name="schedules[<?php echo absint( $index ); ?>][schedule_id]" value="<?php echo absint( $schedule->id ); ?>"/>
				<span class="schedule-name"><?php echo esc_html( $schedule->name ); ?></span>
				<a onclick="removeSchedule(this)"><span class="dashicons dashicons-no-alt"></span></a>
			</li>
				<?php
			}
		}
		?>
		</ol>
	</div>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar">
	</p>
	</form>
</div>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo esc_attr( get_option( 'woo_del_lat_googleKey' ) ); ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/geocodezip/geoxml3/polys/geoxml3.js"></script>
<script type="text/javascript" src="<?php echo esc_url( WOO_DEL_LAT_URL . 'public/js/woo-delivery-geolocation.js' ); ?>"></script>
<script type="text/javascript">
	var locationdev = {
	latitude: <?php echo (float) ( $unique->latitude ?? 0 ); ?>,
	longitude: <?php echo (float) ( $unique->longitude ?? 0 ); ?>
	}
	var url = "<?php echo esc_js( get_site_url() ); ?>";
	setTimeout(function(){
	jQuery(document).ready(function(){ 
	  
		open_map(false);
		if(!locationdev.latitude && !locationdev.longitude){
		setTimeout(function(){
			var address = jQuery("#address").val();
			if(!locationdev.latitude && !locationdev.longitude){
			if(!address){
				address = prompt("Ingrese una dirección", "");
			}
			if (address != null) {
				jQuery("#address").val(address);
				locationdev.address = address;
				open_map(false);
			}
			}
		}, 10000);
		}

		jQuery("#address").change(function(){
		locationdev  = { address : jQuery(this).val() };
		open_map(false);
		});
	});
	}, 1000);

	function open_map(reload){
	inicialize(locationdev, "map-draw", reload, function(map, locationdev){
		jQuery("#latitude").val(locationdev.latitude);
		jQuery("#longitude").val(locationdev.longitude);
		if(jQuery("#kmlfile").val()){
		initialize_GeoCoverage(map, url + "/" + jQuery("#kmlfile").val(), reload);
		}
		var latlng = new google.maps.LatLng(locationdev.latitude, locationdev.longitude);
		addPointerMap(latlng);
	});
	}

	function addSchedule(element){
	if(jQuery(element).val() && jQuery(".sch-schedule_id[value='"+jQuery(element).val()+"']").length <= 0){
		var index = jQuery("#schedules li").length;
		jQuery("#schedules").append("<li><input class='sch-schedule_id' type='hidden' name='schedules["+index+"][schedule_id]' value='"+jQuery(element).val()+"' /> "+jQuery("#select_schedules option:selected").text()+" <a onclick='removeSchedule(this)'><span class='dashicons dashicons-no-alt'></span></a></li>");
	}
	}

	function removeSchedule(element){
	jQuery(element).parents("li").remove();
	jQuery("#schedules li").each(function(index, ele){
		jQuery(ele).find(".sch-id").prop("name","schedules["+index+"][id]");
		jQuery(ele).find(".sch-schedule_id").prop("name","schedules["+index+"][schedule_id]");
	});
	}

	function loadKml(element){
	jQuery(element).prop("disabled",true);
	jQuery("#upload-loader").show();
	var file_data = jQuery('#kmlfileloader').prop('files')[0];
	var form_data = new FormData();
	form_data.append('file', file_data);
	form_data.append('action', 'woo_delivery_lat_load_kml');
	form_data.append('_ajax_nonce', '<?php echo esc_js( wp_create_nonce( 'woo_del_lat_kml' ) ); ?>');

	if(jQuery){
		jQuery.ajax({
			url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
			type: 'post',
			contentType: false,
			processData: false,
			data: form_data,
			success: function (response) {
			jQuery(element).prop("disabled",false);
			jQuery("#upload-loader").hide();
			console.log("respuesta");
			console.log(response);
			response = JSON.parse(response);
			jQuery("#kmlfile").val(response.path);
			open_map(true);
			},
			error: function (response) {
			console.log("error:");
			console.log(response);
			jQuery(element).prop("disabled",false);
			jQuery("#upload-loader").hide();
			}
		});
	}
	}
</script>