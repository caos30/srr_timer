<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    $data = base64_decode($_GET['data']);
    // this is a temporal 'patch' because the javascript encode64 function doesn't match exactly with the decode64 function ¿?  
    $data = str_replace("%5B%3A%5D","[:]",$data);
    $data = str_replace("%5B%7C%5D","[|]",$data);
    $data = str_replace("%5B%3C%3E%5D","[<>]",$data);

    // 	_id_ [:] 1 [|] username [:] sergi [|]  .... [|] show [:] active

    $data = f_explode($data);

    if (!empty($data['show'])){
        $cons = DB_update('config',array('_id_'=>$data['_id_']),$data);
        $ret = array('ok'=>'1','cons'=>$cons,'data'=>$data);
    }else{
        $ret = array('ok'=>'0','data'=>$data);
    }

    return $ret;

?>
