<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    if (!empty($_GET['id_project']) && intval($_GET['id_project'])>0){
        $id_project = intval($_GET['id_project']);
        $cons1 = DB_delete_by_ID(array('t'=>'projects', 'id'=>$id_project));
        $cons2 = DB_delete(array('t'=>'times', 'w'=>array('id_project'=>$id_project)));
        $ret = array('ok'=>'1','cons1'=>$cons1,'cons2'=>$cons2,'id_project'=>$id_project);
    }else{
        $ret = array('ok'=>'0','id_project'=>$id_project);
    }

    return $ret;

?>
