<?php

# you need to have installed the PDO SQLite driver installed in your server
# for ubuntu is easy to install: sudo apt-get install php5-sqlite

class class_aSQLite {
   #
   # user vars

   #
   ################################################################################
   # internal variables
   # starting from here the following vars should not be changed from
   #
   var $version = '2.8';
   var $email_admin = 'info@imasdeweb.com';
   var $db_filename = 'barllo.sqlite';
   var $db_path = '';
   var $tablas = array();
   var $records = array();
   var $changed_records = array();
   var $added_records = array();
   var $deleted_records = array();
   public $num_records = 0;
   public $num_filtered_records = 0;
   var $tabla_activa = "";
   var $fp_busy = '';
   var $b_error = false;
   var $b_debug_on = false;
   var $debug_file = 'debug.htm';
   var $dbh;

   var $ERROR = false;
   var $ERROR_MSG = "";

   
   #
   ####################################################################eof internal

   function __construct($a_location='') {
      // == physical location of the directory containing data 
      if (is_array($a_location) && isset($a_location['db_path'])) {
         $path = trim($a_location['db_path']);
         if ($path == ''){
             $this->db_path = '';
         }else{
            $this->db_path = $path.'/';
         }
      }else {
         $this->db_path = dirname(__FILE__) . '/';
      }
      $this->db_path = str_replace('//','/',$this->db_path);
      
      // == database filename (something like: mydatabase.sqlite)
      if (is_array($a_location) && isset($a_location['db_filename'])) {
         $database_filename = trim($a_location['db_filename']);
         if ($database_filename != '')
            $this->db_filename = $database_filename;
      }
      // == connect with database
      if (!$this->CONNECT_DATABASE()){
          echo 'class_aSQLite error: Could not connect with database '.$this->db_path.$this->db_filename;
          return;
      }
   }

// EXTERNAL functions **********************************************************************

   function TABLE_LIST() {
      $this->load_table_list();
      return $this->tablas;
   }

   function CONNECT_DATABASE($filename = '', $path = '') {
      if ($filename == '')
         $filename = $this->db_filename;
      if ($path == '')
         $path = $this->db_path;
      if (!file_exists($path . $filename)) return false;
      $this->dbh = null;
      $this->dbh = new PDO('sqlite:' . $path . $filename);
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set errormode to exceptions
      return true;
   }

   function GET_NUM_TABLAS() {
      $n = count($this->tablas);
      if ($n == 0) {
         // we avoid load twice the table list
         // because after class constructor the array "tablas" is empty
         $this->load_table_list();
         $n = count($this->tablas);
      }
      return $n;
   }

   function GET_NUM_RECORDS($table_name , $a_where='') {
      return $this->get_num_records_table($table_name, $a_where);
   }

   function GET_CREATE_TABLE($table_name) {
      if (!$this->table_exist($table_name))
         return false;
      $sth = $this->dbh->prepare("SELECT * FROM sqlite_master WHERE type='table' AND tbl_name=:table_name");
      $sth->execute(array(':table_name' => $table_name));
      if (!$sth) {
         $this->ERROR = true;
         $this->ERROR_MSG = "It's not possible to continue because doesn't exist the table <b>$table_name</b>.";
         return false;
      }
      $a_create_sentence = $sth->fetch();
      return $a_create_sentence['sql'];
   }

   function FIELD_LIST($table_name) {
      if (!$this->table_exist($table_name))
         return false;
      $create_sentence = $this->GET_CREATE_TABLE($table_name);
      return $this->_extract_field_array_from_create_table($create_sentence);
   }

   function RENAME_TABLE($old_name, $new_name) { // $op1 -> old_name_table , $op2 -> new_name_table
      if (!$this->table_exist($old_name) || $this->table_exist($new_name))
         return false;
      $sth = $this->dbh->prepare("ALTER TABLE \"".$old_name."\" RENAME TO \"".$new_name."\"");
      $sth->execute(array());
      $this->load_table_list();
      return true;
   }

   function EMPTY_TABLE($table_name, $id_set_to_zero = 0) { //@@@
      if (!$this->table_exist($table_name))
         return false;
      $sth = $this->dbh->prepare("DELETE FROM \"".$table_name."\"");
      $sth->execute(array());
      $this->VACUUM();
      $this->load_table_list();
      return true;
   }

   function DROP_TABLE($table_name) {
      if (!$this->table_exist($table_name))
         return false;

      $sth = $this->dbh->prepare("DROP TABLE \"".$table_name."\"");
      $sth->execute(array());
      $this->VACUUM();
      $this->load_table_list();
      return true;
   }

   function CREATE_TABLE($table_name, $a_fields) {
      if ($table_name == '' || $this->table_exist($table_name))
         return false;
      if (count($a_fields) == 0) { 
         $sth = $this->_prepare("Missing list of fields... impossible to create the table!");
         $sth->execute();
      } else {
         $a_fields2 = array(0=> $this->qf('_id_').' INTEGER PRIMARY KEY');
         foreach ($a_fields as $fieldname) {
            if (!is_array($fieldname)){
               if ($fieldname != '_id_')
                  $a_fields2[] = $this->qf($fieldname).' TEXT';
            }else{
               if ($fieldname[0] != '_id_')
                  $a_fields2[] = $this->qf($fieldname[0]).' '.$fieldname[1];
            }
         }
         $sth = $this->_prepare('CREATE TABLE "'.$table_name.'" (' . implode(', ', $a_fields2) . ')');
         $sth->execute();
      }
      $this->load_table_list();
      return true;
   }

   function DUPLICATE_TABLE($table_name) { // $op1 -> nombre_tabla   
      if (!$this->table_exist($table_name))
         return false;
      // = build a new unique name 
      $new_table_name = $table_name . '_' . date('Ymd_His');
      // = create the new table
      $a_fields_list = $this->FIELD_LIST($table_name);
      $this->CREATE_TABLE($new_table_name, $a_fields_list);
      // = load the actual records
      $sth = $this->_prepare("SELECT * FROM \"".$table_name."\"");
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute();
      $a_rows = array();
      while ($row = $sth->fetch()) {
         $a_rows[] = $row; 
      }
      unset($sth);
      // = populate the new table
      $this->INSERT_MULTIPLE($new_table_name, $a_rows);
      // == reload the tables list
      $this->load_table_list();
      return true;
   }

   function DUPLICATE_DATABASE() { 
      $db_file = $this->db_path.$this->db_filename;
      if (!file_exists($db_file)) return false;
      $a_path = pathinfo($db_file);
      $backup_file = $a_path['dirname'].'/'.$a_path['filename'].'_'.date('Y-m-d_H-i-s').'.sqlite';
      @copy($db_file,$backup_file);
      return true;
   }

   function TRIM_TABLE($table_name) { // $op1 -> nombre_tabla   
      if (!$this->table_exist($table_name))
         return false;
      
      // == prepare
        $a_fields_list = $this->FIELD_LIST($table_name);
        $a_set = array();
        foreach($a_fields_list as $field){
            if (!isset($field[1]) || $field[1]!='TEXT') continue;
            $qf = $this->qf($field[0]);
            $a_set[] =  $qf . '= LTRIM('.$qf.')';
        }
      
      $sql = 'UPDATE "'.$table_name.'" SET '.implode(', ',$a_set) ;
      $sth = $this->_prepare($sql);

      // == execute query
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute();
      return true;
   }

   function UPDATE_TABLE_FIELDS($table_name, $a_fields) {
      // this function recreate the specified table to the new structure: 
      // is possible to delete fields, create new fields, or resort the existing fields
      if (!$this->table_exist($table_name) || !is_array($a_fields) || count($a_fields)==0)
         return false;
      // == proposed structure
      $a_new_fields = array();
      foreach ($a_fields as $t) {
         $t = trim(stripslashes($t));
         if ($t != "")
            $a_new_fields[$t] = $t;
      }

      // == create the new table structure
      $temporal_name = $table_name . '_temp_' . date('Ymd_His').'_'.rand();
      $this->CREATE_TABLE($temporal_name, $a_new_fields);

      // == populate the new table with the records of the old table
      // == we run it in batches (10,000 records by batch) to avoid crash for RAM exceeded
      $continue = true;
      $l1 = 1; $ll = 10000;
      while ($continue){
        // == load the actual content and 
            $a_records = $this->SELECT(array('t'=>$table_name, 'l1'=>$l1, 'l2'=>($l1 + $ll)));
            if (is_array($a_records) && count($a_records)>0){
                // == populate the new table with the old table records
                    $this->MULTIPLE_INSERT(array('t'=>$temporal_name,'v'=>$a_records));
                    unset($a_records);
                // == next batch
                    $l1 += $ll + 1;
            }else{
                $continue = false;
            }
      }
      
      // == delete the original table and rename the new table
      $this->DELETE_TABLE($table_name);
      $this->RENAME_TABLE($temporal_name, $table_name);
   }

   function MULTIPLE_UPDATE($a_vars) {
      if (!is_array($a_vars) || count($a_vars)==0) return false;
      $ret = array();
      $this->dbh->beginTransaction();
      foreach ($a_vars as $ii => $vars){
          $ret[$ii] = $this->UPDATE($vars);
      }
      $this->dbh->commit();
      return $ret;
   }
   
   function UPDATE($vars) { // $vars=array('t'=>$table_name,'w'=>$a_where=array('name'=>'pepe', 'password'=>'333'),'v'=>$a_values=array()) 
      // other a_where example:  array('_op_'=> '1 && (2 || 3)' , array('name','=','id_casa'),array('mes','>=','200907'),array('mes','<=','200909'));
      if (!isset($vars['t']) || !$this->table_exist($vars['t']))
         return false;
      if (!isset($vars['v']) || !is_array($vars['v']) || count($vars['v'])==0)
         return false;
      
      // == prepare WHERE clausule
      if (!isset($vars['w']) || !is_array($vars['w']) || count($vars['w'])==0){
        $WHERE = '';
        $a_params1 = array();
      }else{
        list($conditions, $a_params1) = $this->prepare_where($vars['w'], $vars['t']);
        $this->dbh->sqliteCreateFunction("preg_match", "preg_match", 2);
        $WHERE = ' WHERE '.$conditions ;
      }

      // == prepare set
      list($set_imploded, $a_params2) = $this->prepare_set($vars['v'], $vars['t']);
      $a_params = array_merge($a_params1,$a_params2);

      // == prepare
      $sql = 'UPDATE "'.$vars['t'].'" SET ' . $set_imploded .' '.$WHERE;
      $sth = $this->_prepare($sql,$a_params);

      // == execute query
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute($a_params);
        return true;
   }
   
   function INSERT($vars) { // $vars=array('t'=>$table_name,'v'=>$a_field_values)
      if (!isset($vars['t']) || !$this->table_exist($vars['t']))
         return false;
      if (!isset($vars['v']) || !is_array($vars['v']) || count($vars['v'])==0)
         return false;
      $a_fields_list = $this->FIELD_LIST($vars['t']);

      $a_set = array();
      $a_values = array();
      $f_implode = array();
      $p_implode = array();
      foreach ($a_fields_list as $f => $arr) {
         if (isset($vars['v'][$f])) {
            $ff = str_replace('-','__',$f);
            $f_implode[] = $this->qf($f);
            $p_implode[] = ':' . $ff;
            $a_values[$ff] = $vars['v'][$f];
         }
      }
      $f_imploded = implode(', ', $f_implode);
      $p_imploded = implode(', ', $p_implode);
      
    // == prepare
      $sql = 'INSERT INTO "' . $vars['t'] . '" (' . $f_imploded . ') VALUES (' . $p_imploded . ')';
      $sth = $this->_prepare($sql,$a_values);
      
      // == execute
      $sth->execute($a_values);
      $new_id = $this->dbh->lastInsertId();
      
      return $new_id;
   }

   function MULTIPLE_INSERT($vars) {
      if (!isset($vars['t']) || !isset($vars['v']) || !is_array($vars['v']))
         return false;
      if (!$this->table_exist($vars['t']))
         return false;
      $a_new_ids = array();
      $b_prepared = false;
      $a_fields_list = $this->FIELD_LIST($vars['t']);
      $this->dbh->beginTransaction();
      foreach ($vars['v'] as $k => $row) {
         // == build "prepare" clausule only when we process the first row 
         if (!$b_prepared) {
            $a_values = array();
            $f_implode = array();
            $p_implode = array();
            foreach ($row as $f => $v) {
               $f = trim($f);
               if (isset($a_fields_list[$f])){
                  $ff = str_replace('-','__',$f);
                  $f_implode[] = $this->qf($f);
                  $p_implode[] = ':' . $ff;
               }
            }
            $f_imploded = implode(', ', $f_implode);
            $p_imploded = implode(', ', $p_implode);
            
            // == try to prepare
            $sql = 'INSERT INTO "' . $vars['t'] . '" (' . $f_imploded . ') VALUES (' . $p_imploded . ')';
            $sth = $this->_prepare($sql,array());
            $b_prepared = true;
         }
         // == filter unexisting fields
            $row2 = array();
            foreach ($row as $f => $v) {
               $f = trim($f);
               if (isset($a_fields_list[$f])){
                  $row2[str_replace('-','__',$f)] = $v;
               }
            }
         // == execute
         $res = $sth->execute($row2);
         $new_id = $this->dbh->lastInsertId();
         $a_new_ids[$new_id] = $new_id;
      }
      $this->dbh->commit();

      return $a_new_ids;
   }

   function MULTIPLE_DELETE($a_vars) {
      if (!is_array($a_vars) || count($a_vars)==0) return false;
      $ret = array();
      $this->dbh->beginTransaction();
      foreach ($a_vars as $ii => $vars){
          $ret[$ii] = $this->DELETE($vars);
      }
      $this->dbh->commit();
      return $ret;
   }
   
   function DELETE_BY_ID($vars) {
      if (empty($vars['id']) || empty($vars['t']) || !$this->table_exist($vars['t']))
         return false;
      $sql = 'DELETE FROM "' . $vars['t'] . '" WHERE '.$this->qf('_id_').' = :_id_';
      $sth = $this->_prepare($sql);
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute(array('_id_'=>$vars['id']));
      return true;
   }

   function MULTIPLE_DELETE_BY_ID($a_vars) {
      if (!is_array($a_vars) || count($a_vars)==0) return false;
      $ret = array();
      $this->dbh->beginTransaction();
      foreach ($a_vars as $ii => $vars){
          $ret[$ii] = $this->DELETE_BY_ID($vars);
      }
      $this->dbh->commit();
      return $ret;
   }
   
   function DELETE($vars) { // $vars=array('t'=>'table_name','w'=>array('name'=>'pepe', 'password'=>'333'))  
      // another example of 'w':  array('_op_'=> '1 && (2 || 3)' , array('name','=','id_casa'),array('mes','>=','200907'),array('mes','<=','200909'));
      if (!isset($vars['t']) || !$this->table_exist($vars['t']))
         return false;
      if (!isset($vars['w']) || (isset($vars['w']) && (!is_array($vars['w']) || count($vars['w'])==0)))
         return false;

      // == prepare WHERE clausule
      list($conditions, $a_params) = $this->prepare_where($vars['w'], $vars['t']);
      if ($conditions=='') return false;
      // == prepare and execute query
      $this->dbh->sqliteCreateFunction("preg_match", "preg_match", 2); 
      $sql = 'DELETE FROM "' . $vars['t'] .'" WHERE '.$conditions;
      $sth = $this->_prepare($sql,$a_params);
      $sth->execute($a_params);
      
      return true;
   }

   function LAST_RECORD($table_name = '') {
      if (!$this->table_exist($table_name))
         return false;
      $sql = 'SELECT * FROM "' . $table_name . '" ORDER BY '.$this->qf('_id_').' DESC LIMIT 1';
      $sth = $this->_prepare($sql);
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute();
      $record = $sth->fetch();
      return $record;
   }

   function FIRST_RECORD($table_name = '') {
      if (!$this->table_exist($table_name))
         return false;
      $sql = 'SELECT * FROM "' . $table_name . '" ORDER BY '.$this->qf('_id_').' ASC LIMIT 1';
      $sth = $this->_prepare($sql);
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute();
      $record = $sth->fetch();
      return $record;
   }

   function GET_BY_ID($table_name = '', $id = '') {
      if (empty($id) || !$this->table_exist($table_name))
         return false;
      $sql = 'SELECT * FROM "' . $table_name . '" WHERE '.$this->qf('_id_').' = :_id_';
      $sth = $this->_prepare($sql);
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute(array('_id_'=>$id));
      $record = $sth->fetch();
      return $record;
   }

   function GET_BY_ID_IN($table_name = '', $id = '', $k_id = '_id_') { // return the set of records with their ID included in the array passed as $id
      if (empty($id) || !is_array($id) || count($id)==0 || !$this->table_exist($table_name))
         return false;
      /* the array $id must pass integer IDs... so as redundant safety measure we convert its values to integer */
      $IN = array();
      foreach($id as $v){
          $v = intval($v);
          if ($v>0) $IN[] = $v;
      }
      if (f_trim($k_id)=='') $k_id = '_id_';
      $sql = 'SELECT * FROM "' . $table_name . '" WHERE '.$this->qf($k_id).' IN ('.implode(',',$IN).')';
      $sth = $this->_prepare($sql);
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      $sth->execute();
      
      $a_records = array();
      while( $record = $sth->fetch() ){
          if (!empty($key))
            $a_records[$record[$key]] = $record;
          else
            $a_records[$record['_id_']] = $record;  
      }
      return $a_records;
      
   }

   function SELECT($vars) { // $vars=array('t'=>'table_name','f'=>'count(_id_) as n, sum(money) as s', 'w'=>array('name'=>'pepe', 'password'=>'333'),
                            // 'l1'=>10,'l2'=>20,'k'=>'_id_','o'=>'age','o2'=>'ASC', 'g'=>'country')

      $mc0 = $this->_microtime(); 
      if (!$this->table_exist($vars['t']))
         return false;
      $key = (isset($vars['k'])) ? trim($vars['k']) : '_id_';
      $w = isset($vars['w']) ? $vars['w'] : array();
      
      // == prepare WHERE clausule
      list($conditions, $a_params) = $this->prepare_where($w, $vars['t']);
      $this->dbh->sqliteCreateFunction("preg_match", "preg_match", 2);
      $WHERE = ($conditions!='') ? ' WHERE '.$conditions : '';
      
      // == prepare order
         $ORDER = $this->prepare_order($vars);

      // == prepare GROUP BY
         $GROUP_BY = !empty($vars['g']) ? 'GROUP BY '.($this->qf($vars['g'])) : '';

      // == prepare FIELDS list
      if (!isset($vars['f']) || trim($vars['f'])==''){
          $FIELDS = '*';
      }else{
          $FIELDS = $vars['f'];
          if (($key=='_id_' || preg_match('/_id_/',$ORDER)) && !preg_match('/_id_/',$vars['f']))
                  $FIELDS .= ', _id_';
      }

      // == prepare LIMIT clausule
        if (isset($vars['l1']) && trim($vars['l1'])!='' && isset($vars['l2']) && trim($vars['l2'])!=''){
            $l1 = max(array(intval($vars['l1'])-1, 0));
            $l2 = intval($vars['l2']) - $l1;
            $LIMIT = ' LIMIT '.$l1.','.$l2;
        }else{
            $LIMIT = '';
        }
        
      // == prepare and execute query
        $sql = 'SELECT '.$FIELDS.' FROM "' . $vars['t'] .'"'.$WHERE. ' '. $ORDER .' '. $GROUP_BY .' '. $LIMIT;
        $sth = $this->_prepare($sql,$a_params);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute($a_params);
      
      $a_records = array();
      $g = !empty($vars['g']) ? $vars['g'] : '';
      while( $record = $sth->fetch() ){
          if (!empty($g))
            $a_records[$record[$g]] = $record;
          else if (!empty($key))
            $a_records[$record[$key]] = $record;
          else
            $a_records[] = $record;  
      }
      
      $mc1 = $this->_microtime();
      //echo '<h1>'.($mc1 - $mc0).' ms</h1>';
      
      return $a_records;

   }

   function VACUUM($vars = NULL){
       if (is_null($vars) || empty($vars['t'])){
           $this->dbh->query("VACUUM;"); 
       }else if (is_array($vars['t'])){
           foreach($vars['t'] as $t){
               if (!empty($t))
               $this->dbh->query("VACUUM ".$t.";"); 
           }
       }else{
           $this->dbh->query("VACUUM ".$vars['t'].";"); 
       }
   }
   
// OLD (DEPRECATED!) EXTERNAL functions (but operative yet)**********************************************************************


   function DELETE_TABLE($table_name) {
      return $this->DROP_TABLE($table_name);
   }

   function CLOSE() {
      if (!empty($this->fp_busy) && file_exists($this->fp_busy)){
          @fclose($this->fp_busy);
      }
      return;
   }

   function GET_CAMPOS_TABLA($op1) {
      $a_fields = $this->FIELD_LIST($op1);
      $ret = '';
      if (is_array($a_fields) && count($a_fields) > 0) {
         foreach ($a_fields as $f => $arr2)
            $ret.=' ' . $f;
      }
      return trim($ret);
   }

   function GET_LISTADO_TABLAS() {
      return $this->TABLE_LIST();
   }

   function UPDATE_RECORD($table_name, $a_where = array(), $a_values = array()) {
      return $this->UPDATE(array('t' => $table_name, 'w' => $a_where, 'v' => $a_values));
   }

   function INSERT_RECORD($table_name, $a_field_values) {
      return $this->INSERT(array('t' => $table_name, 'v' => $a_field_values));
   }

   function INSERT_MULTIPLE($table_name, $a_rows) {
      return $this->MULTIPLE_INSERT(array('t' => $table_name, 'v' => $a_rows));
   }

   function DELETE_RECORD($table_name, $a_where) {
      return $this->DELETE(array('t' => $table_name, 'w' => $a_where));
   }

   function GET_LAST_RECORD_TABLE($table_name = '') {
      return $this->LAST_RECORD($table_name);
   }

   function GET_RECORDS_TABLE($table_name = "", $a_where = array()) {
      return $this->SELECT(array('t' => $table_name, 'w' => $a_where));
   }

   function EXPORT_TABLE_CSV($table_name,$a_rows='') {
      // 1. load the records of the table and fields
      if (empty($a_rows))
      $a_rows = $this->GET_RECORDS_TABLE($table_name);
      $a_fields = $this->FIELD_LIST($table_name);
      //die('<hr />rows=' . $this->_ve($a_rows));

      // 2. generate the content of the CSV file
        $content = ''; $a_line = array();
        foreach($a_fields as $f=>$arr) $a_line[] = '"'.str_replace('"','\"',$f).'"';
        $content .= implode(',',$a_line); unset($a_line);
        foreach($a_rows as $row){
            $a_line = array();
            foreach($a_fields as $f=>$arr) $a_line[] = '"'.str_replace('"','\"',$row[$f]).'"';
            $content .= "\n".implode(',',$a_line); unset($a_line);
        }

      // 3. save the content in a temporal file
        $fp=fopen($this->db_path . "table_".$table_name.".csv",'w+'); 
	fwrite($fp,$content); 
        fclose($fp);

      // 4. force the download of it
        header("Content-Disposition: attachment; filename=table_".$table_name.".csv");
        header("Content-type: application/force-download");
        readfile($this->db_path . "table_".$table_name.".csv");
      exit;
   }
   
   function EXPORT_TABLE($table_name) {
      // 1. get structure of the table
      $create_sentence = $this->GET_CREATE_TABLE($table_name);
      if (!$create_sentence)
         return false;

      // 2. load the records of the table and re-insert to the new table
      $a_rows = $this->GET_RECORDS_TABLE($table_name);
      //die('<hr />rows=' . $this->_ve($a_rows));

      // 3. create a new database with the new table 
      $ex = explode('.', $this->db_filename);
      $ex[0] .= '_temp';
      $db_temp_filename = implode('.',$ex);
      if (file_exists($this->db_path . $db_temp_filename)){
         unlink($this->db_path . $db_temp_filename);
      }
      copy('empty.sqlite',$this->db_path . $db_temp_filename);
      if (!$this->CONNECT_DATABASE($db_temp_filename)) die('Not possible to connect to temporal database: '.$db_temp_filename);
      $this->dbh->exec($create_sentence);

      // 4. insert the content of the table
      if (count($a_rows) > 0)
         $this->INSERT_MULTIPLE($table_name, $a_rows);

      // 5. reconnect with the original database 
      if (!$this->CONNECT_DATABASE('')) return false;

      // 6. force the download of it
      header("Content-Disposition: attachment; filename=" . $db_temp_filename);
      header("Content-type: application/force-download");
      readfile($this->db_path . $db_temp_filename);
      exit;
   }

   function IMPORT_DB($path,$filename) {
       // 1. connect to import database
            if (!$this->CONNECT_DATABASE($filename,$path)) die('Not possible to connect to temporal database: '.$filename.$path);
       
       // 2. load each one of the existing tables and its rows
            $tables_to_import = $this->TABLE_LIST();
            if (!is_array($tables_to_import) || count($tables_to_import)==0) return;
            foreach($tables_to_import as $tname=>$t){
                $tables_to_import[$tname]['create_sentence'] = $this->GET_CREATE_TABLE($t['nombre']);
                $tables_to_import[$tname]['new_name'] = $t['nombre'].'_imported_'.time();
                $tables_to_import[$tname]['create_sentence'] = str_replace(
                        array('CREATE TABLE '.$t['nombre'],'CREATE TABLE "'.$t['nombre'].'"'),
                        'CREATE TABLE '.$tables_to_import[$tname]['new_name'],
                        $tables_to_import[$tname]['create_sentence']);
                $tables_to_import[$tname]['rows'] = $this->SELECT(array('t'=>$t['nombre']));
            }

        // 3. create a new "imported" table for each one and insert the rows imported
            $this->CONNECT_DATABASE();
            foreach($tables_to_import as $tname=>$t){
                $this->dbh->exec($t['create_sentence']);
                $this->MULTIPLE_INSERT(array('t'=>$t['new_name'],'v'=>$t['rows']));
            }
    }

   function IMPORT_TABLE($table_name, $path_import_file) {
      return false;
      /*
       * this method is pending to upgrade to the SQLite version!!! :S
       * 
      $fp = fopen($path_import_file, "r");
      $temporal_name = $table_name . '_' . time();
      $primera_linea = fgets($fp, 10000);
      $temporal_file_name = 'SRR_t_' . $temporal_name . '.php';
      $exp_1 = explode('|', $primera_linea);
      $exp_2 = explode('.', $path_import_file);
      if (count($exp_1) == 3) {
         $this->CREATE_TABLE($temporal_name, explode(' ', stripslashes($exp_1[1]))); // creation of temporal table
         $this->tablas[$temporal_name]['num_records'] = intval($exp_1[2]);
         $this->tablas[$table_name]['num_records'] = intval($exp_1[2]);
         rename($path_import_file, $temporal_file_name);
         $this->reescribir_archivo_tablas();
         $records = $this->SELECT(array('t'=>$temporal_name));
         $id_ultimo = 0;
         if (count($records) > 0) {
            foreach ($records as $record) {
               if (intval($registro['_id_']) > $id_ultimo)
                  $id_ultimo = intval($registro['_id_']);
            }
         }
         $this->reescribir_tabla($table_name, 0, '');
         $this->DELETE_TABLE($temporal_name);
         $this->load_table_list();
      }else {
         $ret = 'Invalid file.';
      }
      return $ret;
       */
   }

// funciones INTERNAS ********************************************************************************************* 

   function _extract_field_array_from_create_table($create_sentence) {
      if (substr($create_sentence, 0, 13) !== 'CREATE TABLE ')
         return false;
      // = get array of fields
      $s_fields = substr(trim($create_sentence), strpos($create_sentence, '(') + 1, -1);
      $a_fields_1 = explode(',', $s_fields);
      $a_fields = array();
      if (count($a_fields_1) > 0) {
         foreach ($a_fields_1 as $ser) {
            $ex = explode(' ', trim($ser));
            if (count($ex) > 1) {
               $a_fields[$this->uqf($ex[0])] = $this->uqf($ex);
            }
         }
      }
      return $a_fields;
   }

   function f_implode_r($a, $s1 = '|', $s2 = '=') {
      $ret = '';
      if (is_array($a)) {
         if (count($a) > 0) {
            foreach ($a as $k => $v) {
               if ($ret != '')
                  $ret.='[' . $s1 . ']';
               $ret.=$k . '[' . $s2 . ']';
               if (is_array($v)) {
                  $ret.=$this->f_implode_r($v, $s1 . substr($s1, 0, 1), $s2 . substr($s2, 0, 1));
               } else {
                  // deactivate possible previous serialized
                  $ret.=str_replace('[', '&sdl791;', $v); // [|] -> &sdl1791;|]  ,  [===] -> &sdl1791;===]
               }
            }
         } else {
            $ret = '';
         }
      } else {
         // deactivate possible previous serialized
         $ret.=str_replace('[', '&sdl791;', $a); // [|] -> &sdl1791;|]  ,  [===] -> &sdl1791;===]
      }
      return $ret;
   }

   function f_explode_r($s, $s1 = '|', $s2 = '=') {
      $ex1 = explode('[' . $s1 . ']', $s); //c[==]1[||]d[==]f[===]0
      if (count($ex1) == 1 && strpos(' ' . $s, '[' . $s2 . ']') === false) {
         $ret = str_replace('&sdl791;', '[', $s);
      } else {
         $ret = array();
         foreach ($ex1 as $ex1s) {
            $ex2 = explode('[' . $s2 . ']', $ex1s);
            if (count($ex2) == 2) {
               $ret[trim($ex2[0])] = $this->f_explode_r($ex2[1], $s1 . substr($s1, 0, 1), $s2 . substr($s2, 0, 1));
            } else { //echo 'entra '.trim($ex2[0]).'  => '.trim($ex2[1]); 
               //$ret='';
            }
         }
      }
      return $ret;
   }

   function prepare_set($a_v, $tablename){
         if (!$a_v || empty($a_v) || !is_array($a_v) || count($a_v)==0) return '';
         
         $a_set = array();
         $a_params = array();
         foreach($a_v as $f => $v){
                $f = trim($f);
                if ($f=='') continue;
                $ff = str_replace('-','__',$f);
                $a_set[] = $this->qf($f) . '= :' . $ff;
                $a_params[':'.$ff] = $v;
         }
         
         $set_imploded = implode(', ', $a_set);
         return array($set_imploded, $a_params);
   }
   
   function prepare_where($a_w, $tablename) { 
      // find _op_ 
      // it can be something complex like: '1 and (2 or 3)' 
      // where 1, 2 & 3 will be replaced by the evaluation (true/false) of each condition within the conditions array
      // == prepare filters
      $a_params = array();
      $_op_ = '';
      $flag_op = 0;
      $_op_default = '';
      $conditions = array(); 
      if (is_array($a_w) && count($a_w) > 0) {
         foreach ($a_w as $var => $val) {
            if (trim($var) == '_op_') { // '_op_' => '1 && (2 || 3)' 
               $flag_op = 1;
               $_op_ = trim($val);
            } else {
               if (is_array($val)) { // $val=array('price','=','15');
                  $conditions[$var] = $val;
               } else { // $a_w = array('price'=>'15');
                  $conditions[$var] = array($var, '=', $val);
               }
               if ($_op_default != '')
                  $_op_default .= ' && ';
               $_op_default .= $var;
            }
         }
      }

      // == main loop
      if ($_op_ == '')
         $_op_ = $_op_default;
      
      if (count($conditions) > 0) {
         // wrap with <{}> all the keys of the conditions 
         // this avoid confuse: 2||3||4||24 with [2]||[3]||[4]||[2][4] and provide this: [2]||[3]||[4]||[24]
         // so we transform, eg: 2||( (3 & & 4)||24) -> <<{2}>>||((<<{3}>>&&<<{4}>>)||<<{24}>>)
         $_opt_ = trim($_op_);
         if ($_opt_ == ''){
             $ret = $records;
         }else{
            $il = strlen($_opt_);
            $_op_ = '';
            $last_c_is_op = true;
            for ($ic = 0; $ic < $il; $ic++) {
               $c = substr($_opt_, $ic, 1);
               if ($c == ' ' or $c == '') {
                  // do nothing
               } else if ($c == '|' or $c == '&' or $c == '(' or $c == ')') {
                  // is operator
                  if (!$last_c_is_op)
                     $_op_.='}>>';
                  $_op_.=$c;
                  $last_c_is_op = true;
               }else {
                  // is key_name
                  if ($last_c_is_op)
                     $_op_.='<<{';
                  $_op_.=$c;
                  $last_c_is_op = false;
               }
            }
            // add a last braket
            if (!$last_c_is_op)
               $_op_.='}>>';

            // == build SQLite WHERE clausule
            // == example: <<{2}>>||((<<{3}>>&&<<{4}>>)||<<{24}>>) -> size = :var2 OR ((age > :var3 AND age < :var4) OR classroom != :var24
                // == replace logical operators
                    $_op_ = str_replace( array('&&','||') , array(' AND ',' OR ') , $_op_);
                // == replace conditions & extract param values for be comparated
                    foreach($conditions as $var => $arr){
                        list($condition,$param) = $this->get_condition($arr[0], $arr[1], $arr[2], $tablename);
                        $_op_ = str_replace ('<<{'.$var.'}>>' , $condition, $_op_);
                        $a_params = array_merge($a_params, $param);
                    }
            
            
         }
      }else {
         $_op_ = '';
      }
        //echo '<hr /><h2>op= '.$_op_.'</h2><h2>'.var_export($a_params,true).'</h2>';
      return array($_op_,$a_params);
   }
 
   function prepare_order($vars){
        $ORDER = '';
        // == prepare direction(s) of order
            $direction = array();
            if (isset($vars['o2']) && trim($vars['o2'])!=''){
                $fields = explode(',',$vars['o2']); // ex: DESC , ASC , DESC
                foreach ($fields as $idf=>$dir){
                    $dir = strtoupper(trim($dir));
                    if (trim($dir)=='ASC') 
                        $direction[$idf] = $dir;
                    else
                        $direction[$idf] = 'DESC';
                }
            }
        // == prepare field(s) of order
            if (isset($vars['o']) && trim($vars['o'])!=''){
                $fields = explode(',',$vars['o']); // ex: date[ddmmyyyy , _id_
                $order = array();
                foreach ($fields as $idf=>$field){
                    $field = trim($field);
                    $ex = explode('[',$field);
                    if ($ex[0]=='') continue;
                    $ftit = $this->qf($ex[0]);
                    if(preg_match('/^_id_/i',$ex[0])){
                        $ORDER = $ftit;
                    }else if (!empty($ex[1])){
                        if ($ex[1] == 's' || $ex[1] == 'si') { // string insensitive
                           $ORDER = 'UPPER('.$ftit.')';
                        } else if ($ex[1] == 'ss') { //String Sensitive to capital letters
                           $ORDER = $ftit;
                        } else if ($ex[1] == 'n') { // integer without decimals and without thousands commas
                           $ORDER = 'CAST('.$ftit.' as integer)';
                        } else if ($ex[1] == 'n,.') { // numbers with "," for thousands and "." for decimals 
                           $ORDER = 'CAST(REPLACE('.$ftit.',",","") as real)';
                        }else if ($ex[1] == 'n.,') { // numbers with "." for thousands and "," for decimals 
                           $ORDER = 'CAST(REPLACE(REPLACE('.$ftit.',".",""),",",".") as real)';
                        }else if ($ex[1] == 'ddmmyy') { // dd/mm/yy or dd-mm-yy  or dd/mm/yy hh:ii....
                           $ORDER = '( SUBSTR('.$ftit.',7,2) || SUBSTR('.$ftit.',4,2) || SUBSTR('.$ftit.',1,2) || SUBSTR('.$ftit.',9) )';
                        }else if ($ex[1] == 'ddmmyyyy') { // dd/mm/yyyy or dd-mm-yyyy or dd/mm/yyyy hh:ii....
                           $ORDER = '( SUBSTR('.$ftit.',7,4) || SUBSTR('.$ftit.',4,2) || SUBSTR('.$ftit.',1,2) || SUBSTR('.$ftit.',11) )';
                        }else {
                            $ORDER = 'UPPER('.$ftit.') ';
                        }
                    }else{
                        $ORDER = 'UPPER('.$ftit.')';
                    }
                    if (!empty($ORDER) && !empty($direction[$idf]))
                        $order[] = $ORDER .' '.$direction[$idf];
                    else
                        $order[] = $ORDER .' '.( !empty($direction[0]) ? $direction[0] : 'DESC');
                }
                if (count($order)>0) $ORDER = ' ORDER BY ' . implode(' , ',$order);
            }
        return $ORDER;
   }
   
   function get_condition($v1, $op, $v2, $tablename) { // $v1=fieldname, $op=comparator, $v2=value
      // based on SQLite official operators: http://www.sqlite.org/lang_expr.html
      $condition = ''; 
      $param = array();
      $op = trim($op);
      $opc = str_replace('|','',$op); // we CLEAN the operator
      $a_comparators = array('=' => ' = ', '>' => ' > ', '<' => ' < ', '>=' => ' >= ', '<=' => ' <= ');
      $v1 = trim($v1);
      
      // special case: fieldname = *
      if ($v1=='*'){
        $a_fields = $this->FIELD_LIST($tablename);
        if (is_array($a_fields) && count($a_fields) > 0) {
           $a_condition = array();
           foreach ($a_fields as $f => $arr2){
               $res = $this->get_condition($f, $op, $v2, $tablename);
               $a_condition[] = $res[0];
               $param = array_merge( $param, $res[1]);
           }
              
           $condition = ' ( ' .implode(' OR ',$a_condition). ' ) ';
        }
        return array($condition,$param);
      }
      $v1_q = $this->qf($v1);
      $rand = '_'.rand(100000,999999);
      if ($opc=='=' && $v2===''){
          $condition = ' ( '. $v1_q . ' = "" OR '.$v1_q.' IS NULL ) ';
      }else if ($opc=='=' && ($v2==='NULL' || $v2===NULL)){
          $condition = $v1_q . ' IS NULL ';
      }else if ($op=='!='){
          if ($v2!=='NULL' && $v2!==NULL ){
            $condition = ' ( '. $v1_q . ' IS NULL OR '.$v1_q.' != :'.$v1.$rand.' ) ';
            $param = array(':'.$v1.$rand => $v2);
          }else{
            $condition = $v1_q . ' IS NOT NULL ';
          }
            
      }else if (isset($a_comparators[$opc])){
          if (preg_match('/\|/',$op)){
              $condition = 'UPPER('. $v1_q .') '. $a_comparators[$opc] . ' UPPER( :' . $v1.$rand.' )';
          }else{
              $condition = $v1_q . $a_comparators[$opc] . ' :' . $v1.$rand;
          }
          $param = array(':'.$v1.$rand => $v2);
          
      }else if ($op=='~' || $op=='~|'){
          $condition = " preg_match( :" .$v1.$rand. " , ". $v1_q .")";
          $i = preg_match('/\|/',$op) ? 'i' : ''; // insensitive
          $param = array(':'.$v1.$rand => '/'.preg_quote($v2,'/').'/'.$i);
          
      }else if ($op=='!~' || $op=='!~|'){
          $condition = " NOT preg_match( :" .$v1.$rand. " , ". $v1_q .")";
          $i = preg_match('/\|/',$op) ? 'i' : ''; // insensitive
          $param = array(':'.$v1.$rand => '/'.preg_quote($v2,'/').'/'.$i);
          
      }else if ($op=='~~'){
          $condition = " preg_match( :" .$v1.$rand. " , ". $v1_q .")";
          $param = array(':'.$v1.$rand => $v2);
          
      }else if ($op=='!~~'){
          $condition = " NOT preg_match( :" .$v1.$rand. " , ". $v1_q .")";
          $param = array(':'.$v1.$rand => $v2);
          
      }
      return array($condition,$param);
   }

   function get_num_records_table($table_name, $a_where='') {
      $count_rows = $this->SELECT(array('t'=>$table_name , 'w'=>$a_where, 'f'=>'COUNT(_id_) as n', 'k'=>''));
      return $count_rows[0]['n'];
   }

   function table_exist($table_name) {
      if ($table_name == '')
         return false;
      $sth = $this->dbh->query("SELECT * FROM sqlite_master WHERE type='table'");
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      while ($arr = $sth->fetch()) {
         if ($arr['name'] == $table_name)
            return true;
      }
      return false;
   }

   function load_table_list() {
      $this->tablas = array();
      $sth = $this->dbh->query("SELECT * FROM sqlite_master WHERE type='table'");
      $sth->setFetchMode(PDO::FETCH_ASSOC);
      while ($arr = $sth->fetch()) {
         $sqlite_fields = $this->_extract_field_array_from_create_table($arr['sql']);
         $n = $this->get_num_records_table($arr['name']);
         $lista_campos = array();
         if (is_array($sqlite_fields) && count($sqlite_fields) > 0) {
            foreach ($sqlite_fields as $arr2) {
               if ($arr2[0] != '_id_')
                  $lista_campos[] = $arr2[0];
            }
         }
         $this->tablas[$arr['name']] = array('nombre' => $arr['name'], 'sqlite_fields' => $sqlite_fields, 'lista_campos' => $lista_campos, 'num_records' => $n);
      }
   }

   /*
    * this is a development function for trim blank spaces after some importations from old versions of db :(
    * maybe it could be a good idea to check why i found that blank spaces and fix it
    */
   function trim_all_values($table_name='') {
            if (empty($table_name)) return;
         // == prepare update
            if (empty($this->tablas)) $this->load_table_list();	 	
            $a_set = array();
            foreach ($this->tablas[$table_name]['lista_campos'] as $f) {
               $ff = str_replace('-','__',$f);
               $a_set[] = $this->qf($f) . '= :' . $ff;
            }
            $set_implode = implode(', ', $a_set);
            $sql = 'UPDATE "' . $table_name . '" SET ' . $set_implode . ' WHERE '.$this->qf('_id_').'=:_id_';
            $sth = $this->_prepare($sql);
         // == execute update
            $records = $this->SELECT(array('t'=>$table_name));
            $this->dbh->beginTransaction();
            foreach ($records as $row) {
               $a_values = array();
               foreach ($row as $f => $v) {
                   $ff = str_replace('-','__',$f);
                   if ($f!='_id_')
                        $a_values[':'.$ff] = trim($v).'__';
                   else
                       $a_values[':'.$ff] = $v;
               }
               $sth->execute($a_values);
               unset($a_values);
            }
        // == commit
         $this->dbh->commit();
   }

   function empty_dir($dir, $b_recursive = 1, $a_exceptions = array()) { // function to empty any directories  
      $handle = opendir($dir);
      while (false !== ($readdir = readdir($handle))) {
         if ($readdir != '.' && $readdir != '..') {
            $path = $dir . '/' . $readdir;
            if (is_file($path)) {
               if (!in_array($readdir, $a_exceptions))
                  @unlink($path);
            } elseif (is_dir($path)) {
               if ($b_recursive == 1) {
                  f_empty_dir($path, $b_recursive);
               }
            }
         }
      }
      closedir($handle);
   }

   function _remove_odd_char($string) {
      return @iconv("UTF-8", "UTF-8//IGNORE", $string);
   }

   /*
    * qf() -> quote field names
    */
   function qf($f,$prefix=''){
        if (strpos($f,',')!==false){
            $ex = explode(',',$f);
            foreach($ex as $i=>$f_) $ex[$i] = '"'.$prefix.$f_.'"';
            return implode(',',$ex);
        }else{
            return '"'.$prefix.$f.'"'; 
        }
   }
   
   /*
    * uqf() -> unquote field names
    */
   function uqf($f){
       return str_replace('"','',$f);
   }
   
   // =================== debug functions ===================

   function _microtime() {
      // 0.41494500 1291000531 -> 1291000531.41494500
      list($usec, $sec) = explode(" ", microtime());
      return ((float) $usec + (float) $sec);
   }

   function _debug($msg, $op = "append", $IP = '') {//201.165.198.18 
      if ($IP != '' && $_SERVER['REMOTE_ADDR'] != $IP)
         return;
      if ($this->b_debug_on === false)
         return;
      switch ($op) {
         case 'append':
            $fp = fopen($this->debug_file, "a+");
            fwrite($fp, $msg . "<br />\n");
            fclose($fp);
            break;
         case 'clear':
            $fp = fopen($this->debug_file, "w+");
            fwrite($fp, $msg . "<br />\n");
            fclose($fp);
            break;
      }
   }
   
   function exception2html($exc){
        return   "<h3>Error on file <u>".$exc->getFile()."</u> (".$exc->getLine()."):</h3>"
                ."<h4>".$exc->getMessage()."</h4>"
                .str_replace("\n","<br />",$exc->getTraceAsString());
   }

    function _prepare($sql,$a_params=array()){
        try {
              $sth = $this->dbh->prepare($sql);
        } catch (Exception $exc) {
              echo $this->exception2html($exc);
              echo "<h3>$sql</h3>";echo _var_export($a_params);
              die();
        }
        return $sth;
    }

}

?>