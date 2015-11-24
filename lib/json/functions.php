<?php /**/ ?>
<?php

require_once('ddbb.php');
global $config;

$config = DB_get_first_record('config');
f_get_language_list();
function f_get_language_list(){
    $scan = scandir('../languages');
    $languages = array();
    foreach($scan as $filename){
        $ex = explode('.',$filename);
        if (strlen($ex[0])==2) $languages[] = $ex[0];
    }
    return $languages;
}

function f_get_json_scripts(){
    $json_scripts = array();
    $files = scandir(dirname(__FILE__));
    foreach($files as $filename){
        if (preg_match('/^json_(.*)\.php$/', $filename, $matches)) $json_scripts[] = $matches[1];
    }
    return $json_scripts;
}

function f_save_projects_data($a_projects_data){
	global $config;
	$a_projects = DB_select('projects',array(),'_id_');
	// check if it's necesary to update the data about an existing project in the database 
	$b_exists_times_changes = 0; 
	$b_exists_name_changes = 0; 
	if (count($a_projects_data)>0){
		foreach ($a_projects_data as $k=>$a_project){
			$id=$a_project['_id_']; 
			if (isset($a_projects[$id])){
				// the project exists after and before 
				// (the most of the times! 99.99999%)
				if ( intval($a_projects[$id]['n_total_time']) != intval($a_project['all'])  
						or trim($a_projects[$id]['title']) != trim($a_project['title'])
						or intval($a_projects[$id]['n_today_time']) != intval($a_project['today'])  
						or (isset($a_project['b_active']) && trim($a_projects[$id]['b_active']) != trim($a_project['b_active']) ) 
						) 
				DB_update('projects',array(array('_id_','=',$id)),array('title'=>$a_project['title'],'b_active'=>$a_project['b_active'],'n_today_time'=>$a_project['today'],'n_total_time'=>$a_project['all']));
			}else{
				DB_insert('projects',array('title'=>$a_project['title'],'b_active'=>$a_project['b_active'],'n_today_time'=>$a_project['today'],'n_total_time'=>$a_project['all'],
										'n_times'=>0,'d_first'=>date('Ymd'),'d_last'=>date('Ymd'),'t_creation'=>time()));
			}
		
		}
	}
	
	// check if there is a change of day
	 	$b_updated_today_times = f_check_change_of_day();
	
	return $b_updated_today_times;
}

function f_check_change_of_day(){
	global $config;
	$b_updated_today_times = 0;
	// check if it's necesary change of day!
		$today = f_getToday();
		// check if there are change of day
		if (is_null($config['today']) || empty($config['today'])){	
                    $config['today'] = $today; 
                    DB_update('config',array(),array('today'=>$today));
                }else if ($config['today']!=$today){	
                    $b_updated_today_times =  f_pass_today_time_to_times_table();
                    $config['today'] = $today; 
                    DB_update('config',array(),array('today'=>$today));
		}
	return $b_updated_today_times;
}

function f_getToday($formatted=false){
	global $config, $texts;
	// time in seconds now 
		$now = time() // time in seconds from January 1 1970 00:00:00 GMT ... AT THE SERVER!!!
                        + intval($config['n_hours_difference'])*3600  // append (or quit) X hours of time, depending on the num of difference hours between user and server 
                        - intval($config['n_hour_start_day'])*3600 ; // quit Y hours of time, depending on the hour of start/end the day of work for the user ;) a good idea is 5.00 AM 
	// "psychological date" now 
		if ($formatted){
                    f_load_texts();
                    $today = date('N d n Y',$now);
                    $a_today = explode(' ',$today);
                    $a_today[0] = $texts['_DAY_'.$a_today[0]];
                    $a_today[2] = $texts['_MONTH_'.$a_today[2]];
                    $today = implode(' ',$a_today);
                }else{
                    $today = date('Ymd',$now);
                }
	return $today;
}

function f_pass_today_time_to_times_table(){
	global $config;
	$a_projects = DB_select('projects',array(),'_id_');
	$b_exists_changes = 0;
	$Ymd = $config['today']; // YYYYmmdd
	$t_today = mktime(1,1,1,substr($Ymd,4,2),substr($Ymd,-2),substr($Ymd,0,4));
	$wday=date('N',$t_today);
	if (count($a_projects)>0){
		foreach($a_projects as $id=>$a_project){
			if (intval($a_project['n_today_time'])>0){
				DB_insert('times',array('id_project'=>$id,'d_yyyymmdd'=>$Ymd,'n_week_day'=>$wday,'n_time'=>intval($a_project['n_today_time'])));
				$n_total_time = intval($a_project['n_total_time']) + intval($a_project['n_today_time']); 
				$n_times = intval($a_project['n_times'])+1;
				DB_update('projects',array(array('_id_','=',$id)),array('n_today_time'=>0,'n_total_time'=>$n_total_time,'n_times'=>$n_times,'d_last'=>$Ymd));
				$b_exists_changes = 1; 
			} 
		}
	}
	return $b_exists_changes;
}

function f_set_project_name($id,$title){
	if (trim($title)!="" and intval($id)>0){
		DB_update('projects',array(array('_id_','=',$id)),array('title'=>$title));
	}
	return;
}

function f_add_project($title){
	if (trim($title)!=""){
            return DB_insert('projects',array(  'title'=>$title,
                                                'b_active'=>'1',
                                                'n_today_time'=>'0',
                                                'n_total_time'=>'0',
                                                'n_times'=>'0',
                                                'd_first'=>date('Ymd'),
                                                'd_last'=>date('Ymd'),
                                                't_creation'=>time())
                    );
	}
	return 0;
}

function f_get_times_project($id){
	$a_project = array();
	$a_project['_id_'] = $id; 
	$a_project['today_time'] = 0;
	$a_project['total_time'] = 0;
	$a_project['max_time_day'] = 0;
	$a_project['max_time_month'] = 0;
	$a_project['max_time_year'] = 0;
	$a_project['num_times'] = 0;
	$a_project['first_date'] = '';
	$a_project['last_date'] = '';
	$a_project['times'] = array();
	$a_project['months'] = array();
	$a_project['years'] = array();
	$last_time = 0;
	$a_times = DB_select('times',array(array('id_project','=',$id))); 
	if (count($a_times)>0 and is_array($a_times)){
		foreach ($a_times as $time){ 			
			$monthk = substr($time['d_yyyymmdd'],0,6);
			$yeark = substr($time['d_yyyymmdd'],0,4);
			$n_time = intval($time['n_time']);
			$n_date = intval($time['d_yyyymmdd']);
			if (!isset($a_project['months'][$monthk])){
				$a_project['months'][$monthk] = array('d'=>$monthk, 't'=>0);
			}
			if (!isset($a_project['years'][$yeark])){
				$a_project['years'][$yeark] = array('d'=>$yeark, 't'=>0);
			}
			$a_project['months'][$monthk]['t'] += $n_time;
			$a_project['years'][$yeark]['t'] += $n_time;
			if ($a_project['months'][$monthk]['t']>$a_project['max_time_month']) $a_project['max_time_month'] = $a_project['months'][$monthk]['t'];
			if ($a_project['years'][$yeark]['t']>$a_project['max_time_year']) $a_project['max_time_year'] = $a_project['years'][$yeark]['t'];
			$a_project['times'][]=array('d'=>$n_date,'t'=>$n_time);
			if ($n_time>$a_project['max_time_day']) $a_project['max_time_day'] = $n_time;
			$a_project['num_times']++;
			$a_project['total_time'] += $n_time;
			if ($a_project['first_date']=='') $a_project['first_date'] = $n_date;
			$a_project['last_date'] = $n_date;
			$last_time = $n_time; 
		}
		if ($last_time>0 and $a_project['last_date']==date('Ymd'))
		$a_project['today_time'] = $last_time;
	}
	unset($a_times);
	return $a_project;
} 

function _var_export($arr){
	$html = "\n<div style='margin-left:100px;'>";
	if (is_array($arr)){
		foreach ($arr as $k=>$ele) 
			$html .= "\n<div style='float:left;'><b>$k <span style='color:#822;'>-></span> </b></div>"
					  ."\n<div style='border:1px #ddd solid;'>"._var_export($ele)."</div>";
	}else{
		$html .= ($arr==NULL)? "&nbsp;":$arr;
	} 
	$html .= "</div>";
	return $html;
}

function _ext($file_name){
	$ex = explode(".",$file_name);
	return $ex[(count($ex)-1)];
}

function f_explode($s,$s1='[|]',$s2='[:]'){
	
	$ex1 = explode($s1,$s);
	$ret=array();
	if (count($ex1>0)){
		foreach ($ex1 as $ex1s){
			$ex2 = explode($s2,$ex1s);
			if (count($ex2)==2) $ret[trim($ex2[0])]=trim($ex2[1]);
		}
	}
	return $ret;
}

function f_implode($a,$s1='[|]',$s2='[:]'){
	$ret='';
	if (count($a)>0){
		foreach ($a as $k=>$v){
			if (trim($k)!=''){
				if ($ret!='') $ret.=$s1;
				$ret.=$k.$s2.$v;
			}
		}
	}
	return $ret;
}

function f_load_texts(){
    $lang_code = !empty($_GET['lang']) ? $_GET['lang'] : 'en' ;
    if (!file_exists('../languages/'.$lang_code.'.php')) $lang_code = 'en';
    require_once('../languages/'.$lang_code.'.php');
    return;
}

/*
function f_build_allprojects(){
	$projects = array();
	$tree = f_build_tree('../data');
	foreach ($tree as $path){
		if (_ext($path)=="task"){
			$html = "";
			$ele = array();
			$ele['path'] = $path; 
			$ele['today_time'] = 0;
			$ele['total_time'] = 0;
			$ele['num_times'] = 0;
			$ele['first_date'] = '';
			$ele['last_date'] = '';
			$lines = f_read_file('..'.$path);
			// read the lines 
			foreach($lines as $lin){
				// search metadata 
				$ex = explode(":",$lin);
				if (count($ex)>1){
					 if (strtolower($ex[0])=='project') $ele['project'] = trim($ex[1]); 
					 if (strtolower($ex[0])=='name') $ele['name'] = trim($ex[1]); 
					 if (strtolower($ex[0])=='created') $ele['created'] = intval($ex[1]); 
				}else{
				// count times
					$ex2 = explode(" ",$lin);
					if (count($ex2)==2 and intval($ex2[0])>0 and intval($ex2[1])>0 ){
						$ele['num_times']++;
						$ele['total_time'] += intval($ex2[1]);
						if ($ele['first_date']=='') $ele['first_date'] = trim($ex2[0]);
						$ele['last_date'] = trim($ex2[0]);
					} 
				} 
			}
			unset ($lines);
			if (isset($ele['name'])){
				$projects[] = $ele;
				unset($ele);
			}
		}
	}
	unset($tree);

	// save the tree in the all.projects file
	if (count($projects)>0) f_rewrite_allprojects_file($projects); 

	return $projects;
}
*/

?>
