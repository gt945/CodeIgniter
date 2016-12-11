<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );

class Db_Model extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->db = $this->load->database ('default', true);
	}
	
	public function __call($m, $a)
	{
		return call_user_func_array(array(&$this->db,$m), $a);
	}
	
	public function table($name, $select = array())
	{
        $this->name = $name;
        $this->primary = 'id';
//		$this->from("{$name} {$alias}");
	}
	
	public function prepare($join = true)
	{
	}
	
	public function sheet()
	{
		return $this->db->get ()->result_array ();
	}
	
	public function row()
	{
		return $this->db->get ()->row_array ();
	}
	
	public function col($colname)
	{
		$result = array();
		$data = $this->db->get ()->result_array ();
		
		if (count($data) && isset($data[0][$colname])) {
			foreach($data as $d) {
				$result[] = $d[$colname];
			}
		}
		return $result;
	}
	
	public function cell($colname)
	{
		$result = null;

		$data = $this->db->get ()->row_array ();

		if (count($data) && isset($data[$colname]) ) {
				$result = $data[$colname];
		}
		return $result;
	}

	public function save($data) {
	    $ret = array();
        $this->trans_start ();
        foreach($data as $d) {
            if (isset($d[$this->primary])) {
                $ret[] = $d[$this->primary];
                $this->db2->where($this->primary, $d[$this->primary]);
                $this->db2->update ($this->name, $d);
            } else {
                $this->db2->insert ($this->name, $d);
                $ret[] = $this->db2->insert_id ();
            }
        }
        $this->trans_complete ();
        return $ret;
    }

}
