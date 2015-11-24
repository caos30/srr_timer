<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data
    $data = base64_decode($_GET['data']);
    // this is a temporal 'patch' because the javascript encode64 function doesn't match exactly with the decode64 function ¿?  
    $data = str_replace("%5B%3A%5D","[:]",$data);
    $data = str_replace("%5B%7C%5D","[|]",$data);
    $data = str_replace("%5B%3C%3E%5D","[<>]",$data);

    // 	_id_ [:] 5 [|] title [:] st.com. [|]  b_active [:] 0 [|] today [:] 27 [|] all [:] 1233062 [<>] _id_ [:] 6 [|] title [:] ww.co.uk [|] today [:] 0 [|] all [:] 133954   

    $a_projects_data = array();
    $ex1 = explode("[<>]",$data);
    if (count($ex1)>0){
    foreach($ex1 as $ele1){
            $a_project = array();
            $ex2=explode("[|]",$ele1);
            if (count($ex2)>0){
                    foreach($ex2 as $ele2){
                            $ex3=explode("[:]",$ele2); 
                            if (count($ex3)==2){
                                    if (trim($ex3[0])!="" and trim($ex3[1])!=""){
                                            if (trim($ex3[0])=='title')
                                                $a_project[trim($ex3[0])] = trim(urldecode($ex3[1]));
                                            else
                                                $a_project[trim($ex3[0])] = trim($ex3[1]);
                                    }
                            }
                    }
            }
            if (isset($a_project['_id_']))
            $a_projects_data[intval($a_project['_id_'])] = $a_project;
            unset($a_project);
    }
    }
    //echo var_export($a_projects_data,true);return;
    $b_updated_today_times = f_save_projects_data($a_projects_data);

    //f_set_project_name($path,$name); 

    $ret=array();
    $ret['updated_today_times'] = $b_updated_today_times;   
    $ret['script'] = ""; 
    $ret['current_date'] = f_getToday(true); 

    return $ret; 

?>
