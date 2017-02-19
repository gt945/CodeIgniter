<?php
defined('BASEPATH') or exit ('No direct script access allowed');

class Db_Model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->db3 = $this->load->database('default', true);
	}

	public function __call($m, $a)
	{
		return call_user_func_array(array(&$this->db3, $m), $a);
	}

	public function table($name, $select = array())
	{
		$this->name = $name;
		$this->primary = 'id';
		$this->selected = $select;
		$this->cached = false;
		$this->cache = array();
	}

	public function prepare()
	{

	}
	public function save($datas)
	{
		$ret = array();
		foreach ($datas as $d) {
			if (!is_array($d)) {
				$datas = array(
					$datas
				);
			}
			break;
		}
		foreach ($datas as $d) {
			if (isset($d[$this->primary])) {
				$ret[] = $d[$this->primary];
				$this->db->where($this->primary, $d[$this->primary]);
				$this->db->update($this->name, $d);
			} else {
				$this->db->insert($this->name, $d);
				$ret[] = $this->db->insert_id();
			}
		}
		return $ret;
	}

	public function parse($filters, $start = true, $alias = "b")
	{
		$is_blank = true;
		if ($start) {
			$this->group_start();
		}

		foreach ($filters->rules as $r) {
			if (isset($r->field)) {
				if (isset($this->fields) && !isset($this->fields[$r->field])) {			 //字段不存在
					continue;
				}
				if (isset($this->crud_field) && !$this->crud_field[$r->field]['_role_r']) {			 //没有读权限
					continue;
				}
				$this->parse_rules($filters->groupOp, $r->field, $r->op, $r->data, $alias);
				$is_blank = false;
			}
		}

		if (isset($filters->groups) && count($filters->groups) != 0) {
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

	public function parse_rules($groupOp, $field, $op, $data, $alias = "b")
	{
		if ($groupOp === "OR") {
			$func = "or_";
		} else {
			$func = "";
		}
		$side_array = array(
			1 => 'before',
			2 => 'after',
			3 => 'both'
		);
		$opt = array(
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
		switch ($op) {
			case "nn" :
				$func .= "where";
				$this->$func ("{$alias}.{$field} is not null");
				break;
			case "nu" :
				$func .= "where";
				$this->$func ("{$alias}.{$field}");
				break;
			case "ni" :
				$func .= "where_not_in";
				$array_in = explode(',', $data);
				$this->$func ("{$alias}.{$field}", $array_in);
				break;
			case "in" :
				$func .= "where_in";
				$array_in = explode(',', $data);
				$this->$func ("{$alias}.{$field}", $array_in);
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
				$this->$func ("{$alias}.{$field}", $data, $side_array [$side]);
				break;
			default :
				$func .= "where";
				$this->$func ("{$alias}.{$field} {$opt[$op]}", $data);
		}
	}

	public function parse_relate($value, $require, $type = null, $field = null, $table = null, $caption = null, $replace = null)
	{
		if ($type == null) {
			$type = "eq";
		}
		if ($field == null) {
			$field = $require;
		}
		if ($type == 'JN') {
			$this->join ( "{$table} b", "b.{$field}='{$value}'", 'LEFT' );
			$this->where("b.{$caption} = `{$replace}`");
		} else {
			$this->parse_rules("AND", $field, $type, $value, "a");
		}
	}
}
