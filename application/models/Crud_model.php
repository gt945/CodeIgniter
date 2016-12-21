<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
include_once(APPPATH.'models/Db_model.php');

class Crud_model extends Db_Model {
	const TYPE_LABEL        = 0;
	const TYPE_NUMBER       = 1;
	const TYPE_DATE         = 2;
	const TYPE_TIME         = 3;
	const TYPE_DATETIME     = 4;
	const TYPE_SELECT       = 5;
	const TYPE_MULTI        = 6;
	const TYPE_BOOL         = 7;
	const TYPE_TEXTAREA     = 8;
	const TYPE_PASSWORD     = 9;
	const TYPE_BIT          = 10;
	const TYPE_AUTOCOMPLETE = 11;
	const TYPE_HELPER       = 12;

	const TYPE_BUTTON       = 100;
	const TYPE_WIDGET       = 101;

	const ALIGN_LEFT        = 0;
	const ALIGN_CENTER      = 1;
	const ALIGN_RIGHT       = 2;
	
	const PROP_FIELD_PRIMARY    = 0x0001;
	const PROP_FIELD_FILTER     = 0x0002;
	const PROP_FIELD_HIDE       = 0x0004;
	const PROP_FIELD_UNIQUE     = 0x0008;
	const PROP_FIELD_READONLY   = 0x0010;
	const PROP_FIELD_SORT       = 0x0020;
	const PROP_FIELD_MINIFY     = 0x0040;
	const PROP_FIELD_ADVANCE    = 0x0080;
	const PROP_FIELD_TABLE      = 0x0100;
	const PROP_FIELD_CURRENCY   = 0x0200;
	const PROP_FIELD_REQUIRED   = 0x0400;
    const PROP_FIELD_VIRTUAL    = 0x0800;
    const PROP_FIELD_STRING     = 0x1000;

	const PROP_TABLE_EXPORT = 0x0001;
	const PROP_TABLE_IMPORT = 0x0002;

	const SEARCH_OPTION_EQ = 0x0001;
	const SEARCH_OPTION_NE = 0x0002;
	const SEARCH_OPTION_LT = 0x0004;
	const SEARCH_OPTION_LE = 0x0008;
	const SEARCH_OPTION_GT = 0x0010;
	const SEARCH_OPTION_GE = 0x0020;
	const SEARCH_OPTION_CN = 0x0040;
	const SEARCH_OPTION_NC = 0x0080;
	const SEARCH_OPTION_BW = 0x0100;
	const SEARCH_OPTION_BN = 0x0200;
	const SEARCH_OPTION_EW = 0x0400;
	const SEARCH_OPTION_EN = 0x0800;
	const SEARCH_OPTION_IN = 0x1000;
	const SEARCH_OPTION_NI = 0x2000;
	const SEARCH_OPTION_NU = 0x4000;
	const SEARCH_OPTION_NN = 0x8000;
	
	const CRUD_TABLE = 'crud_table';
	const CRUD_FIELD = 'crud_field';
	

	public function __construct()
	{
		parent::__construct ();
		$this->load->model( 'auth_model' );
		$this->load->model ('db_model', "db2");
	}
	
	public function table($name, $select = array())
	{
		
		$crud_field = array();
		$fields = array();
		
		$this->db2->where ( 'name', $name );
		$this->db2->from ( Crud_model::CRUD_TABLE );
		$crud_table = $this->db2->row();
		if (!$crud_table) {
            $this->db2->where ( 'id', $name );
            $this->db2->from ( Crud_model::CRUD_TABLE );
            $crud_table = $this->db2->row();
		}
        if (!$crud_table) {
            $this->db2->where ( 'caption', $name );
            $this->db2->from ( Crud_model::CRUD_TABLE );
            $crud_table = $this->db2->row();
        }
		if ($crud_table) {
            parent::table($crud_table['name'], $select);
			$crud_table['_role_c'] = $this->auth_model->check_role($crud_table['role_c'] );
			$crud_table['_role_r'] = $this->auth_model->check_role($crud_table['role_r'] );
			$crud_table['_role_u'] = $this->auth_model->check_role($crud_table['role_u'] );
			$crud_table['_role_d'] = $this->auth_model->check_role($crud_table['role_d'] );
			

			$this->db2->where ( 'tid', $crud_table ['id'] );
			$this->db2->order_by ( 'seq', 'asc' );
			$this->db2->from (Crud_model::CRUD_FIELD);
			$crud_field = $this->db2->sheet();
			foreach ( $crud_field as $k => $f ) {
				$f['_role_r'] = $this->auth_model->check_role($f['role_r']);
				$f['_role_u'] = $this->auth_model->check_role($f['role_u']);
				$fields[$f ['name']] = true;
				unset ( $crud_field [$k] );
				if($f['prop'] & Crud_model::PROP_FIELD_PRIMARY) {
					$this->primary = $f['name'];
				} else if(count($select)
					&& !in_array($f['name'], $select)
					&& !in_array(strtolower($f['name']), $select)) {
					continue;
				}
				$crud_field [$f ['name']] = $f;
			}
		}
		if (is_array($crud_table) && is_array($crud_field)) {
			$this->tree_code = $_SESSION['groupinfo']['tree_code'];
			$this->crud_table = $crud_table;
			$this->crud_field = $crud_field;
			$this->fields = $fields;
			$this->prepared = false;
			if ($crud_table['pid_field'] === '') {						//TreeGrid flag
				$this->pid = null;
			} else {
				$this->pid = $crud_table['pid_field'];
			}
			if (isset($crud_field['gid']) && $name !== 'user_group') {	//user group
					$this->group = $this->get_group_ids($_SESSION['userinfo']['gid'], true);
//                 $dbContextGroup = $this->table("user_group");
// 					$this->groupTree = $this->get_tree_data_by_id($dbContextGroup, $_SESSION['userinfo']['gid'], true);
// 					$this->groupTreeOption = $this->get_tree_option($this->groupTree, 'id', 'groupname');
			} else {
				$this->group = null;
			}
			if (isset($crud_field['uid'])) {
				$this->user = true;
			} else {
				$this->user = false;
			}
			if (isset($crud_field['tree_code'])) {						//TreeCode flag
				$this->tree = true;
			} else {
				$this->tree = false;
			}

		} else {
			return false;
		}
		
		return true;
	}
	
	public function prepare($join = true)
	{
		parent::prepare($join);
		if ($this->prepared) {
			return true;
		}
		$table = $this->name;

		$this->start_cache();
		
		$this->from("{$table} a");
// 		if ($this->group) {
// 			$this->where_in("a.gid", $this->group);
// 		}
		
		if ($this->user) {
			$this->where("a.uid", $_SESSION['userinfo']['id']);
		}
		
		$i = 1;
		foreach ( $this->crud_field as &$f ) {
			if ($f['type'] >= Crud_model::TYPE_BUTTON ||  ($f['prop'] & Crud_model::PROP_FIELD_VIRTUAL) ) {
				continue;
			}
			$this->select ( "a.{$f['name']}" );
			if ($join) {
				switch($f ['type']) {
					case Crud_model::TYPE_SELECT:
						if ($f['join_caption'] == ''){
							continue;
						}
						$this->select ( "a{$i}.{$f['join_caption']} r{$i}" );
						if ($f ['join_condition'] != '' && $f ['join_condition_value'] != '') {
							$this->join ( "{$f['join_table']} a{$i}", "a.{$f['name']}=a{$i}.{$f['join_value']} and a{$i}.{$f['join_condition']} = \"{$f['join_condition_value']}\"", 'LEFT' );
						} else {
							$this->join ( "{$f['join_table']} a{$i}", "a.{$f['name']}=a{$i}.{$f['join_value']}", 'LEFT' );
						}
					case Crud_model::TYPE_MULTI:
					case Crud_model::TYPE_BIT:
						$f ['_caption'] = "r{$i}";
						break;
					default:
						break;
				}
			}
			$i++;
		}
		
		$this->prepared = true;
		return true;
	}
	
	public function install($table)
	{
		if (!$this->auth_model->check_role(1)){
			return 0;
		}
		if (!$this->table_exists($table)) {
            return 0;
        }
		$fields = $this->field_data ( $table );						//get all fields in table
// 		echo "<pre>";
// 		print_r($fields);
// 		echo "</pre>";
		if (count ( $fields ) > 0) {
			$this->select ( 'id' );
			$this->where ( 'name', $table );
				
			$tid = $this->get ( Crud_model::CRUD_TABLE )->row ( 'id' );
			if (! $tid) {
				$data = array (
						'name' => $table,
						'caption' => $table
				);
				if ($table == "user_group") {
					$data['pid_field'] = 'gid';
				}
				foreach ( $fields as $f ) {
					if ($f->name === 'pid') {							//if table have pid field, set pid_field
						$data['pid_field'] = 'pid';
					}
				}
				$this->insert ( Crud_model::CRUD_TABLE, $data );
				$tid = $this->insert_id ();
			}
			if ($tid) {
				$this->select ( 'id,name' );
				$this->where ( 'tid', $tid );						//get all fields in crud_field table
				$exist_fields = $this->get ( Crud_model::CRUD_FIELD )->result_array ();
	
				$this->trans_start ();
				foreach ( $fields as $f ) {
					$find = false;
					foreach ( $exist_fields as $k => $ef ) {			//find fields not in crud_field table
						if ($ef ['name'] === $f->name) {
							$find = true;
							unset ( $exist_fields [$k] );
							break;
						}
					}
					if (! $find) {										//append to crud_field
						$data = array (
								'tid' => $tid,
								'name' => $f->name,
								'caption' => $f->name,
								'width' => 80,
								'prop' => Crud_model::PROP_FIELD_HIDE,
								'search_option' => Crud_model::SEARCH_OPTION_EQ | Crud_model::SEARCH_OPTION_NE
						);
						switch ($f->type) {
							case "int":
								$data['type'] = Crud_model::TYPE_NUMBER;
								break;
							case "varchar":
								$data['type'] = Crud_model::TYPE_LABEL;
								break;
							case 'time':
								$data['type'] = Crud_model::TYPE_TIME;
								break;
							case "datetime":
								$data['type'] = Crud_model::TYPE_DATETIME;
								$data ['prop'] |= Crud_model::PROP_FIELD_SORT;
								$data['search_option'] |= Crud_model::SEARCH_OPTION_LT | Crud_model::SEARCH_OPTION_LE | Crud_model::SEARCH_OPTION_GT | Crud_model::SEARCH_OPTION_GE;
								break;
							case "date":
								$data['type'] = Crud_model::TYPE_DATE;
								$data ['prop'] |= Crud_model::PROP_FIELD_SORT;
								$data['search_option'] |= Crud_model::SEARCH_OPTION_LT | Crud_model::SEARCH_OPTION_LE | Crud_model::SEARCH_OPTION_GT | Crud_model::SEARCH_OPTION_GE;
								
								break;
						}
						if ($f->name === 'id') {
							$data ['type'] = Crud_model::TYPE_NUMBER;
							$data ['prop'] |= Crud_model::PROP_FIELD_PRIMARY;
						}
	
						if ($f->name === 'uid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'username';
							$data ['prop'] |= Crud_model::PROP_FIELD_SORT;
						}
						if ($f->name === 'gid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_group';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'groupname';
							$data ['prop'] |= Crud_model::PROP_FIELD_SORT;
						}
						if ($f->name === 'rid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_role';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'rolename';
						}
						if ($f->name === 'tree_code') {
							$data ['type'] = Crud_model::TYPE_LABEL;
							$data ['prop'] |= Crud_model::PROP_FIELD_HIDE;
						}
						if ($f->name === 'pid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = $table;
							$data['join_value'] = 'id';
							$data['join_caption'] = 'id';
						}
						if ($f->name === 'password') {
							$data ['type'] = Crud_model::TYPE_PASSWORD;
						}
						$this->insert ( Crud_model::CRUD_FIELD, $data );
					}
				}
				foreach ( $exist_fields as $k => $ef ) {				//delete not exist field
					if ($ef['type'] >= Crud_model::TYPE_BUTTON || ($ef['prop'] & Crud_model::PROP_FIELD_VIRTUAL)) {
                        continue;
                    }
                    $this->where ( 'id', $ef ['id'] );
					$this->delete ( Crud_model::CRUD_FIELD );
				}
				$this->trans_complete ();
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	public function get_group_ids($gid, $sub_tree = false)
	{
		$cache_id = "user_group_ids_{$gid}";
		
		
		//$db = $this->load->database ('default', true);
// 		if ( ! $result = $this->cache->get($cache_id)) {
			
			$this->db2->select('tree_code');
			$this->db2->from('user_group');
			$this->db2->where('id', $gid);
			$tree_code = $this->db2->cell('tree_code');
			
			if (!$tree_code) {
				return null;
			}
			$this->db2->select('id');
			$this->db2->from('user_group');
			if ($sub_tree) {
				$this->db2->like('tree_code', $tree_code, 'after');
			} else {
				$this->db2->where('tree_code', $tree_code);
			}
			$data = $this->db2->sheet();
			$result = array();
			foreach ($data as $d) {
				array_push($result, $d['id']);
			}
			if (!count($result)) {
				return null;
			}
			
// 			$this->cache->save($cache_id, $result);
// 		}
		return $result;
	}
	
	public function get_group_tree_code($gid)
	{
		
		
		$this->db2->select('tree_code');
		$this->db2->from('user_group');
		$this->db2->where('id', $gid);
		$tree_code = $this->db2->cell('tree_code');
		return $tree_code;
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

	public function parse($filters, $start=true)
	{
		$is_blank = true;
		if ($start) {
			$this->group_start();
		}
		
		foreach ($filters->rules as $r) {
			if ( (isset($this->fields[$r->field]))
				/*&& ($r->field == $this->primary
					|| ($this->crud_field[$r->field]['prop'] & Crud_model::PROP_FIELD_FILTER)
				)*/
			) {
				$this->_parse_rules($filters->groupOp, $r->field, $r->op, $r->data);
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
				$this->parse($g, false);
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

	public function get_tree_data_by_id($id = 0, $prepare = false)
	{
		return $this->_get_tree_data_by_id($id, $prepare);
	}

	private function _get_tree_data_by_id($id = 0, $prepare = false)
	{

		$this->stash_cache();
		$this->where("a.id", $id);
		$data = $this->get()->row_array();
		$this->pop_cache();
		if ($data) {
			$data['children'] = $this->_get_tree_data_by_pid($id);
		}
		return $data;
	}

	public function get_tree_data_by_pid($pid = 0, $prepare = false)
	{
		return $this->_get_tree_data_by_pid($pid, $prepare);
	}

	private function _get_tree_data_by_pid($pid = 0, $prepare = false)
	{
		if ($prepare) {
			$this->prepare();
		}
		$this->stash_cache();
		$this->where("a.{$this->pid}", $pid);
		$data = $this->sheet();
		$this->pop_cache();
		foreach ($data as &$d) {
			$d['children'] = $this->get_tree_data_by_pid($d['id']);
		}
		return $data;
	}

	public function check_pid_confilct($ids, $pid)
	{
		
		
		while($pid) {
			$this->db2->select("id,{$this->pid}");
			$this->db2->from($this->name);
			$this->db2->where('id', $pid);
			$data = $this->db2->row();
			$pid = $data[$this->pid];
			if (array_search($data['id'], $ids, false) !== false) {
				return false;
			}
		}
		return true;
	}

	public function build_all_tree_code($table, $id = 1, $pid_field = 'pid')
	{
		
		
		$this->db2->trans_start ();
		$this->db2->select("id,{$pid_field},tree_code");
		$this->db2->from($table);
		$this->db2->where('id' , $id);
		$data = $this->db2->row();
		if ($data) {
			if ($data[$pid_field] == 0) {
				$new_tree_code = "{$data['id']},";
			} else {
				$this->db2->select("id,{$pid_field},tree_code");
				$this->db2->from($table);
				$this->db2->where('id' , $data[$pid_field]);
				$parent_data = $this->db2->row();
				$new_tree_code = "{$parent_data['tree_code']}{$data['id']},";
			}
			if (strcmp ($data['tree_code'],  $new_tree_code)) {
				$this->db2->set('tree_code', $new_tree_code);
				$this->db2->where('id', $id);
				$this->db2->update($table);
			}
			$this->build_children_tree_code($table, $id, $new_tree_code, $pid_field);
		}
		$this->db2->trans_complete ();
	}

	public function build_children_tree_code($table, $pid, $tree_code, $pid_field = 'pid')
	{
		
		
		$this->db2->select("id,{$pid_field},tree_code");
		$this->db2->from($table);
		$this->db2->where($pid_field , $pid);
		$data = $this->db2->sheet();
		foreach($data as &$d) {
			$new_tree_code = "{$tree_code}{$d['id']},";
			if (strcmp ($d['tree_code'],  $new_tree_code)) {
				$this->db2->set('tree_code', $new_tree_code);
				$this->db2->where('id', $d['id']);
				$this->db2->update($table);
			}
			$this->build_children_tree_code($table, $d['id'], $new_tree_code, $pid_field);
		}
	}
}
