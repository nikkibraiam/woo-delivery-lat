<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if(isset($_POST["googleKey"]) && current_user_can("manage_options") && check_admin_referer("woo_del_lat_config")){
    update_option("woo_del_lat_googleKey", sanitize_text_field($_POST["googleKey"]));
    update_option("woo_del_lat_firebaseApiKey", sanitize_text_field($_POST["firebaseApiKey"]));
    update_option("woo_del_lat_fcmServerKey", sanitize_text_field($_POST["fcmServerKey"]));
    update_option("woo_del_lat_notificationEmails", sanitize_text_field($_POST["notificationEmails"]));
    update_option("woo_del_lat_activeNotification", isset($_POST["activeNotification"]) ? "on" : "");
    update_option("woo_del_lat_templateEmail", absint($_POST["templateEmail"]));
}

$googleKey = get_option("woo_del_lat_googleKey");
$firebaseApiKey = get_option("woo_del_lat_firebaseApiKey");
$fcmServerKey = get_option("woo_del_lat_fcmServerKey");
$notificationEmails = get_option("woo_del_lat_notificationEmails");
$activeNotification = get_option("woo_del_lat_activeNotification");
$templateEmail = get_option("woo_del_lat_templateEmail");

//Carga un listado de post para la plantilla.
$args = array(
    'post_type'=> 'post',
    'orderby'    => 'post_title',
    'order'    => 'DESC',
    'posts_per_page' => -1
    );
$templateList = new WP_Query( $args );
?>




<div class="wrap">
  <h1 class="wp-heading-inline">Configuración Delivery Lat</h1>
  <form action="" method="post">
    <?php wp_nonce_field("woo_del_lat_config"); ?>
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th>
            <label for="name">Llave de Google maps</label><br>
            <small>La llave de Google maps es necesaria para buscar las ubicaciones de las sedes.</small>
          </th>
          <td>
            <input type="text" name="googleKey" id="googleKey" value="<?php echo esc_attr($googleKey)?>" class="regular-text" required>
          </td>
        </tr>
        <tr>
          <th>
            <label for="firebaseApiKey">Llave web de Firebase</label><br>
            <small>apiKey del proyecto de Firebase usado para las notificaciones push en el navegador.</small>
          </th>
          <td>
            <input type="text" name="firebaseApiKey" id="firebaseApiKey" value="<?php echo esc_attr($firebaseApiKey)?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th>
            <label for="fcmServerKey">Llave de servidor de FCM</label><br>
            <small>Necesaria para enviar la notificación push a la sede cuando llega una orden.</small>
          </th>
          <td>
            <input type="password" name="fcmServerKey" id="fcmServerKey" value="<?php echo esc_attr($fcmServerKey)?>" class="regular-text" autocomplete="off">
          </td>
        </tr>
        <tr>
          <th>
            <label for="name">Correo electrónico de notificaciones</label><br>
            <small>Acá debes agregar el correo electrónico que se usará como 
            destinatario cada vez que llega una nueva orden.</small>
          </th>
          <td>
            <input type="text" name="notificationEmails" id="notificationEmails" value="<?php echo $notificationEmails?>" class="regular-text" required>
          </td>
        </tr>
        <tr>
        <th>
            <label>Notificación en el navegador</label>
        </th>
        <td>Activo? <input type="checkbox" name="activeNotification" id="activeNotification" <?php echo $activeNotification?"checked":""?>></td>
        </tr>
        <tr>
          <th>
            <label for="name">Plantilla de correo electrónico de notificación.</label><br>
          </th>
          <td>
            <select name="templateEmail" id="templateEmail" value="<?php echo $templateEmail; ?>">
        <?php if($templateList->have_posts()){ 
                while ( $templateList->have_posts() ) { $templateList->the_post(); ?>
                <option value="<?php echo get_the_ID(); ?>" <?php echo $templateEmail && $templateEmail == get_the_ID()? "selected":"";?>><?php echo the_title(); ?></option>
           <?php } } else{ ?>
                <option value="">No disponible</option>
        <?php } ?>
            </select>
          </td>
        </tr>
        <tr>
            <td colspan="2">
                <small>Tenga en cuenta que las plantillas deben de tener los siguientes valores reemplazables.</small><br>
                <small>##officeName: Se reemplazará por el nombre de la sucursal o sede a quien se realiza el pedido.</small><br>
                <small>##urlGoogleDestination: Se reemplazará por la url de destino del domicilio.</small><br>
                <small>##addressDestination: Se reemplazará por la dirección de destino.</small><br>
                <small>##urlOrden: Se reemplaza por la url de la orden en wordpress.</small><br>
            </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar">
    </p>
  </form>
</div>