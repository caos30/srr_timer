<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    // == sum for all projects ;) 
        $a_projects = DB_select('projects',array(),'_id_');
        if (count($a_projects)>0){
                //foreach ($a_projects as )
        }

    // == clean "null" fields
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
