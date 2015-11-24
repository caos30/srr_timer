<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    $name = base64_decode($_GET['name']);
    $path = base64_decode($_GET['path']);

    f_set_project_name($path,$name); 

    $ret=array();
    $ret['name'] = $name; 
    $ret['path'] = $path;  
    $ret['script'] = ""; 

    return $ret; 

?>