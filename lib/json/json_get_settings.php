<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    $settings = DB_get_first_record('config');

    $settings['languages'] = f_get_language_list();

    return array('settings'=>$settings);

?>