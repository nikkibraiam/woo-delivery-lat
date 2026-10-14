<?php 

class ScheduleService{

    private $tableName = "";
    private $tableNameDet = "";
    private $wpdb;

    function __construct(){
        global $wpdb;
        $this->tableName = $wpdb->prefix . "wdlat_schedules";
        $this->tableNameDet = $wpdb->prefix . "wdlat_schedules_details";
        $this->wpdb = $wpdb;
    }

    function getList($active = false){
        $tableName = $this->tableName;
        $sql = "SELECT * FROM $tableName";
        if($active){
            $sql = $sql . " WHERE state = 1";
        }
        $res = $this->wpdb->get_results($sql);
        return $res;
    }

    function get($id, $include_det = false){
        $tableName = $this->tableName;
        $res = $this->wpdb->get_results("SELECT * FROM $tableName WHERE id = $id");
        if(sizeof($res) > 0){
            if($include_det){
                $res[0]->details = $this->getDetails($res[0]->id);
            }
            return $res[0];
        }
        return null;
    }

    function getDetails($shedule_id){
        $tableName = $this->tableNameDet;
        $res = $this->wpdb->get_results("SELECT * FROM $tableName WHERE schedule_id = $shedule_id");
        return $res;
    }

    
    function save($schedule){
        $tableName = $this->tableName;
        if($schedule["id"] != null && $schedule["id"] > 0){
            $this->wpdb->query("UPDATE $tableName SET name='".$schedule["name"]."', description='".$schedule["description"]."', state='".$schedule["state"]."' WHERE id=".$schedule["id"]);
        }else{
            $res = $this->wpdb->query("INSERT INTO $tableName (name, description) VALUES('".$schedule["name"]."', '".$schedule["description"]."')");
            $schedule["id"] = $this->wpdb->insert_id;
        }

        $oldDetails = $this->getDetails($schedule["id"]);
        $ids = array_map(function($detail){return $detail["id"];}, $schedule["details"]);
        if(isset($oldDetails)){
            foreach($oldDetails as $oldDetail){
                if(!in_array($oldDetail->id, $ids)){
                    $this->deleteDetail($oldDetail->id);
                }        
            }
        }
        
        if(isset($schedule["details"])){
            foreach($schedule["details"] as $detail){
                $detail["schedule_id"] = $schedule["id"];
                $this->saveDetail($detail);
            }
        }
        if(isset($this->wpdb)){
            print_r($this->wpdb->last_error);
        }
        return true;
    }

    function saveDetail($detail){
        $tableName = $this->tableNameDet;
        if($detail["id"] != null && $detail["id"] > 0){
            $this->wpdb->query("UPDATE $tableName SET schedule_id='".$detail["schedule_id"]."', week_day='".$detail["week_day"]."', start_hour='".$detail["start_hour"]."', end_hour='".$detail["end_hour"]."', state='".$detail["state"]."' WHERE id=".$detail["id"]);
        }else{
            $res = $this->wpdb->query("INSERT INTO $tableName (schedule_id, week_day, start_hour, end_hour) VALUES('".$detail["schedule_id"]."', '".$detail["week_day"]."', '".$detail["start_hour"]."', '".$detail["end_hour"]."')");
        }
        if(isset($this->wpdb)){
            print_r($this->wpdb->last_error);
        }
        return true;
    }

    

    function deleteDetail($id){
        $tableName = $this->tableNameDet;
        $this->wpdb->query("DELETE FROM $tableName WHERE id = $id");
        return true;
    }

    function delete($id){
        $tableName = $this->tableName;
        $tableNameChild = $this->wpdb->prefix . "wdlat_branch_schedule";
        $this->wpdb->query("DELETE FROM $tableNameChild WHERE schedule_id = $id");
        $this->wpdb->query("DELETE FROM $tableName WHERE id = $id");
        return true;
    }

}

?>