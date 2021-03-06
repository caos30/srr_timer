<?php 
		
# you need to have installed the PDO SQLite driver installed in your server
# for ubuntu is easy to install: sudo apt-get install php5-sqlite

    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

// = = = = =  ini :: C O N F I G   = = = = 
    $config = array();
    $config['username'] = 'admin';
    $config['passwd'] = '1234';
    $config['page_title'] = 'PHP aSQLite - opensource license';
    $config['default_records_per_page'] = 50;
    $config['db_path'] = '../';
    $config['db_filename'] = 'barllo.sqlite';
    $config['cookie_ctime_def'] = time()+1.5*24*60*60; // 1.5 days 
    $config['stats_limit_per_field'] = 200;
//  = = = = =  end :: C O N F I G   = = = =  

// == for avoid duplicated cookie names, for example, between different apps on the same domain name
        $c_ = md5(dirname(__FILE__)).'_'; 

// == location of the database file
        /*
         * we could use this ADMIN application for manage whatever database using this URL
         * 
         *  admin/index.php?db_path=../&db_filename=barllo.sqlite
         */
    // == path
        if (!empty($_POST['db_path'])){
            $config['db_path'] = urldecode(trim($_POST['db_path']));
        }else if (!empty($_GET['db_path'])){
            $config['db_path'] = urldecode(trim($_GET['db_path']));
        }else if (isset($_COOKIE[$c_.'cookie_db_path'])){
            $config['db_path'] = $_COOKIE[$c_.'cookie_db_path'];
        }
        setcookie( $c_.'cookie_db_path', $config['db_path'] ,0);
    // == database filename
        if (!empty($_POST['db_filename'])){
            $config['db_filename'] = urldecode(trim($_POST['db_filename']));
        }else if (!empty($_GET['db_filename'])){
            $config['db_filename'] = urldecode(trim($_GET['db_filename']));
        }else if (isset($_COOKIE[$c_.'cookie_db_filename'])){
            $config['db_filename'] = $_COOKIE[$c_.'cookie_db_filename'];
        }
        setcookie( $c_.'cookie_db_filename', $config['db_filename'] ,0);

    // == records per page
        if (!empty($_POST['table_name']))
            $table_name = $_POST['table_name'];
        else if (!empty($_GET['table_name']))
            $table_name = $_GET['table_name'];
        else if (!empty($_REQUEST['table_name']))
            $table_name = $_REQUEST['table_name'];
        
        if (!empty($_POST['records_per_page']))
            $records_per_page = intval($_POST['records_per_page']);
        else if (!empty($_GET['records_per_page']))
            $records_per_page = intval($_GET['records_per_page']);
        else if (!empty($_REQUEST['records_per_page']))
            $records_per_page = intval($_REQUEST['records_per_page']);
        else if (!empty($table_name) && isset($_COOKIE[$c_.'cookie_tb_'.$table_name.'_records_per_page']))
            $records_per_page = intval($_COOKIE[$c_.'cookie_tb_'.$table_name.'_records_per_page']);
        else
            $records_per_page = 0;
        
        if (!empty($table_name) && $records_per_page > 0){
            $config['records_per_page'] = $records_per_page;
            setcookie( $c_.'cookie_tb_'.$table_name.'_records_per_page', $records_per_page ,0);
        }else if (!empty($table_name)){
            $config['records_per_page'] = $config['default_records_per_page'];
        }
            
    // == search variables
        if (!empty($_REQUEST['table_name']) && isset($_REQUEST['search_query'])){
            if (isset($_REQUEST['search_query']) && $_REQUEST['search_query']!=''){
                $config['search_query'] = $_REQUEST['search_query'];
                $config['search_field'] = $_REQUEST['search_field'];
                $config['search_regexp'] = (isset($_REQUEST['search_regexp']) && $_REQUEST['search_regexp']=='on') ? 'on' : 'off';
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_query', $config['search_query'] ,0);
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_field', $config['search_field'] ,0);
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_regexp', $config['search_regexp'] ,0);
            }else{
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_query', '' ,-10);
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_field', '' ,-10);
                setcookie( $c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_regexp', '' ,-10);
            }
        }else if (isset($_REQUEST['table_name']) && !empty($_COOKIE[$c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_query'])){
            $config['search_query'] = $_COOKIE[$c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_query'];
            $config['search_field'] = $_COOKIE[$c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_field'];
            $config['search_regexp'] = $_COOKIE[$c_.'cookie_tb_'.$_REQUEST['table_name'].'_search_regexp'];
        }
        
    // == save order if choosed one
        if (!empty($_POST['k_order']) && !empty($_POST['table_name'])){
            setcookie( $c_.'cookie_tb_'.$_POST['table_name'].'_k_order', $_POST['k_order'] ,0);
        }else if (!empty($_GET['k_order']) && !empty($_GET['table_name'])){
            setcookie( $c_.'cookie_tb_'.$_GET['table_name'].'_k_order', $_GET['k_order'] ,0);
        }
// == save i_page if choosed one
        if (!empty($_POST['i_page']) && !empty($_POST['table_name'])){
            setcookie( $c_.'cookie_tb_'.$_POST['table_name'].'_i_page', $_POST['i_page'] ,0);
        }else if (!empty($_GET['i_page']) && !empty($_GET['table_name'])){
            setcookie( $c_.'cookie_tb_'.$_GET['table_name'].'_i_page', $_GET['i_page'] ,0);
        }
        
        

// == try connection to database
    $error_msg = "";
    if (!file_exists(dirname(__FILE__) . "/../class_aSQLite.php")){
            echo "<html style='width:100%;'><body><div style='display:table;min-width: 20%;background-color:#eee;padding:21px;margin:10% auto;border-radius:7px;'>";
            echo "<p style='white-space:nowrap;'>Impossible to continue: it's missing the file <b>class_aSQLite.php</b>.</p>";
            echo "</div></body></html>";
            die();
    }else{
        include (dirname(__FILE__) . "/../class_aSQLite.php");
    }
    try {
        $database = new class_aSQLite(array(
                        'db_path' => $config['db_path'],
                        'db_filename'=>$config['db_filename']
                                        ));
    } catch (Exception $e) {
        // == we detect if exists an old database version and if yes then we offer user the upgrading
        $SRR = c_old_db_connection();
        if ($SRR !== false){
            if (isset($_GET['upgrade'])){
                copy(dirname(__FILE__).'/empty.sqlite',$config['db_path'].'/'.$config['db_filename']);
                $database = new class_aSQLite(array(
                                'db_path' => $config['db_path'],
                                'db_filename'=>$config['db_filename']
                                                ));
            }else{
                echo "<html style='width:100%;'><body><div style='width: 30%;background-color:#eee;padding:21px;margin:10% auto;border-radius:7px;'>";
                echo "<p>It's missing the database file <b>".$config['db_filename']."</b>, but it exists an OLD database version in this path.</p>";
                echo "<p>Click <a href='index.php?upgrade=1&db_path=".$config['db_path']."'><b>HERE</b></a> for create an empty database file <b>".$config['db_filename']."</b> and you can afterward try to CLONE/IMPORT the old database in this new one.</p>";
                echo "</div></body></html>";
                die();
            }
        }else{
            die($e->getMessage());
        }
    }
    $config['version'] = $database->version;
    $tables = $database->GET_LISTADO_TABLAS();
    
// == validate user
    if (!isset($_COOKIE) || !isset($_COOKIE[$c_.'cookie_logged'])){
       setcookie( $c_.'cookie_logged', '0' , $config['cookie_ctime_def']);
       $error_login = 1;
    }else{
       if (!empty($_REQUEST['pag']) && $_REQUEST['pag']=='logout'){
          setcookie( $c_.'cookie_logged', '0' , $config['cookie_ctime_def']);
          $error_login = 1;
       }else if ($_COOKIE[$c_.'cookie_logged']=='1'){
          $error_login = 0;
       }else if (!empty($_REQUEST['pag']) && $_REQUEST['pag']=='validate_login'){
          if ($_REQUEST['username']==$config['username'] && $_REQUEST['passwd']==$config['passwd']){
             setcookie( $c_.'cookie_logged', '1' , $config['cookie_ctime_def']);
             $error_login = 0;
          }else{
             $error_login = 1;
          }
       }else{
          $error_login = 1;
       }
    }

// == main vars
$pag = (!empty($_REQUEST['pag'])) ? $_REQUEST['pag'] : '';
$op1 = (!empty($_REQUEST['op1'])) ? $_REQUEST['op1'] : '';
$table_name = !empty($_REQUEST['table_name']) ? trim($_REQUEST['table_name']) : '';

// == ventana de login

    if ($error_login == 1) {
       $body = c_render_view('login',array());
       $html = c_render_view('layout',array('body'=>$body, 'db'=>$database, 'config'=>$config));
       die($html);
    }

// == if not error login 
    
    if (!empty($op1)) {
        //echo _var_export($_POST); die();
       switch ($op1) {
              case "vacuum":
                    $database->VACUUM();
                    break;
              case "trim":
                     if ($table_name != "") {
                        $database->TRIM_TABLE($table_name);
                     }
                     break;
              case "save_record":
                     $table = $tables[$table_name];
                     $valores_a = array();
                     foreach ($table['lista_campos'] as $campo) {
                        $data = trim($_REQUEST['f_' . trim($campo)]);
                        // == apply a recalculation of string length if it seems to be a serializated value
                        // == why? because when the strings contained in a serializated array contain htmlentities or accented vowels the serializated crash!! :o
                        $reg = '/s:(\d+):"(.*?)";/';
                        if (preg_match($reg,$data)){ 
                            $data = preg_replace_callback($reg, '_preg_replace_callback', $data);
                        }
                        if (trim($campo) != ""){
                            $valores_a[trim($campo)] = $data;
                        }
                     }
                     if (!empty($_REQUEST['id_record'])){
                         $database->UPDATE_RECORD($table_name, array('_id_' => $_REQUEST['id_record']), $valores_a);
                     }else{
                         $database->INSERT_RECORD($table_name, $valores_a);
                     }
                     break;
              case "delete_record":
                     $table = $tables[$table_name];
                     $database->DELETE_RECORD($table_name, array('_id_' => $_REQUEST['id_record']));
                     break;
              case "empty_table":
                     $database->EMPTY_TABLE($table_name, 1);
                     $tables = $database->GET_LISTADO_TABLAS();
                     break;
              case "save_table":
                     $table_name = $_POST['table_name'];
                     $new_table_name = trim($_POST['new_table_name']);
                     $fields = explode(" ", stripslashes(trim($_POST['fields'])));
                     $b_new = $table_name=='' ? true : false;
                     // check that it doesn't exist a table name with this name
                     $tables = $database->GET_LISTADO_TABLAS();
                     if ($new_table_name=="" || count($fields)==0){
                         $error_msg = "You must specify a name for the table and a list of fields.";
                     }else if (($b_new && isset($tables[$new_table_name]) ) 
                             || (!$b_new && $new_table_name!=$table_name && isset($tables[$new_table_name]) )){
                         $error_msg = "You cannot assign this name for this table because it yet exists another table named as: ".$new_table_name;
                     }else{
                         if ($b_new){
                               $database->CREATE_TABLE($new_table_name, $fields);
                         }else{
                            // save fields list 
                                $database->UPDATE_TABLE_FIELDS($table_name, $fields); // $table_name , $fields -> array campos
                            // save table name
                                if ($new_table_name != $table_name)
                                $database->RENAME_TABLE($table_name, $new_table_name);
                         }
                     }
                     $tables = $database->GET_LISTADO_TABLAS();
                     break;
              case "duplicate_table":
                     if ($table_name != "") {
                        $database->DUPLICATE_TABLE($table_name);
                        unset($tables);
                        $tables = $database->GET_LISTADO_TABLAS();
                     }
                     break;
              case "delete_table":
                     $database->DELETE_TABLE($table_name);
                     unset($tables);
                     $tables = $database->GET_LISTADO_TABLAS();
                     break;
              case "import_db":
                     if ($_FILES['import_file']['tmp_name'] != '') { 
                        if (isset($_FILES['import_file']) and is_uploaded_file($_FILES['import_file']['tmp_name'])) {
                           if (move_uploaded_file($_FILES['import_file']['tmp_name'], 'tmp/temp.sqlite')) {
                              // if the file has been correctly moved to its place 
                              chmod(dirname(__FILE__).'/tmp/temp.sqlite', 0777);
                              $database->IMPORT_DB(dirname(__FILE__).'/tmp/','temp.sqlite');
                              @unlink(dirname(__FILE__).'/tmp/temp.sqlite');
                              $tables = $database->GET_LISTADO_TABLAS();
                              $database->ERROR_MSG = 'The table SQL database has been perfectly imported.';
                           } else {
                              // it wasn't posible to upload file
                              $database->ERROR_MSG = "Error: it wasn't posible to upload file";
                           }
                        }
                     }
                    break;
              case "import_table":
                     if ($_FILES['import_file']['tmp_name'] != '') {
                        if (isset($_FILES['import_file']) and is_uploaded_file($_FILES['import_file']['tmp_name'])) {
                           if (move_uploaded_file($_FILES['import_file']['tmp_name'], 'temp.php')) {
                              // if the file has been correctly moved to its place 
                              $database->IMPORT_TABLE($table_name, 'temp.php');
                              $tables = $database->GET_LISTADO_TABLAS();
                              $database->ERROR_MSG = 'The table ' . $table_name . ' has been perfectly imported.';
                           } else {
                              // it wasn't posible to upload file
                              $database->ERROR_MSG = "Error: it wasn't posible to upload file";
                           }
                        }
                     }
                     break;
              case "export_table":
                     $database->EXPORT_TABLE($table_name);
                     break;
              case "export_table_csv":
                     if (isset($_REQUEST['filter'])){
                        if (isset($config['search_query']) && $config['search_query']!=''){
                            $operator = (isset($config['search_regexp']) && $config['search_regexp']=='on') ? '~~' : '~|';
                            $w = array(array($config['search_field'], $operator, $config['search_query']));
                        }else{
                            $w = array();
                        }
                        $k_order = (!empty($_REQUEST['k_order'])) ? stripslashes($_REQUEST['k_order']) : (isset($_COOKIE[$c_.'cookie_tb_'.$table_name.'_k_order'])?$_COOKIE[$c_.'cookie_tb_'.$table_name.'_k_order']:'');
                        $records = $database->SELECT(array('t' => $table_name, 'w'=>$w, 'o' => $k_order.'[s', 'o2' => 'ASC'));
                        $database->EXPORT_TABLE_CSV($table_name,$records);
                     }else{
                        $database->EXPORT_TABLE_CSV($table_name);
                     }
                     break;
              case "import_old_database":
                  $current_tables = $database->TABLE_LIST();
                  $old_db_tables = c_old_db_tables();
                     if (count($old_db_tables)>0){
                         $ts = time();
                         foreach($old_db_tables as $table){
                             if (isset($current_tables[$table['nombre']]))
                                $new_table_name = $table['nombre'].'_'.$ts.'_imported';
                             else
                                $new_table_name = $table['nombre']; 
                             $database->CREATE_TABLE($new_table_name, $table['lista_campos']);
                             $rows = c_old_table_records($table['nombre']);
                             if (count($rows)>0){
                                 foreach($rows as $row){
                                    $row['campos']['_id_'] = $row['id'];
                                    $database->INSERT_RECORD($new_table_name, $row['campos']);
                                 }
                             }
                         }
                     }
                     $tables = $database->GET_LISTADO_TABLAS();
                     break;
              case "import_json_database":
                    c_import_json_db_tables();
                    $tables = $database->GET_LISTADO_TABLAS();
                    break;
       } // switch
    }
        
    $ilink = 0;
    switch ($pag) {
       case "statistics":
            // == load & prepare data 
                $statistics = array();
                $table = $tables[$table_name];
                $records = $database->SELECT(array('t' => $table['nombre'], 'w'=>array()));
                if (count($records)>0){
                    $first = reset($records);
                    foreach($first as $ftit=>$v) $statistics[$ftit] = array('n'=>0,'min'=>1e10, 'max'=>-1e10, 'stats'=>array());
                    foreach($records as $arr){
                        foreach ($arr as $ftit=>$v){
                            if ($v=='') continue;
                            $statistics[$ftit]['n']++;
                            if ($ftit != '_id_'){
                                if (!isset($statistics[$ftit]['stats'][$v]))
                                    $statistics[$ftit]['stats'][$v] = 1;
                                else
                                    $statistics[$ftit]['stats'][$v]++;
                            }
                            //if ($v > $statistics[$ftit]['max']) $statistics[$ftit]['max'] = $v;
                            //if ($v < $statistics[$ftit]['min']) $statistics[$ftit]['min'] = $v;
                        }
                    }
                    // == sort and limit to only the most repeated values
                    $limit = $config['stats_limit_per_field'];
                    foreach ($statistics as $ftit=>$arr){
                        arsort($arr['stats']);
                        if (count($arr['stats']) > $limit){
                            $new_stats = array();
                            $ii = 0;
                            foreach ($arr['stats'] as $f=>$v){
                                $ii++;
                                if ($ii > $limit) break;
                                $new_stats[$f] = $v;
                            }
                            $statistics[$ftit]['stats'] = $new_stats;
                        }else{
                            $statistics[$ftit]['stats'] = $arr['stats'];
                        }
                    }
                }
                
            // == render
                $body = c_render_view('statistics',array(  'table'=>$table, 'tables'=>$tables ,'statistics'=>$statistics, 'config'=>$config));
                $html = c_render_view('layout',array('body'=>$body, 'db'=>$database, 'config'=>$config, 'error_msg'=>$error_msg));
                die($html);
           
           break;
       case "records":
              $config_char_size = 20;
              $table = $tables[$table_name];
              $k_order = (!empty($_REQUEST['k_order'])) ? stripslashes($_REQUEST['k_order']) : (isset($_COOKIE[$c_.'cookie_tb_'.$table_name.'_k_order'])?$_COOKIE[$c_.'cookie_tb_'.$table_name.'_k_order']:'');
              $i_page = (!empty($_REQUEST['i_page'])) ? intval($_REQUEST['i_page']) : (isset($_COOKIE[$c_.'cookie_tb_'.$table_name.'_i_page'])?$_COOKIE[$c_.'cookie_tb_'.$table_name.'_i_page']:1);
              $limit_first = ($i_page - 1) * $config['records_per_page'] + 1;
              $limit_last = $i_page * $config['records_per_page'];
              if (isset($config['search_query']) && $config['search_query']!=''){
                  $operator = (isset($config['search_regexp']) && $config['search_regexp']=='on') ? '~~' : '~|';
                  $w = array(array($config['search_field'], $operator, $config['search_query']));
              }else{
                  $w = array();
              }
              $records = $database->SELECT(array('t' => $table['nombre'], 'w'=>$w, 'o' => $k_order.'[s', 'o2' => 'ASC', 'l1' => $limit_first, 'l2' => $limit_last));
              $table['total_records'] = $database->GET_NUM_RECORDS($table['nombre']);
              if (count($w)>0)
                $table['filtered_records'] = $database->GET_NUM_RECORDS($table['nombre'],$w);
              else
                $table['filtered_records'] = $table['total_records'];
              $ilink++;
              // == preparar paginado
                $n_reg_x_pag = $config['records_per_page']; // how many elements per page 
                $n_pages = intval($table['filtered_records'] / $n_reg_x_pag);
                if ($n_pages != ($table['filtered_records'] / $n_reg_x_pag)) $n_pages++;
                if ($i_page>$n_pages) $n_pages = $i_page; // for coherence when deleting the last record of the last page

              // == find the record to edit
                  $editable_record = array();
                  if ($op1 == 'edit_record' && count($records) > 0) {
                     foreach ($records as $arr) {
                         if ($_REQUEST['id_record'] == $arr['_id_']) $editable_record = $arr;
                     }
                  }

              // == render
                $body = c_render_view('record_list',array(  'table'=>$table,'records'=>$records, 'n_pages'=>$n_pages, 'i_page'=>$i_page, 
                                                            'op1'=>$op1, 'editable_record'=>$editable_record, 'k_order'=>$k_order, 'config_char_size'=>$config_char_size, 'config'=>$config));
                $html = c_render_view('layout',array('body'=>$body, 'db'=>$database, 'config'=>$config, 'error_msg'=>$error_msg));
                die($html);

              break;
       default:

              // == find the table to edit
                  $editable_table = array();
                  if ($op1 == 'edit_table' && count($tables) > 0) {
                     foreach ($tables as $arr) {
                         if (trim($_GET['table_name']) == trim($arr['nombre'])) $editable_table = $arr;
                     }
                  }
                  ksort($tables);
                  
              // == render
                $body = c_render_view('table_list',array('tables'=>$tables, 'op1'=>$op1, 'editable_table'=>$editable_table));
                $html = c_render_view('layout',array('body'=>$body, 'db'=>$database, 'config'=>$config, 'error_msg'=>$error_msg));
                die($html);

              break;
    }

    $database->CLOSE();
    unset($database);

    return;

// == private function for nicely print arrays in HTML&CSS style ;)
    function _var_export($arr, $title='') {
        $html = !empty($title) ? '<h3>'.$title.'</h3>' : '';
        $html .= "\n<div style='margin-left:100px;font-size:11px;font-family:sans-serif;background-color:#fff;'>";
        if (is_array($arr)) {
             foreach ($arr as $k => $ele)
                 $html .= "\n<div style='float:left;'><b>$k <span style='color:#822;'>-></span> </b></div>"
                         . "\n<div style='border:1px #ddd solid;'>" . _var_export($ele) . "</div>";
        }else {
               $html .= ($arr == NULL) ? "&nbsp;" : htmlspecialchars($arr);
        }
        $html .= "</div>";
        return $html;
    }

    function c_render_view($viewname,Array $vars){
            if (count($vars)>0){ foreach($vars as $k=>$v){${$k}=$v;}}
        // == we save a copy of the content already existing at the output buffer (for no interrump it)
            $existing_render = ob_get_clean( );
        // == we begin a new output
            ob_start( );
            include(dirname(__FILE__).'/view_'.$viewname.'.php');
        // == we get the current output
            $render = ob_get_clean( );     
        // == we re-send to output buffer the existing content before to arrive to this function ;)
            ob_start( );
            echo $existing_render;

            return $render;
    }

    function c_db_available_list(){
            global $config;
            $ret = array();
            $handle = opendir($config['db_path']);
            while (false !== ($readdir = readdir($handle))) {
               $path = $config['db_path'] . '/' . $readdir;
               if ($readdir != '.' && $readdir != '..' 
                       && is_file($path) && preg_match('/.sqlite$/i',$readdir)) {
                  $ret[] = array($readdir,filesize($path));
               }
            }
            closedir($handle);
            return $ret;
    }

    function c_bytes_format($bytes){
        if ($bytes<1024) $ret=$bytes.'b';
        else if ($bytes<1024*1024) $ret=number_format($bytes/1024,1).'Kb';
        else if ($bytes<1024*1024*1024) $ret=number_format($bytes/(1024*1024),1).'Mb';
        else $ret=number_format($bytes/(1024*1024*1024),1).'Gb';
        return $ret;
    } 

    function c_old_db_tables(){
            $SRR = c_old_db_connection();
            if (!$SRR){
                $tables = array();
            }else{
                $tables = $SRR->GET_LISTADO_TABLAS();
            }
            return $tables;
    }

    function c_old_table_records($table_name){
            $SRR = c_old_db_connection();
            if (!$SRR){
                $rows = array();
            }else{
                if($SRR->GET_NUM_RECORDS($table_name) >0){
                    $rows = $SRR->GET_RECORDS_TABLE($table_name);
                }else{
                    $rows = array();
                }
            }
            return $rows;
    }

    function c_old_db_connection(){
            global $config;
            if (file_exists($config['db_path'] . '/SRR_tablas.txt')){
                $old_path = $config['db_path'];
            }else if (file_exists($config['db_path'] . '/SRR_tablas.php')){
                $old_path = $config['db_path'];
            }else if (file_exists($config['db_path'] . '/class_SRR_database_sim/SRR_tablas.php')){
                $old_path = $config['db_path'] . '/class_SRR_database_sim';
            }else if (file_exists($config['db_path'] . '/class_SRR_database_sim/SRR_tablas.txt')){
                $old_path = $config['db_path'] . '/class_SRR_database_sim';
            }else{
                $old_path = '';
            }
            $old_path = str_replace('//','/',$old_path);
            if (!empty($old_path)){
                    if (file_exists(dirname(__FILE__).'/../class_SRR_database_sim35.php')){
                            include_once (dirname(__FILE__)."/../class_SRR_database_sim35.php");
                            $SRR = new class_SRR_database_sim35(array('path_data'=>$old_path));
                    }else if (file_exists(dirname(__FILE__).'/../class_SRR_database_sim5.php')){
                            include_once (dirname(__FILE__)."/../class_SRR_database_sim5.php");
                            $SRR = new class_SRR_database_sim5(array('path_data'=>$old_path));
                    }else if (file_exists($old_path.'/class_SRR_database_sim5.php')){
                            include_once ($old_path.'/class_SRR_database_sim5.php');
                            $SRR = new class_SRR_database_sim5(array('path_data'=>$old_path));
                    }else if (file_exists($old_path.'/class_SRR_database_sim3.php')){
                            include_once ($old_path.'/class_SRR_database_sim3.php');
                            $SRR = new class_SRR_database_sim3(array('path_data'=>$old_path));
                    }else if (file_exists($old_path.'/class_SRR_database_sim.php')){
                            include_once ($old_path.'/class_SRR_database_sim.php');
                            $SRR = new class_SRR_database_sim(array('path_data'=>$old_path));
                    }else{
                        return false;
                    }
            }else{
                $SRR = false;
            }
            return $SRR;

    }

    function c_test(){
        // == we shoot this 'easter egg' with the use of 'test' keyword on URL ;)
        if (!isset($_GET['test'])) return;

        global $config;
        include('test.php');
        $test = new Test($config);
        //list($spent_time,$test_table,$n_records) = $test->create_random_data(10000);
        list($spent_time,$test_table,$n_records) = $test->edit_random_data('test_4325', 100);
        echo "<p>Edited $n_records records at the new table <b>$test_table</b> (in $spent_time seconds).</p>";

    }

    function c_load_json_db_tables(){
            global $config;
            $json_db_tables = array();
            
            // == are there a JSON file of an exported database from MySQL PHPMyAdmin ?
                $scan = scandir($config['db_path']);
                if (is_array($scan) && count($scan)>0){
                    foreach ( $scan as $filename){
                        if (!preg_match('/(.*)\.json$/i',$filename)) continue;
                        $json_db_tables[$filename] = array('db_name'=>'','tables'=>array());
                    }
                }
            // == extract list of tables contained
                if (count($json_db_tables)>0){
                    foreach ($json_db_tables as $filename=>$arr){
                        $comment = false;
                        $db_name = '';
                        $tb_content = '';
                        $tb_name = '';
                        $handle = fopen($config['db_path'].'/'.$filename,"r"); 
                        while (($line = fgets($handle)) !== false) {
                            $line = trim($line);
                            if ($line==''){
                                continue;
                            }else if (substr($line,0,2)=='/*'){
                                $comment = true;
                            }else if (substr($line,0,2)=='*/'){
                                $comment = false;
                            }else if (preg_match('/^\/\/ Database \'(.*)\'/i' , $line, $matches)){
                                $db_name = trim($matches[1]);
                                $json_db_tables[$filename]['db_name'] = $db_name;
                            }else if (preg_match('/(.*)\/\/ '.$db_name.'.(.*)$/i' , $line, $matches)){
                                $tb_content .= trim($matches[1]);
                                $length = strlen($tb_content);
                                $a_tb_content = json_decode($tb_content,true);
                                if (is_array($a_tb_content) && count($a_tb_content) > 0){
                                    $first = reset($a_tb_content);
                                    $json_db_tables[$filename]['tables'][$tb_name] = array('n_records'=>count($a_tb_content), 'size'=> $length, 'fields'=>  array_keys($first));
                                }
                                $tb_content = '';
                                $tb_name = trim($matches[2]);
                            }else{
                                $tb_content .= $line;
                            }
                        }
                        fclose($handle);
                    }
                }
                
            //echo '<h3>number of tables: '.count($json_db_tables[$filename]['tables']).'</h3>'.  _var_export($json_db_tables,'$json_db_tables'); die();
            
            return $json_db_tables;
    }

    function c_import_json_db_tables(){
            global $config, $database;
            
            if (empty($_POST['filename']) || empty($_POST['tables'])) return;
            
            $json_db_filename = $_POST['filename'];
            
            if (!preg_match('/(.*)\.json$/i',$json_db_filename)) return;
            if (!file_exists($config['db_path'].'/'.str_replace('/','',$json_db_filename))) return;
            
            //echo _var_export($_POST,'$_POST');die();
            
            $json_db_tables = array();
            $current_tables = $database->TABLE_LIST();
            $ts = time();
            
            // == extract list of tables contained
                $comment = false;
                $db_name = '';
                $tb_content = '';
                $tb_name = '';
                $handle = fopen($config['db_path'].'/'.$json_db_filename,"r"); 
                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    if ($line==''){
                        continue;
                    }else if (substr($line,0,2)=='/*'){
                        $comment = true;
                    }else if (substr($line,0,2)=='*/'){
                        $comment = false;
                    }else if (preg_match('/^\/\/ Database \'(.*)\'/i' , $line, $matches)){
                        $db_name = trim($matches[1]);
                        $json_db_tables[$json_db_filename]['db_name'] = $db_name;
                    }else if (preg_match('/(.*)\/\/ '.$db_name.'.(.*)$/i' , $line, $matches)){
                            if ($tb_name!='' && isset($_POST['tables'][$tb_name])){
                                // == this is the end of a line cointaining the records of a table
                                // == so we create a new table to the sqlite database
                                    $tb_content .= trim($matches[1]);
                                    $a_tb_content = json_decode($tb_content,true);
                                    if (is_array($a_tb_content) && count($a_tb_content) > 0){

                                        // == table name
                                            $new_table_name = $db_name.'___'.$tb_name;
                                            if (isset($current_tables[$new_table_name]))
                                            $new_table_name .= '___'.$ts;

                                        // == table fields
                                            $first = reset($a_tb_content);
                                            $fields = array_keys($first);
                                            
                                        // == populate autonumeric _id_ ?
                                            if ($_POST['populate_id']!='no' && in_array('id',$fields)){
                                                foreach ($a_tb_content as $ii=>$arr){
                                                    $a_tb_content[$ii]['_id_'] = $arr['id'];
                                                    if ($_POST['populate_id']=='yesyes')
                                                        unset($a_tb_content[$ii]['id']);
                                                }
                                                if ($_POST['populate_id']=='yesyes') {
                                                    foreach ($fields as $k=>$v){
                                                        if ($v=='id') unset($fields[$k]);
                                                    }
                                                }
                                            }
                                            

                                        // == save at db
                                            $database->CREATE_TABLE($new_table_name, $fields);
                                            $database->MULTIPLE_INSERT(array('t'=>$new_table_name, 'v'=>$a_tb_content));

                                    }
                            }
                            $tb_name = trim($matches[2]);
                            $tb_content = '';
                            
                    }else{
                        $tb_content .= $line;
                    }
                }
                fclose($handle);
            
            return $json_db_tables;
    }

    function f_bytes_format($bytes){
        if ($bytes<1024) $ret=$bytes.'b';
        else if ($bytes<1024*1024) $ret=number_format($bytes/1024,1).'Kb';
        else if ($bytes<1024*1024*1024) $ret=number_format($bytes/(1024*1024),1).'Mb';
        else $ret=number_format($bytes/(1024*1024*1024),1).'Gb';
        return $ret;
    }    

    function _preg_replace_callback($matches){
           return "s:".strlen($matches[2]).":\"".$matches[2]."\";";
     }  
    
?>