<?php 

class BranchOfficeService{

    private $tableName = "";
    private $tableNameChild = "";
    private $wpdb;

    function __construct(){
        global $wpdb;
        $this->tableName = $wpdb->prefix . "wdlat_branch_offices";
        $this->tableNameChild = $wpdb->prefix . "wdlat_branch_schedule";
        $this->wpdb = $wpdb;
    }

    function getList(){
        $tableName = $this->tableName;
        $res = $this->wpdb->get_results("SELECT * FROM $tableName");
        return $res;
    }

    function get($id, $includeSchedule = false){
        $tableName = $this->tableName;
        $res = $this->wpdb->get_results("SELECT * FROM $tableName WHERE id = $id");
        if(sizeof($res) > 0){
            if($includeSchedule){
                $res[0]->schedules = $this->getSchedule($res[0]->id);
            }
            return $res[0];
        }
        return null;
    }

    function getSchedule($office_id){
        $tableNameChild = $this->tableNameChild;
        $tableSchedule = $this->wpdb->prefix . "wdlat_schedules";
        $res = $this->wpdb->get_results("SELECT os.id as idmain, sch.* FROM $tableNameChild as os 
                INNER JOIN $tableSchedule as sch  ON os.schedule_id = sch.id  
                WHERE os.office_id = $office_id");
        return $res;
    }
    
    function save($branchOffice){
        $tableName = $this->tableName;
        if($branchOffice["id"] != null && $branchOffice["id"] > 0){
            $this->wpdb->query("UPDATE $tableName SET name='".$branchOffice["name"]."', description='".$branchOffice["description"]."', phone='".$branchOffice["phone"]."', address='".$branchOffice["address"]."', latitude='".$branchOffice["latitude"]."', longitude='".$branchOffice["longitude"]."', kmlfile='".$branchOffice["kmlfile"]."', state='".$branchOffice["state"]."' WHERE id=".$branchOffice["id"]);
        }else{
            $res = $this->wpdb->query("INSERT INTO $tableName (name, description, phone, address, latitude, longitude, kmlfile) VALUES('".$branchOffice["name"]."', '".$branchOffice["description"]."', '".$branchOffice["phone"]."', '".$branchOffice["address"]."', '".$branchOffice["latitude"]."', '".$branchOffice["longitude"]."', '".$branchOffice["kmlfile"]."')");
            $branchOffice["id"] = $this->wpdb->insert_id;
        }

        if(isset($branchOffice["schedules"])){
            $oldSchedules = $this->getSchedule($branchOffice["id"]);
            $ids = array_map(function($schedule){return $schedule["id"];}, $branchOffice["schedules"]);
            if(isset($oldSchedules)){
                foreach($oldSchedules as $oldSchedule){
                    if(!in_array($oldSchedule->idmain, $ids)){
                        $this->deleteSchedule($oldSchedule->idmain);
                    }
                }
            }

            if(isset($branchOffice["schedules"])){
                foreach($branchOffice["schedules"] as $schedule){
                    $schedule["office_id"] = $branchOffice["id"];
                    $this->saveSchedule($schedule);
                }
            }
        }
        if(isset($this->wpdb)){
            print_r($this->wpdb->last_error);
        }
        return true;
    }

    function deleteSchedule($schedule_office_id){
        $tableName = $this->tableNameChild;
        $this->wpdb->query("DELETE FROM $tableName WHERE id = $schedule_office_id");
        return true;
    }

    function saveSchedule($office_schedule){
        $tableName = $this->tableNameChild;
        if($office_schedule["id"] != null && $office_schedule["id"] > 0){
            $this->wpdb->query("UPDATE $tableName SET schedule_id='".$office_schedule["schedule_id"]."', office_id='".$office_schedule["office_id"]."' WHERE id=".$office_schedule["id"]);
        }else{
            $res = $this->wpdb->query("INSERT INTO $tableName (schedule_id, office_id) VALUES ('".$office_schedule["schedule_id"]."', '".$office_schedule["office_id"]."')");
        }
        if(isset($this->wpdb)){
            print_r($this->wpdb->last_error);
        }
        return true;
    }

    function delete($id){
        $tableName = $this->tableName;
        $tableNameChild = $this->tableNameChild;
        $this->wpdb->query("DELETE FROM $tableNameChild where office_id = $id");
        $this->wpdb->query("DELETE FROM $tableName WHERE id = $id");
        return true;
    }

}

?>