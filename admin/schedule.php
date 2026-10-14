<?php
    defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

    require_once 'services/schedule-service.php';

    $service = new ScheduleService();
    if(!current_user_can("manage_options")){
  wp_die("forbidden");
}
if(isset($_GET["delete"])){
      if($_GET["delete"] == true){
        check_admin_referer("woo_del_lat_delete_schedule");
    $res = $service->delete(absint($_GET["id-object"]));
      }
    }
    
    if(isset($_POST) && count($_POST) > 0){
  check_admin_referer("woo_del_lat_save_schedule");
      $res = $service->save(wp_unslash($_POST));
    }
    $result = $service->getList();
    
    if(isset($_GET["edit"]) && $_GET["edit"] == true){ 
      require("schedule-manager.php");
    } else { ?>
    
    <div class="wrap">
      <h1 class="wp-heading-inline">Horarios</h1>
      <a href="admin.php?page=woo-delivery-lat/admin/schedule.php&edit=true" class="page-title-action">Añadir Nuevo</a>
      <hr class="wp-header-end">
      <table class="wp-list-table widefat striped">
        <thead>
          <tr>
            <th width="20%">ID</th>
            <th width="20%">Name</th>
            <th width="20%">Descripción</th>
            <th width="20%">Estado</th>
          </tr>
        </thead>
        <tbody>
        <?php
            if($result != null && sizeof($result) > 0){
              foreach ($result as $print) { ?>
                  <tr>
                    <td width='20%'><?php echo esc_html($print->id)?></td>
                    <td width='20%'><?php echo esc_html($print->name)?></td>
                    <td width='20%'><?php echo esc_html($print->description)?></td>
                    <td width='20%'><?php echo esc_html($print->state)?></td>
                    <td width='20%'>
                      <a href="admin.php?page=woo-delivery-lat/admin/schedule.php&edit=true&id-object=<?php echo absint($print->id)?>">
                      <button type='button'>Actualizar</button></a> 
                      <a href="admin.php?page=woo-delivery-lat/admin/schedule.php&id-object=<?php echo absint($print->id)?>&delete=true&_wpnonce=<?php echo wp_create_nonce("woo_del_lat_delete_schedule")?>">
                      <button type='button'>Eliminar</button></a>
                    </td>
                  </tr>
              <?php
              }
            }else{ ?>
              <tr><td style="text-align: center;" colspan='5'>No se han creado horarios</td></tr>
            <?php }
        ?>
        </tbody>
      </table>
    </div>
    
    <?php }