<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    $title = urldecode(base64_decode($_GET['title']));

    $id = f_add_project($title);

    $ret=array();
    $ret['title'] = $title; 
    $ret['_id_'] = $id; 
    $ret['script'] = ""; 

    return $ret; 

?>