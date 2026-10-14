<?php
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

<div id="<?php echo $id; ?>" class="father" data-index="<?php echo $index;?>">
    <table style="width:100%">
        <tr>
            <td colspan="3"><input class="office" name="offices[<?php echo $index; ?>][office_id]" type="hidden" value="<?php echo $office['office_id']; ?>"/><h3 id="title" style="text-align: center;"><?php echo $office_name; ?></h3></td>
            <td style="text-align: center;"><span class="dashicons dashicons-no-alt" onclick="jQuery(this).parents('.father').remove()"></span></td>
        </tr>
        <tr>
            <td>Precio <?php echo wc_help_tip("(Opcional) Este precio sobre-escribe el primario para la sucursal seleccionada."); ?></td>
            <td><input type="text" class="wc_input_decimal price" name="offices[<?php echo $index; ?>][price]" value="<?php echo $office['price']; ?>"/></td>
            <td>Cantidad <?php echo wc_help_tip("(Opcional) Cuando llegue a 0, se desactiva el producto."); ?></td>
            <td><input type="text" class="wc_input_decimal amount" name="offices[<?php echo $index; ?>][amount]" value="<?php echo $office['amount']; ?>"/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center"><h4>Horarios <?php echo wc_help_tip("(Opcional) Si selecciona un horario, este producto solo estará disponible en el horario seleccionado, de lo contrario está disponible para los horarios de la sucursal."); ?></h4></td>
            <td colspan="2">
                <select style="width: 60%;" onchange="addSchedule(this)">
                    <option value="">--Seleccione--</option>
                    <?php if($schedules){
                        foreach($schedules as $schedule){ ?>
                            <option value="<?php echo $schedule->id; ?>"><?php echo $schedule->name; ?></option>
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
                        <div class="center schedule item-<?php echo $schedule;?>">
                            <input type="hidden" name="offices[<?php echo $index; ?>][schedule][<?php echo $key; ?>]" value="<?php echo $schedule;?>">
                            <span><?php echo $scheduleService->get($schedule)->name ?></span>
                            <span class="dashicons dashicons-no-alt" onclick="removeSchedule(this)"></span>
                        </div>
                    <?php }
                }
            ?>
            </td>
        </tr>
    </table>
</div>