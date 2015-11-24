<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data
    $id = trim($_GET['id']);

    $a_project = f_get_times_project($id); 

    $ret=array();
    $ret['a_project'] = $a_project; 
    $ret['script'] = ""; 

    return $ret;

?>