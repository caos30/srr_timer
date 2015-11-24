<?php 

ini_set('display_errors', 'On'); error_reporting(E_ALL);

include_once("functions.php");

$json_scripts = f_get_json_scripts();

if (empty($_GET['op']) || !in_array($_GET['op'], $json_scripts)) return json_encode (array('ok'=>'0'));

$ret = include_once('json_'.$_GET['op'].'.php');

echo json_encode($ret + array('ok'=>1));

return; 

?>
