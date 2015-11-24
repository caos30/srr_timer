<?php 
		
class Test{
    
    var $config;
    var $db;
    var $tmp_table;
    var $spent_time;
    
    function Test($config){
        $this->config = $config;
        $this->db = new class_aSQLite(array(
                        'db_path' => $config['db_path'],
                        'db_filename'=>$config['db_filename']
                                        ));
    }
    
    function create_random_data($n_records=10000){
        $runtime1 = $this->_microtime();
        $this->tmp_table = 'test_'.rand(1,10000);
        $this->db->CREATE_TABLE($this->tmp_table, array('a','b'));
        $a_rows = array();
        for($ii=0 ; $ii<$n_records ; $ii++){
            $a_rows[] = array('a'=>rand(1,1000),'b'=>rand(1,1000));
        }
        $this->db->MULTIPLE_INSERT(array('t'=>$this->tmp_table,'v'=>$a_rows));
        $runtime2 = $this->_microtime();
        return array(round(($runtime2 - $runtime1),2), $this->tmp_table, $n_records);
    }
    
    function edit_random_data($tablename, $n_records=100){
        $runtime1 = $this->_microtime();
        for($ii=0 ; $ii<$n_records ; $ii++){
            $a1 = rand(1,1000);
            $this->db->UPDATE(array('t'=>$tablename, 'w'=>array('_op_'=>'1 && 2',array('a','>',$a1),array('a','<',($a1+20)) ), 'v'=>array('c'=>rand(1,1000))));
        }
        $runtime2 = $this->_microtime();
        return array(round(($runtime2 - $runtime1),2), $this->tmp_table, $n_records);
    }
    
    function _microtime(){
      // 0.41494500 1291000531 -> 1291000531.41494500
      list($usec, $sec) = explode(" ", microtime());
      return ((float)$usec + (float)$sec);
    }
    
}

?>
