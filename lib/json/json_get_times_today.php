<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data
    // == check if there are change of day
        if (isset($_GET['first_load']) && $_GET['first_load']=='1'){
            f_check_change_of_day();
            f_sum_total_times();
        }
            
    // == clean "null" fields
        $a_projects = DB_select(array('t'=>'projects'));
        if (count($a_projects)>0){
                foreach ($a_projects as $ip=>$arr){
                    foreach ($arr as $f=>$v){
                        if (!$v || $v==null) $a_projects[$ip][$f] = '';
                    }
                }
        }

    $ret=array();
    $ret['a_projects'] = $a_projects; 
    $ret['script'] = ""; 
    $ret['current_date'] = f_getToday(true); 

    return $ret;

?>
