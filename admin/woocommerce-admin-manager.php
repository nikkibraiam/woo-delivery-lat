<?php
    defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

    require_once 'services/schedule-service.php';
    require_once 'services/branch-office-service.php';

    $scheduleService = new ScheduleService();
    $schedules = $scheduleService->getList(true);
    $id = 'model-office';
    $office_name = 'Sucursal Nombre';
    if(isset($office["office_id"])){
        $id = 'office_'.$office['office_id'];
        $officeService = new BranchOfficeService();
        $officeDB = $officeService->get($office['office_id']);
        $office_name = $officeDB->name;
    }else{
        $index = -1;
    }
?>

<div id="<?php echo esc_attr($id); ?>" class="father" data-index="<?php echo (int)$index;?>">
    <table style="width:100%">
        <tr>
            <td colspan="3"><input class="office" name="offices[<?php echo (int)$index; ?>][office_id]" type="hidden" value="<?php echo esc_attr($office['office_id']); ?>"/><h3 id="title" style="text-align: center;"><?php echo esc_html($office_name); ?></h3></td>
            <td style="text-align: center;"><span class="dashicons dashicons-no-alt" onclick="jQuery(this).parents('.father').remove()"></span></td>
        </tr>
        <tr>
            <td>Precio <?php echo wc_help_tip("(Opcional) Este precio sobre-escribe el primario para la sucursal seleccionada."); ?></td>
            <td><input type="text" class="wc_input_decimal price" name="offices[<?php echo (int)$index; ?>][price]" value="<?php echo esc_attr($office['price']); ?>"/></td>
            <td>Cantidad <?php echo wc_help_tip("(Opcional) Cuando llegue a 0, se desactiva el producto."); ?></td>
            <td><input type="text" class="wc_input_decimal amount" name="offices[<?php echo (int)$index; ?>][amount]" value="<?php echo esc_attr($office['amount']); ?>"/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center"><h4>Horarios <?php echo wc_help_tip("(Opcional) Si selecciona un horario, este producto solo estará disponible en el horario seleccionado, de lo contrario está disponible para los horarios de la sucursal."); ?></h4></td>
            <td colspan="2">
                <select style="width: 60%;" onchange="addSchedule(this)">
                    <option value="">--Seleccione--</option>
                    <?php if($schedules){
                        foreach($schedules as $schedule){ ?>
                            <option value="<?php echo absint($schedule->id); ?>"><?php echo esc_html($schedule->name); ?></option>
                    <?php }
                    } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="schedules">
            <?php 
                if(isset($office["schedule"])){
                    foreach($office["schedule"] as $key => $schedule){ ?>
                        <div class="center schedule item-<?php echo absint($schedule);?>">
                            <input type="hidden" name="offices[<?php echo (int)$index; ?>][schedule][<?php echo $key; ?>]" value="<?php echo absint($schedule);?>">
                            <span><?php echo esc_html($scheduleService->get($schedule)->name) ?></span>
                            <span class="dashicons dashicons-no-alt" onclick="removeSchedule(this)"></span>
                        </div>
                    <?php }
                }
            ?>
            </td>
        </tr>
    </table>
</div>