<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once 'services/branch-office-service.php';
require_once 'constants/week_day.php';
require_once 'constants/hours_day.php';

$service = new ScheduleService();

$unique = (object) array(
	'id'          => null,
	'name'        => null,
	'description' => null,
	'state'       => null,
	'details'     => null,
);
if ( isset( $_GET['id-object'] ) ) {
	$unique = $service->get( absint( $_GET['id-object'] ), true );
}

?>

<div class="wrap">
	<h1 class="wp-heading-inline">Gestion del horario</h1>
	<form action="admin.php?page=woo-delivery-lat/admin/schedule.php" method="post">
	<?php wp_nonce_field( 'woo_del_lat_save_schedule' ); ?>
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
		<tr class="show-admin-bar state-front-wrap">
			<th scope="row">Estado</th>
			<td>
			<label for="state">
				<input name="state" type="checkbox" id="state" value="<?php echo esc_attr( $unique->state ); ?>" checked='checked' />
				Activo?</label>
				<br />
			</td>
		</tr>
		</tbody>
	</table>
	<h3 class="wp-heading-inline">Detalles del Horario</h3>
	<a href="javascript:void(0)" onclick="addDetail()" class="page-title-action">Añadir Detalle</a>
	<table id="schedule-details">
		<thead>
		<tr>
			<th>Día de la semana</th>
			<th>Hora Inicio</th>
			<th>Hora Fin</th>
			<th>Activo?</th>
			<th>Acciones</th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( isset( $unique->id ) && isset( $unique->details ) ) {
			$index = 0;
			foreach ( $unique->details as $detail ) {
				?>
			<tr>
				<td>
				<input type='hidden' value="<?php echo absint( $detail->id ); ?>" class='det-id' id='details[<?php echo absint( $index ); ?>][id]' name='details[<?php echo absint( $index ); ?>][id]'/>
				<select id="details[<?php echo absint( $index ); ?>][week_day]" name="details[<?php echo absint( $index ); ?>][week_day]" class="det-week_day">
					<option value="">--Seleccione--</option>
					<?php foreach ( WOO_DEL_LAT_WEEK_DAYS as $item ) { ?>
					<option value="<?php echo esc_attr( $item['id'] ); ?>" <?php selected( $item['id'], $detail->week_day ); ?>><?php echo esc_html( $item['text'] ); ?></option>
					<?php } ?>
				</select>
				</td>
				<td>
				<select id="details[<?php echo absint( $index ); ?>][start_hour]" name="details[<?php echo absint( $index ); ?>][start_hour]" class="det-start_hour">
					<option value="">--Seleccione--</option>
					<?php foreach ( WOO_DEL_LAT_HOURS_DAYS as $item ) { ?>
					<option value="<?php echo esc_attr( $item['id'] ); ?>" <?php selected( $item['id'], $detail->start_hour ); ?>><?php echo esc_html( $item['text'] ); ?></option>
					<?php } ?>
				</select>
				</td>
				<td>
				<select id="details[<?php echo absint( $index ); ?>][end_hour]" name="details[<?php echo absint( $index ); ?>][end_hour]" class="det-end_hour">
					<option value="">--Seleccione--</option>
					<?php foreach ( WOO_DEL_LAT_HOURS_DAYS as $item ) { ?>
					<option value="<?php echo esc_attr( $item['id'] ); ?>" <?php selected( $item['id'], $detail->end_hour ); ?>><?php echo esc_html( $item['text'] ); ?></option>
					<?php } ?>
				</select>
				</td>
				<td>
				<input type='checkbox' class='det-state' checked id='details[<?php echo absint( $index ); ?>][state]' name='details[<?php echo absint( $index ); ?>][state]'/>
				</td>
				<td>
				<a href='javascript:void(0)' title='Eliminar' onclick='removeDetail(this)' class='button'>Borrar</a>
				</td>
			</tr>
				<?php
				++$index;
			}
		}
		?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar">
	</p>
	</form>
	<select id="week_day" style="display: none;">
	<option value="">--Seleccione--</option>
	<?php foreach ( WOO_DEL_LAT_WEEK_DAYS as $item ) { ?>
		<option value="<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></option>
	<?php } ?>
	</select>
	<select id="hours_days" style="display: none;">
	<option value="">--Seleccione--</option>
	<?php foreach ( WOO_DEL_LAT_HOURS_DAYS as $item ) { ?>
		<option value="<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></option>
	<?php } ?>
	</select>
</div>
<style>
#schedule-details{
	width: 100%;
	margin-top: 30px;
}

#schedule-details input{
	text-align: center;
}

.wp-core-ui select{
	width: 100%;
}

#schedule-details input{
	margin: 0 auto;
	display: block;
}

.wp-core-ui .button, 
.wp-core-ui .button-secondary{
	margin: 0 auto;
	display: table;
}
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
	<?php if ( ! isset( $unique->id ) ) { ?>
		addDetail();
	<?php } ?>
	});

	function removeDetail(element){
	var tr = jQuery(element).parents("tr");
	jQuery(tr).remove();
	jQuery("#schedule-details tbody tr").each(function(index, ele){
		jQuery(ele).find(".det-id").prop("id","details["+index+"][id]").prop("name", "details["+index+"][id]");
		jQuery(ele).find(".det-week_day").prop("id","details["+index+"][week_day]").prop("name", "details["+index+"][week_day]");
		jQuery(ele).find(".det-start_hour").prop("id","details["+index+"][start_hour]").prop("name", "details["+index+"][start_hour]");
		jQuery(ele).find(".det-end_hour").prop("id","details["+index+"][end_hour]").prop("name", "details["+index+"][end_hour]");
		jQuery(ele).find(".det-state").prop("id","details["+index+"][state]").prop("name", "details["+index+"][state]");
	});

	}

	function addDetail(){
	var index = jQuery("#schedule-details tbody tr").length;
	var tr = document.createElement("tr");

	var td2 = document.createElement("td");
	jQuery(td2).append("<input type='hidden' value='' class='det-id' id='details["+index+"][id]' name='details["+index+"][id]'/>");
	var td2child = jQuery("#week_day").clone();
	jQuery(td2child).prop("id","details["+index+"][week_day]").prop("name", "details["+index+"][week_day]").addClass("det-week_day").show();
	jQuery(td2).append(td2child);

	var td3 = document.createElement("td");
	var td3child = jQuery("#hours_days").clone();
	jQuery(td3child).prop("id","details["+index+"][start_hour]").prop("name", "details["+index+"][start_hour]").addClass("det-start_hour").show();
	jQuery(td3).append(td3child);

	var td4 = document.createElement("td");
	var td4child = jQuery("#hours_days").clone();
	jQuery(td4child).prop("id","details["+index+"][end_hour]").prop("name", "details["+index+"][end_hour]").addClass("det-end_hour").show();
	jQuery(td4).append(td4child);

	var td5 = document.createElement("td");
	jQuery(td5).append("<input type='checkbox' class='det-state' checked id='details["+index+"][state]' name='details["+index+"][state]'/>");

	var td6 = document.createElement("td");
	jQuery(td6).append("<a href='javascript:void(0)' title='Eliminar' onclick='removeDetail(this)' class='button'>Borrar</a>");

	jQuery(tr).data("index", index).append([td2, td3, td4, td5, td6]);
	jQuery("#schedule-details").append(tr);

	}
</script>