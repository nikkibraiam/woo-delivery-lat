<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once 'services/branch-office-service.php';

$service = new BranchOfficeService();
if(isset($_GET["delete"])){
  if($_GET["delete"] == true){
    $res = $service->delete($_GET["id-object"]);
  }
}

if(isset($_POST) && count($_POST) > 0){
  $res = $service->save($_POST);
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

<?php if(isset($_GET["edit"]) && $_GET["edit"] == true){ 
  require("branch-office-manager.php");
 } else { ?>

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
        if($result != null && sizeof($result) > 0){
          foreach ($result as $print) { ?>
              <tr>
                <td width='20%'><?php echo $print->id?></td>
                <td width='20%'><?php echo $print->name?></td>
                <td width='20%'><?php echo $print->phone?></td>
                <td width='20%'><?php echo $print->address?></td>
                <td width='20%'>
                  <a href="admin.php?page=woo-delivery-lat/admin/branch-office.php&edit=true&id-object=<?php echo $print->id?>">
                  <button type='button'>Actualizar</button></a> 
                  <a href="admin.php?page=woo-delivery-lat/admin/branch-office.php&id-object=<?php echo $print->id?>&delete=true">
                  <button type='button'>Eliminar</button></a>
                </td>
              </tr>
          <?php
          }
        }else{ ?>
          <tr><td style="text-align: center;" colspan='5'>No se han creado sucursales</td></tr>
        <?php }
    ?>
    </tbody>
  </table>
</div>

<?php }