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
		$this->selected = $select;
		$this->cached = false;
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

	public function save($data)
	{
		$ret = array();
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
		return $ret;
	}
	
	public function parse($filters, $start=true, $alias="b")
	{
		$is_blank = true;
		if ($start) {
			$this->group_start();
		}
		
		foreach ($filters->rules as $r) {
			if ( isset($r->field) /*&& (isset($this->fields[$r->field]))
				&& ($r->field == $this->primary
					|| ($this->crud_field[$r->field]['prop'] & Crud_model::PROP_FIELD_FILTER)
				)*/
			) {
				$this->_parse_rules($filters->groupOp, $r->field, $r->op, $r->data, $alias);
				$is_blank = false;
			}
		}
		
		if (isset($filters->groups) && count ($filters->groups) != 0) {
			$is_blank = false;
			foreach ($filters->groups as $g) {
				if ($filters->groupOp === "OR") {
					$this->or_group_start();
				} else {
					$this->group_start();
				}
				$this->parse($g, false, $alias);
				$this->group_end();
			}
		}
		if ($is_blank) {
			$this->where('1 =', 1);
		}
		if ($start) {
			$this->group_end();
		}
		
		
	}
	
	private function _parse_rules($groupOp, $field, $op, $data, $alias = "b")
	{
		if ($groupOp === "OR") {
			$func = "or_";
		} else {
			$func = "";
		}
		$side_array = array (
				1 => 'before',
				2 => 'after',
				3 => 'both'
		);
		$opt = array (
				"eq" => "=",
				"ne" => "<>",
				"lt" => "<",
				"le" => "<=",
				"gt" => ">",
				"ge" => ">=",
				"cn" => "LIKE",
				"nc" => "NOT LIKE",
				"bw" => "LIKE",
				"bn" => "NOT LIKE",
				"ew" => "LIKE",
				"en" => "NOT LIKE",
				"in" => "IN",
				"ni" => "NOT IN",
				"nu" => "IS NULL",
				"nn" => "IS NOT NULL",
		);
		if (!isset($opt[$op])) {
			die("无效的关系符");
		}
		switch($op) {
			case "nn" :
				$func .= "where";
				$this->$func ( "{$alias}.{$field} is not null" );
				break;
			case "nu" :
				$func .= "where";
				$this->$func ( "{$alias}.{$field}" );
				break;
			case "ni" :
				$func .= "where_not_in";
				$array_in = explode(',', $data);
				$this->$func ( "{$alias}.{$field}", $array_in );
				break;
			case "in" :
				$func .= "where_in";
				$array_in = explode(',', $data);
				$this->$func ( "{$alias}.{$field}", $array_in );
				break;
			case "nc" :
			case "en" :
			case "bn" :
				$func .= "not_";
			case "cn" :
				//$func .= "where";
				//$this->$func ( "match(b.{$field}) against('{$data}' IN BOOLEAN MODE )" );
				//break;
			case "ew" :
			case "bw" :
				$side = 0;
				if ($op == 'nc' || $op == 'cn') {
					$side = 3;
				} else if ($op == 'bw' || $op == 'bn') {
					$side = 2;
				} else if ($op == 'ew' || $op == 'en') {
					$side = 1;
				}
				$func .= "like";
				$this->$func ( "{$alias}.{$field}", $data, $side_array [$side] );
				break;
			default :
				$func .= "where";
				$this->$func ( "{$alias}.{$field} {$opt[$op]}", $data );
		}
	}
	
	public function parse_relate($value, $require, $type = null, $field = null, $table = null, $caption = null, $replace = null)
	{
		$rules = array();
		if($type == null) {
			$type = "eq";
		}
		if($field == null) {
			$field = $require;
		}
		if($type == 'JN') {
			$this->db2->where($field, $value);
			$this->db2->from($table);
			$value = $this->db2->cell($caption);
			$this->where($replace, $value);
		} else {
			$this->_parse_rules("AND", $field, $type, $value, "a");
		}
	}

}
