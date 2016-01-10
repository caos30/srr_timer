<?php 

ini_set('display_errors', 'On'); error_reporting(E_ALL);

 // = this $config_backend is for backend issues, meanwhile the array $config is for the frontend UI ;)
    global $config_backend;
    $config_backend = array(
                    'path' => dirname(__FILE__).'/../../',
                    );
    
include_once ('../db/class_aSQLite.php');
include_once ('ddbb.php');
include_once ('functions.php');

$json_scripts = f_get_json_scripts();

if (empty($_GET['op']) || !in_array($_GET['op'], $json_scripts)) return json_encode (array('ok'=>'0'));

$ret = include_once('json_'.$_GET['op'].'.php');

echo json_encode($ret + array('ok'=>1));

return; 

?>
