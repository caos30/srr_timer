<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data
    global $texts;

    f_load_texts();

    $ret=array();
    $ret['texts'] = $texts; 

    return $ret;

?>