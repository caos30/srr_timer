<?php 

// == we want to deny the direct access to this file
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) return array('ok'=>'0');

// == process data

    if (isset($_GET['id_project_from']) && intval($_GET['id_project_from'])>0
            && isset($_GET['id_project_to']) && intval($_GET['id_project_to'])>0
            && intval($_GET['id_project_from'])!=intval($_GET['id_project_to'])){
        $id_project_from = intval($_GET['id_project_from']);
        $id_project_to = intval($_GET['id_project_to']);
        // == load times from both projects
            $times_from = DB_select(array('t'=>'times', 'w'=>array(array('id_project','=',$id_project_from)), 'k'=>'d_yyyymmdd'));
            $times_to = DB_select(array('t'=>'times', 'w'=>array(array('id_project','=',$id_project_to)), 'k'=>'d_yyyymmdd'));
        // == merge both arrays: 
            // == compare and generate an array of times to be edited and an array of times to be added
                $to_add = array();
                $to_edit = array();
                if (is_array($times_from) && count($times_from)>0){
                    foreach($times_from as $date=>$arr){
                        $arr['id_project'] = $id_project_to;
                        unset($arr['_id_']);
                        if (!isset($times_to[$date])){
                            $to_add[] = $arr;
                        }else{
                            $arr['n_time'] = intval($times_to[$date]['n_time']) + intval($arr['n_time']);
                            $to_edit[] = array('t'=>'times', 'w'=>array(array('_id_','=',$times_to[$date]['_id_'])), 'v'=>$arr);
                        }
                    }
                }
            // == save changes
                //echo '<h3>$to_add</h3>'._var_export($to_add);                echo '<h3>$to_edit</h3>'._var_export($to_edit);                die();
                if (count($to_add)>0){
                    DB_multiple_insert(array('t'=>'times', 'v'=>$to_add));
                }
                if (count($to_edit)>0){
                    DB_multiple_update(array('q'=>$to_edit));
                }
            
        // == merge 'projects' records
            $from_project = DB_get_first_record(array('t'=>'projects', 'w'=>array(array('_id_','=',$id_project_from))));
            $to_project = DB_get_first_record(array('t'=>'projects', 'w'=>array(array('_id_','=',$id_project_to))));
            $v = array(
                't_creation' => min(array($from_project['t_creation'],$to_project['t_creation'])),
                'n_today_time' => intval($from_project['n_today_time']) + intval($to_project['n_today_time']),
                'n_init_time' => intval($from_project['n_init_time']) + intval($to_project['n_init_time']),
                'd_first' => min(array($from_project['d_first'],$to_project['d_first'])),
                'd_last' => max(array($from_project['d_last'],$to_project['d_last'])),
            );
            DB_update_by_ID(array('t'=>'projects', 'id'=>$id_project_to, 'v'=>$v));
                
        // == delete the id_project_from times and project record
            DB_delete(array('t'=>'times', 'w'=>array(array('id_project','=',$id_project_from))));
            DB_delete_by_ID(array('t'=>'projects', 'id'=>$id_project_from));
            
        // == sum again the total of time for the id_project_to project
            f_sum_total_times();
            
        $ret = array('ok'=>'1');
    }else{
        $ret = array('ok'=>'0');
    }

    return $ret;

?>
