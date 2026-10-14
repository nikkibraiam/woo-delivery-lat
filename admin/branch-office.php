<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once 'services/branch-office-service.php';

$service = new BranchOfficeService();
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'forbidden' );
}
if ( isset( $_GET['delete'] ) ) {
	if ( true == $_GET['delete'] ) {
		check_admin_referer( 'woo_del_lat_delete_branch_office' );
		$res = $service->delete( absint( $_GET['id-object'] ?? 0 ) );
	}
}

if ( isset( $_POST ) && count( $_POST ) > 0 ) {
	check_admin_referer( 'woo_del_lat_save_branch_office' );
	$res = $service->save( wp_unslash( $_POST ) );
}
$result = $service->getList();
?>
<style>
	#TB_window{
	overflow: auto;  
	}

	#TB_ajaxContent{
	overflow:initial;
	margin: 0 auto;
	}
</style>

<?php
if ( isset( $_GET['edit'] ) && true == $_GET['edit'] ) {
	require 'branch-office-manager.php';
} else {
	?>

<div class="wrap">
	<h1 class="wp-heading-inline">Sucursales</h1>
	<a href="admin.php?page=woo-delivery-lat/admin/branch-office.php&edit=true" class="page-title-action">Añadir Nuevo</a>
	<hr class="wp-header-end">
	<table class="wp-list-table widefat striped">
	<thead>
		<tr>
		<th width="20%">ID</th>
		<th width="20%">Name</th>
		<th width="20%">Phone</th>
		<th width="20%">Address</th>
		<th width="20%">Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if ( null != $result && count( $result ) > 0 ) {
		foreach ( $result as $print ) {
			?>
				<tr>
				<td width='20%'><?php echo esc_html( $print->id ); ?></td>
				<td width='20%'><?php echo esc_html( $print->name ); ?></td>
				<td width='20%'><?php echo esc_html( $print->phone ); ?></td>
				<td width='20%'><?php echo esc_html( $print->address ); ?></td>
				<td width='20%'>
					<a href="admin.php?page=woo-delivery-lat/admin/branch-office.php&edit=true&id-object=<?php echo absint( $print->id ); ?>">
					<button type='button'>Actualizar</button></a> 
					<a href="admin.php?page=woo-delivery-lat/admin/branch-office.php&id-object=<?php echo absint( $print->id ); ?>&delete=true&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'woo_del_lat_delete_branch_office' ) ); ?>">
					<button type='button'>Eliminar</button></a>
				</td>
				</tr>
			<?php
		}
	} else {
		?>
			<tr><td style="text-align: center;" colspan='5'>No se han creado sucursales</td></tr>
		<?php
	}
	?>
	</tbody>
	</table>
</div>

	<?php
}