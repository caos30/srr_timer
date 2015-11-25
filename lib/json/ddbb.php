<?php /**/ ?>
<?php 

function DB_get_first_record($tabla,$where=array()){ 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data','db_filename'=>'timer.sqlite'));
        return $database->FIRST_RECORD($tabla);
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

function DB_select($table,$where=array(),$k='_id_'){	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
        
	// operations 
	$cons = $database->GET_RECORDS_TABLE(trim($table),$where); 
	$ret = array();
	if (count($cons)>0 and is_array($cons)){
		 foreach ($cons as $ele){
		 	if ($k=='_id_'){
		 		$ret[$ele['_id_']] = $ele;
		 	}else if ($k==''){
		 		$ret[] = $ele;
		 	}else if (isset($ele[$k])){
		 		$kk = $ele[$k];
		 		$ret[$kk] = $ele;
		 	}
		 }
	}
	$database->CLOSE();
	unset($database); 
	return $ret;
}

function DB_search($table,$find=array(),$k='_id_'){	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
	// operations 
	$cons = $database->SEARCH_RECORDS_TABLE(trim($table),$find);  
	$ret = array();
	if (count($cons)>0 and is_array($cons)){
		 foreach ($cons as $ele){
		 	if ($k=='_id_'){
		 		$ret[$ele['_id_']] = $ele;
		 	}else if ($k==''){
		 		$ret[] = $ele;
		 	}else if (isset($ele[$k])){
		 		$kk = $ele[$k];
		 		$ret[$kk] = $ele;
		 	}
		 }
	}
	$database->CLOSE();
	unset($database); 
	return $ret;
}

function DB_update($table,$where_a=array(),$valores_a=array()){	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
	// operations 
	$ret = $database->UPDATE_RECORD(trim($table),$where_a,$valores_a);   
	$database->CLOSE();
	unset($database); 
	return $ret;
}

function DB_insert($table,$valores_a=array()){	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
	// operations 
	$id = $database->INSERT_RECORD(trim($table),$valores_a);   
	$database->CLOSE();
	unset($database); 
	return $id;  
}

function DB_insert_multiple($table,$valores_a){
	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
	// operations 
	if (count($valores_a)>0 and is_array($valores_a)){
		foreach ($valores_a as $valores_aa)
			$cons = $database->INSERT_RECORD(trim($table),$valores_aa); 
	}  
	$database->CLOSE();
	unset($database); 
	return $ret;
}

function DB_delete($table,$where_a=array()){	// connection with the database 
        // connection with the database 
	include_once ('../db/class_aSQLite.php'); 
        $database = new class_aSQLite(array('db_path'=>'../../data', 'db_filename'=>'timer.sqlite'));
	// operations 
	$cons = $database->DELETE_RECORD(trim($table),$where_a);   
	$database->CLOSE();
	unset($database); 
	return $cons;
}




?>