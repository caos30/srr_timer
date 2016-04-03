<?php 

/*
 * internal function for this PHP file, to put in clear which is the location of the database, depending on the incoming data at $vars
 */
function _DB_fill_db($vars){
    global $config_backend;
    if (!isset($vars['db'])) 
        $vars['db'] = array();
    if (empty($vars['db']['db_filename']))
        $vars['db']['db_filename'] = 'timer.sqlite';
    if (empty($vars['db']['db_path']))
        $vars['db']['db_path'] = $config_backend['path'].'data';
    return $vars;
}

/*
 * required params: t (table String)
 * optional params: w (where Array), o (order field String), o2 (order direction String), 
 *                  l1 (first record Integer), l2 (last record Integer), k (field for associative array String)
 */
function DB_select($vars){
        if (empty($vars['t'])) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
	$ret = $db->SELECT($vars); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String)
 * optional params: o (order field String), o2 (order direction String), w (where Array),
 *                  nn (NotNull: true, include this param if you want to get an empty record if ID doesn't exist at this table) 
 */
function DB_get_first_record($vars){ 
        if (empty($vars['t'])) return false;
        $vars = _DB_fill_db($vars); 
        $db = new class_aSQLite($vars['db']);
        $vars['l1'] = 1;
        $vars['l2'] = 1;
        $vars['k'] = '';
        $vars['o'] = empty($vars['o']) ? '_id_' : $vars['o'];
        $vars['o2'] = empty($vars['o2']) ? 'ASC' : $vars['o2'];
	$cons = $db->SELECT($vars); 
	$db->CLOSE();
	unset($db);
	if (is_array($cons) and count($cons)>0)
            $ret = $cons[0]; 
        else if (isset($vars['nn']) && $vars['nn']===true)
            $ret = DB_get_empty_record($vars);
        else
            $ret = array();
        return $ret;
}


function DB_sort_array($arr,$k_order,$order='ASC',$limit_ini='',$limit_fin=''){ // permite ordenar según varios campos: $k_order='apellido nombre'
	$ret = array();
	$id_order = array();
	$ex = explode(' ',trim($k_order));
	$a_k_order = array(); 
	foreach ($ex as $ki){ //$ki = 'fec_reserva[ddmmyy' or $ki='nombre' 
		$a_k_order[] = explode('[',$ki);
	}
	if (count($arr)>0 and is_array($arr)){
		// read the content of the $k_order field 
		// and storing at the $id_order array 
		foreach ($arr as $k=>$ele){
			// build the key for sort 
 	 		$v=''; 
 	 		foreach ($a_k_order as $arr2){ // $arr=array(0=>'fec_reserva',1=>'ddmmyy') or $arr=array(0=>'nombre');
 	 			$v = trim($ele[$arr2[0]]); 
				if (isset($arr2[1])){
			 	 		if ($arr2[1]=='n'){
			 	 			$v = str_replace(',','.',$v);
			 	 			if (strpos($v,'.')===false){ 
			 	 				$v = substr('000000000000'.$v,-12); 
			 	 			}else{
			 	 				$ex=explode('.',$v);
			 	 				$v1 = substr('00000000'.$ex[0],-8); 
			 	 				$v2 = substr($ex[1].'0000',0,4);  
			 	 				$v=$v1.'.'.$v2;
			 	 			}
			 	 		}else if ($arr2[1]=='s'){
			 	 			$v = strtolower($v);
			 	 		}else if ($arr2[1]=='ddmmyy'){ // dd/mm/yy or dd-mm-yy 
			 	 			$v = substr($v,6,2).substr($v,3,2).substr($v,0,2);
			 	 		}else if ($arr2[1]=='ddmmyyyy'){ // dd/mm/yyyy or dd-mm-yyyy 
			 	 			$v = substr($v,6,4).substr($v,3,2).substr($v,0,2); 
			 	 		}else{
			 	 		}
			 	 }
			}

			// store at $id_order array for sorting later 
	 	 	$vp = 0; 
	 	 	while (isset($id_order[$v.'_('.$vp.')'])){ $vp = intval($vp)+1; }
	 	 	$id_order[$v.'_('.$vp.')'] = $k;
	 	}
	 	// sorting the array of values 
	 	ksort($id_order);

	 	// rebuild the array, yet ordered 
	 	$num = count($id_order);
	 	$ii=1;
	 	foreach ($id_order as $k){ 
	 		if ($order=='ASC')	$ret[$ii] = $arr[$k];
	 		else					$ret[($num-$ii)] = $arr[$k]; 
	 		$ii++;
	 	}
	 	// re-ksort if the order is DESC 
	 	if ($order!='ASC') ksort($ret);
	 	// limit if necesary
	 	if ($limit_ini!='' and $limit_fin!=''){
	 		$ini=intval($limit_ini);
	 		$fin=intval($limit_fin);
	 		$ii=1;
	 		$ret2=array();
	 		foreach ($ret as $k=>$ele){
	 			if ($ii>=$ini and $ii<=$fin) $ret2[$k]=$ele;
	 			$ii++;
	 		}
	 		unset($ret);
	 		$ret = $ret2;
	 		unset($ret2);
	 	}
	}
	
	return $ret;
}

/*
 * required params: t (table String), V (values Array)
 * optional params: w (where Array)
 */
function DB_update($vars){
        if (empty($vars['t']) || empty($vars['v'])) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
	$ret = $db->UPDATE($vars); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String), v (values Array), id (integer)
 */
function DB_update_by_ID($vars){
        if (empty($vars['t']) || empty($vars['v']) || !isset($vars['id'])) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
        if ($vars['id'] === 0) {
            $ret = $db->INSERT($vars); 
        }else{
            $vars['w'] = array('_id_'=>$vars['id']);
            $ret = $db->UPDATE($vars); 
        }
	$db->CLOSE();
        return $ret;
}
/*
 * required params: m (module String), q (queries array, each one must contain a valid DB_UPDATE $vars)
 */
function DB_multiple_update($vars){
        if (empty($vars['q']) || !is_array($vars['q']) || count($vars['q'])==0) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
	$ret = $db->MULTIPLE_UPDATE($vars['q']); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String), v (values Array), id (integer)
 */
function DB_insert($vars){
        if (empty($vars['t']) || empty($vars['v'])) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
        $ret = $db->INSERT($vars); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String), v (array, each one must contain a valid DB_INSERT $vars['v'])
 */
function DB_multiple_insert($vars){
        if (empty($vars['v']) || !is_array($vars['v']) || count($vars['v'])==0
                || empty($vars['t']) ) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
	$ret = $db->MULTIPLE_INSERT($vars); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String), w (where Array)
 */
function DB_delete($vars){
        if (empty($vars['t']) || empty($vars['w'])) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
        $ret = $db->DELETE($vars); 
	$db->CLOSE();
        return $ret;
}

/*
 * required params: t (table String), id (integer > 0)
 */
function DB_delete_by_ID($vars){
        if (empty($vars['t']) || empty($vars['id']) || intval($vars['id']) === 0) return false;
        $vars = _DB_fill_db($vars);
        $db = new class_aSQLite($vars['db']);
        $ret = $db->DELETE_BY_ID($vars); 
	$db->CLOSE();
        return $ret;
}

?>