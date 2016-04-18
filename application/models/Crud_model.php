<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );

/**
 * Crud_model class.
 *
 * @extends CI_Model
 */
class Crud_model extends CI_Model {

	var $dbContext = null;
	const TYPE_LABEL = 0;
	const TYPE_NUMBER = 1;
	const TYPE_DATE = 2;
	const TYPE_TIME = 3;
	const TYPE_DATETIME = 4;
	const TYPE_SELECT = 5;
	const TYPE_MULTI = 6;
	const TYPE_BOOL = 7;
	const TYPE_TEXTAREA = 8;
	const TYPE_PASSWORD = 9;
	const TYPE_BIT = 10;
	const TYPE_REF = 11;
	
	const ALIGN_LEFT = 0;
	const ALIGN_CENTER = 1;
	const ALIGN_RIGHT = 2;
	
	const PROP_FIELD_PRIMARY = 0x0001;
	const PROP_FIELD_FILTER = 0x0002;
	const PROP_FIELD_HIDDEN = 0x0004;
	const PROP_FIELD_UNIQUE = 0x0008;
	const PROP_FIELD_SORT = 0x0010;
	
	const CRUD_TABLE = 'crud_table';
	const CRUD_FIELD = 'crud_field';
	
	public function __construct() {
		parent::__construct ();
		$this->load->database ();
		$this->load->helper ( 'bool' );
		$this->load->library( 'session' );
		$this->load->driver('cache', array('adapter' => 'file', 'key_prefix' => "u{$_SESSION['userinfo']['id']}_"));
	}
	public function install($table) {
		$fields = $this->db->field_data ( $table );						//get all fields in table
		echo "<pre>";
		print_r($fields);
		echo "</pre>";
		if (count ( $fields ) > 0) {
			$this->db->select ( 'id' );
			$this->db->where ( 'name', $table );
				
			$tid = $this->db->get ( Crud_model::CRUD_TABLE )->row ( 'id' );
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
				$this->db->insert ( Crud_model::CRUD_TABLE, $data );
				$tid = $this->db->insert_id ();
			}
			if ($tid) {
				$this->db->select ( 'id,name' );
				$this->db->where ( 'tid', $tid );						//get all fields in crud_field table
				$exist_fields = $this->db->get ( Crud_model::CRUD_FIELD )->result_array ();
	
				$this->db->trans_start ();
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
								'prop' => 0,
								'search_option' => 0x0003
						);
						switch ($f->type) {
							case "int":
								$data['type'] = Crud_model::TYPE_NUMBER;
								break;
							case "varchar":
								$data['type'] = Crud_model::TYPE_LABEL;
								break;
							case "datetime":
								$data['type'] = Crud_model::TYPE_DATETIME;
								break;
							case "date":
								$data['type'] = Crud_model::TYPE_DATE;
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
						}
						if ($f->name === 'gid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_group';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'groupname';
						}
						if ($f->name === 'rid') {
							$data ['type'] = Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_role';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'rolename';
						}
						if ($f->name === 'tree_code') {
							$data ['type'] = Crud_model::TYPE_NUMBER;
							$data ['prop'] |= Crud_model::PROP_FIELD_HIDDEN;
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
						$this->db->insert ( Crud_model::CRUD_FIELD, $data );
					}
				}
				foreach ( $exist_fields as $k => $ef ) {				//delete not exist field
					$this->db->where ( 'id', $ef ['id'] );
					$this->db->delete ( Crud_model::CRUD_FIELD );
				}
				$this->db->trans_complete ();
			} else {
				// TODO: ERROR: insert error
			}
		} else {
			// TODO: ERROR: no such table
		}
	}
	
	public function check_role($role)
	{
		$rid = $_SESSION['userinfo']['rid'];
		return check_str_bool($role, $rid);
	}
	public function context()
	{
		$dbContext = new stdClass();
		$dbContext->db = $this->load->database ('default', true);
		return $dbContext;
	}
	public function table($name) {
		$cache_id = "table_{$name}";
		if ( ! $dbContext = $this->cache->get($cache_id)) {
			$this->db->select ( '*' );
			$this->db->where ( 'name', $name );
			$crud_field = array();
			$crud_table = $this->db->get ( Crud_model::CRUD_TABLE, 1 )->row_array ();
			if ($crud_table) {
				$crud_table['_role_c'] = $this->check_role($crud_table['role_c'] );
				$crud_table['_role_r'] = $this->check_role($crud_table['role_r'] );
				$crud_table['_role_u'] = $this->check_role($crud_table['role_u'] );
				$crud_table['_role_d'] = $this->check_role($crud_table['role_d'] );
				$this->db->select ( '*' );
				$this->db->where ( 'tid', $crud_table ['id'] );
				$this->db->order_by ( 'seq', 'asc' );
				$crud_field = $this->db->get ( Crud_model::CRUD_FIELD )->result_array ();
				foreach ( $crud_field as $k => $f ) {
					$f['_role_r'] = $this->check_role($f['role_r']);
					$f['_role_u'] = $this->check_role($f['role_u']);
					$crud_field [$f ['name']] = $f;
					unset ( $crud_field [$k] );
				}
			}
			if (is_array($crud_table) && is_array($crud_field)) {
				$dbContext = new stdClass();
				$dbContext->tree_code = $_SESSION['groupinfo']['tree_code'];
				$dbContext->crud_table = $crud_table;
				$dbContext->crud_field = $crud_field;
				$dbContext->name = $name;
				$dbContext->prepared = false;
				if ($crud_table['pid_field'] === '') {						//TreeGrid flag
					$dbContext->pid = null;
				} else {
					$dbContext->pid = $crud_table['pid_field'];
				}
				if (isset($crud_field['gid']) && $name !== 'user_group') {	//user group
					$dbContext->group = $this->get_group_ids($_SESSION['userinfo']['gid'], true);
					$dbContextGroup = $this->table("user_group");
					$dbContext->groupTree = $this->get_tree_data_by_id($dbContextGroup, $_SESSION['userinfo']['gid'], true);
					$dbContext->groupTreeOption = $this->get_tree_option($dbContext->groupTree, 'id', 'groupname');
				} else {
					$dbContext->group = null;
				}
				if (isset($crud_field['uid'])) {
					$dbContext->user = true;
				} else {
					$dbContext->user = false;
				}
				if (isset($crud_field['tree_code'])) {						//TreeCode flag
					$dbContext->tree = true;
				} else {
					$dbContext->tree = false;
				}
				$dbContext->db = null;
			} else {
				return NULL;
			}	
			$this->cache->save($cache_id, $dbContext);
		}
		return $dbContext;
	}

	public function update($dbContext)
	{
		$this->db->select ( '*' );
		$this->db->where ( 'id', $dbContext->crud_table['id'] );
		$save = $dbContext->crud_table;
		foreach ($save as $k=>$v) {
			if (substr( $k, 0, 1 ) === "_") {
				unset($save[$k]);
			}
		}
		$this->db->update ( Crud_model::CRUD_TABLE, $save );
		$this->db->trans_start ();
		foreach ($dbContext->crud_field as $f) {
			foreach ($f as $k=>$v) {
				if (substr( $k, 0, 1 ) === "_") {
					unset($f[$k]);
				}
			}
			$this->db->where( 'id', $f['id'] );
			$this->db->update ( Crud_model::CRUD_FIELD, $f );
		}
		$this->db->trans_complete ();
	}

	public function prepare(&$dbContext, $join = true)
	{
		if (!$dbContext) {
			return false;
		}
		if ($dbContext->prepared) {
			return true;
		}
		$table = $dbContext->name;
		$dbContext->db = $this->load->database ('default', true);
		$dbContext->db->start_cache();
	
		$dbContext->db->from ( $table );
		
		if ($dbContext->group) {
			$dbContext->db->where_in("{$table}.gid", $dbContext->group);
		}
		
		if ($dbContext->user) {
			$dbContext->db->where("{$table}.uid", $_SESSION['userinfo']['id']);
		}
		
		$i = 1;
		foreach ( $dbContext->crud_field as &$f ) {
			$dbContext->db->select ( "{$table}.{$f['name']}" );
			if ($join) {
				if ($f ['type'] == Crud_model::TYPE_SELECT
						|| $f ['type'] == Crud_model::TYPE_MULTI
						|| $f ['type'] == Crud_model::TYPE_BIT) {
					$f ['_joined_data'] = $this->get_left_join ( $table, $f ['name'], $f );
				}
				if ($f ['type'] == Crud_model::TYPE_SELECT) {
					$dbContext->db->select ( "a{$i}.{$f['join_caption']} r{$i}" );
					if ($f ['join_condition'] != '' && $f ['join_condition_value'] != '') {
						$dbContext->db->join ( "{$f['join_table']} a{$i}", "{$table}.{$f['name']}=a{$i}.{$f['join_value']} and a{$i}.{$f['join_condition']} = \"{$f['join_condition_value']}\"", 'LEFT' );
					} else {
						$dbContext->db->join ( "{$f['join_table']} a{$i}", "{$table}.{$f['name']}=a{$i}.{$f['join_value']}", 'LEFT' );
					}
					$f ['_joined'] = "r{$i}";
				}
			}
			$i ++;
		}
		
		$dbContext->prepared = true;
		return true;
	}
	
	public function edit($dbContext, $oper, $ids, $post)
	{
		$ret = (object)array(
				"message" => null
		);
		if (!$this->prepare($dbContext)) {
			$ret->message = "Can Not Prepare";
			return $ret;
		}
		$this->load->library('crud_hook');
		$table = $dbContext->name;
		switch ($oper) {
			case 'set' :
			case 'create' :
				$data = array ();
				foreach ( $dbContext->crud_field as $k => $f ) {
					if(!$f['_role_u']) {
						continue;
					}
					if (! isset ( $post [$k] )  || $f['prop'] & Crud_model::PROP_FIELD_PRIMARY) {
						continue;
					}
					
					$data [$k] = $post [$k];
					if ($f ['type'] == Crud_model::TYPE_PASSWORD && $data [$k] === "******") {
						unset ( $data [$k] );
						continue;
					}
					
// 					if ($f ['type'] == Crud_model::TYPE_MULTI) {
// 					$data_in = explode(',', $data [$k]);
// 						$data [$k] = '';
// 						foreach ($data_in as $d) {
// 							if (isset($f ['_joined_data'][$d])) {
// 								$data [$k] .= "{$d},";
// 							}
// 						}
// 					}
// 					if ($f ['type'] == Crud_model::TYPE_BIT) {
// 						$data_in = explode(',', $data [$k]);
// 						$data [$k] = 0;
// 						foreach ($data_in as $d) {
// 							if (isset($f ['_joined_data'][$d])) {
// 								$data [$k] |= (int)$d;
// 							}
// 						}
// 					}
					
					if ($f ['type'] == Crud_model::TYPE_SELECT) { 		// 如果为选择项，但是为-1，那么去掉
						$options = $this->get_left_join ( $table, $f ['name'], $f);
						if (! isset ( $options [$data [$k]] )) {
							unset ( $data [$k] );
						}
						continue;
					}
					if ($f ['prop'] & Crud_model::PROP_FIELD_UNIQUE) {
						$this->db->select("*");
						$this->db->from($dbContext->name);
						$this->db->where($k, $data [$k]);
						$exist = $this->db->get()->result_array();
						
						if ($oper=='set') {
							if (count($ids) > 1) {
								unset ( $data [$k] );
								continue;
							} else {
								if (count($exist) && $exist[0]['id'] != $ids[0]) {
									$ret->message = "已存在重复数据";
									return $ret;
								}
							}
						} else if ($oper=='create') {
							if (count($exist)) {
								$ret->message = "已存在重复数据";
								return $ret;
							}
						}
						
					}
					
					if ($data [$k] === null) { 							// drop null data
						unset ( $data [$k] );
						continue;
					}
				}
				
				if (method_exists($this->crud_hook, $dbContext->crud_table['before_edit'])) {
					$method = $dbContext->crud_table['before_edit'];
					$this->crud_hook->$method($oper, $data, $dbContext->crud_field);
				}
				
				if ($oper == 'create') {
					if (count($data)) {
						$dbContext->db->insert ( $table, $data );
						$id = $dbContext->db->insert_id ();
						$ret->id = $id;
					}
					if ($dbContext->tree) {
						$this->build_all_tree_code($table, $id, $dbContext->pid);
					}
				} else if ($oper == 'set') {
					if (count($data)) {
						if ($dbContext->pid) {
							if (! $this->check_pid_confilct ( $dbContext, $ids, $data [$dbContext->pid] )) {
								$ret->message = "数据冲突";
								return $ret;
							}
						}
						$dbContext->db->trans_start ();
						$dbContext->db->where_in ( 'id', $ids );
						$dbContext->db->update ( $table, $data );
						
						if ($dbContext->pid) {
							if ($dbContext->tree) {
								foreach ( $ids as $id ) {
									$this->build_all_tree_code ( $table, $id, $dbContext->pid );
								}
							}
						}
						$dbContext->db->trans_complete ();
					} else {
						$ret->message = "无修改";
						return $ret;
					}
				}
				break;
			case 'delete' :
				//TODO: change delete behavior
				if($dbContext->crud_table['_role_d']) {
					if ($dbContext->tree) {
						//TODO check depends
						$ret->message = "无法删除";
						return $ret;
					}
					$dbContext->db->where_in ( 'id', $ids );
					$dbContext->db->delete ( $table );
				}
				break;
			default :
				$ret->message = "不支持的操作";
				break;
		}
		return $ret;
	}
	
	public function parm($dbContext, $method, $arg1, $arg2 = null)
	{
		$dbContext->db->$method($arg1, $arg2);
	}
	public function sheet($dbContext)
	{
		return $dbContext->db->get ()->result_array ();
	}
	public function row($dbContext)
	{
		return $dbContext->db->get ()->row_array ();
	}
	public function col($dbContext, $colname)
	{
		$result = array();
		$data = $dbContext->db->get ()->result_array ();
		if (count($data) && isset($data[0][$colname])) {
			foreach($data as $d) {
				$result[] = $d[$colname];
			}
		}
		return $result;
	}
	public function cell($dbContext, $colname)
	{
		$result = null;
		$data = $dbContext->db->get ()->row_array ();
		if (count($data) && isset($data[$colname]) ) {
			$result = $data[$colname];
		}
		return $result;
	}
	public function stash_cache($dbContext)
	{
		$dbContext->db->stash_cache();
	}
	public function pop_cache($dbContext)
	{
		$dbContext->db->pop_cache();
	}
	public function get_left_join($table, $field, $field_info = null)
	{
		if ($field_info == null) {
			$dbContext = $this->table($table);
			if (!$dbContext) {
				return 1;
			}
			$field_info = $dbContext->crud_field[$field];
		}
		$join_dbContext = $this->table($field_info['join_table']);
		if (!$join_dbContext) {
			return 4;
		}
		if (!$this->prepare($join_dbContext, false)) {
			return 2;
		}
		switch ($field_info['type']) {
			case Crud_model::TYPE_SELECT :
			case Crud_model::TYPE_MULTI :
			case Crud_model::TYPE_BIT :
				$join_dbContext->db->select ( "{$field_info['join_value']} _value,{$field_info['join_caption']} _option" );
				if ($field_info['join_condition'] != '' && $field_info['join_condition_value'] != '') {
					$join_dbContext->db->where ( $field_info['join_condition'], $field_info['join_condition_value'] );
				}
				$join_dbContext->db->order_by("_value", "asc");
				$data = $join_dbContext->db->get ()->result_array ();
				$response = array ();
				foreach ( $data as $k => $d ) {
					$response[] = array(
							'id'=>$d ['_value'],
							'caption'=>$d ['_option']
					);
				}
// 				if ($join_dbContext->pid) {
// 					$response [0] = array('_value' => 0, '_option' => '');
// 				}
				break;
			default :
				return 3;
				break;
		}
		return $response;
	}
	
	public function parse($dbContext, $filters, $start=true)
	{
		$is_blank = true;
		if ($start) {
			$dbContext->db->group_start();
		}
		foreach ($filters->rules as $r) {
			if ($dbContext->crud_field[$r->field]['prop'] & PROP_FIELD_FILTER) {
				$this->_parse_rules($dbContext, $filters->groupOp, $r->field, $r->op, $r->data);
				$is_blank = false;
			}
		}
		
		if (isset($filters->groups) && count ($filters->groups) != 0) {
			$is_blank = false;
			foreach ($filters->groups as $g) {
				if ($filters->groupOp === "OR") {
					$dbContext->db->or_group_start();
				} else {
					$dbContext->db->group_start();
				}
				$this->parse($dbContext, $g, false);
				$dbContext->db->group_end();
			}
		}
		if ($is_blank) {
			$dbContext->db->where('1 =', 1);
		}
		if ($start) {
			$dbContext->db->group_end();
		}
		
		
	}
	private function _parse_rules($dbContext, $groupOp, $field, $op, $data)
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
				$dbContext->db->$func ( "{$dbContext->name}.{$field} is not null" );
				break;
			case "nu" :
				$func .= "where";
				$dbContext->db->$func ( "{$dbContext->name}.{$field}" );
				break;
			case "ni" :
				$func .= "where_not_in";
				$array_in = explode(',', $data);
				$dbContext->db->$func ( "{$dbContext->name}.{$field}", $array_in );
				break;
			case "in" :
				$func .= "where_in";
				$array_in = explode(',', $data);
				$dbContext->db->$func ( "{$dbContext->name}.{$field}", $array_in );
				break;
			case "nc" :
			case "en" :
			case "bn" :
				$func .= "not_";
			case "cn" :
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
				$dbContext->db->$func ( "{$dbContext->name}.{$field}", $data, $side_array [$side] );
				break;
			default :
				$func .= "where";
				$dbContext->db->$func ( "{$dbContext->name}.{$field} {$opt[$op]}", $data );
		}
	}
	
	public function build_all_tree_code($table, $id = 1, $pid_field = 'pid')
	{
		$this->db->trans_start ();
		$this->db->select("id,{$pid_field},tree_code");
		$this->db->from($table);
		$this->db->where('id' , $id);
		$data = $this->db->get()->row_array();
		if ($data) {
			if ($data[$pid_field] == 0) {
				$new_tree_code = "{$data['id']},";
			} else {
				$this->db->select("id,{$pid_field},tree_code");
				$this->db->from($table);
				$this->db->where('id' , $data[$pid_field]);
				$parent_data = $this->db->get()->row_array();
				$new_tree_code = "{$parent_data['tree_code']}{$data['id']},";
			}
			if (strcmp ($data['tree_code'],  $new_tree_code)) {
				$this->db->set('tree_code', $new_tree_code);
				$this->db->where('id', $id);
				$this->db->update($table);
			}
			$this->build_children_tree_code($table, $id, $new_tree_code, $pid_field);
		}
		$this->db->trans_complete ();
	}
	
	public function build_children_tree_code($table, $pid, $tree_code, $pid_field = 'pid')
	{
		$this->db->select("id,{$pid_field},tree_code");
		$this->db->from($table);
		$this->db->where($pid_field , $pid);
		$data = $this->db->get()->result_array();
		foreach($data as &$d) {
			$new_tree_code = "{$tree_code}{$d['id']},";
			if (strcmp ($d['tree_code'],  $new_tree_code)) {
				$this->db->set('tree_code', $new_tree_code);
				$this->db->where('id', $d['id']);
				$this->db->update($table);
			}
			$this->build_children_tree_code($table, $d['id'], $new_tree_code, $pid_field);
		}
	}
	
	public function get_group_ids($gid, $sub_tree = false)
	{
		$cache_id = "user_group_ids_{$gid}";
		if ( ! $result = $this->cache->get($cache_id)) {
			
			$this->db->select('tree_code');
			$this->db->from('user_group');
			$this->db->where('id', $gid);
			$tree_code = $this->db->get()->row('tree_code');
			
			if (!$tree_code) {
				return null;
			}
			$this->db->select('id');
			$this->db->from('user_group');
			if ($sub_tree) {
				$this->db->like('tree_code', $tree_code, 'after');
			} else {
				$this->db->where('tree_code', $tree_code);
			}
			$data = $this->db->get()->result_array();
			$result = array();
			foreach ($data as $d) {
				array_push($result, $d['id']);
			}
			if (!count($result)) {
				return null;
			}
			
			$this->cache->save($cache_id, $result);
		}
		return $result;
	}
	
	public function get_tree_data_by_id($dbContext = null, $id = 0, $prepare = false)
	{
		return $this->_get_tree_data_by_id($dbContext, $id, $prepare);
	}
	private function _get_tree_data_by_id($dbContext = null, $id = 0, $prepare = false)
	{
		if (!$dbContext) {
			return null;
		}
		if ($prepare) {
			$this->prepare($dbContext);
		}
		$dbContext->db->stash_cache();
		$dbContext->db->where("{$dbContext->name}.id", $id);
		$data = $dbContext->db->get()->row_array();
		$dbContext->db->pop_cache();
		if ($data) {
			$data['children'] = $this->_get_tree_data_by_pid($dbContext, $id);
		}
		return $data;
	}
	
	public function get_tree_data_by_pid($dbContext, $pid = 0, $prepare = false)
	{
		return $this->_get_tree_data_by_pid($dbContext, $pid, $prepare);
	}
	private function _get_tree_data_by_pid($dbContext, $pid = 0, $prepare = false)
	{
		if (!$dbContext) {
			return null;
		}
		if ($prepare) {
			$this->prepare($dbContext);
		}
		$dbContext->db->stash_cache();
		$dbContext->db->where("{$dbContext->name}.{$dbContext->pid}", $pid);
		$data = $dbContext->db->get()->result_array();
		$dbContext->db->pop_cache();
		foreach ($data as &$d) {
			$d['children'] = $this->get_tree_data_by_pid($dbContext, $d['id']);
		}
		return $data;
	}
	
	public function get_tree_option($data, $value, $option, &$tree = null, $prefix = "", $last = true)
	{
		if (!$tree) {
			$tree = array();
			$tree[] = "<option value='{$data[$value]}'>{$prefix}{$data[$option]}</option>";
		} else if ($last) {
			$tree[] = "<option value='{$data[$value]}'>{$prefix}┗{$data[$option]}</option>";
			$prefix .= "　";
		} else {
			$tree[] = "<option value='{$data[$value]}'>{$prefix}┣{$data[$option]}</option>";
			$prefix .= "┃";
		}
		if (is_array($data['children'])) {
			$i = 0;
			foreach($data['children'] as $d) {
				$i++;
				if ($i == count($data['children'])) {
					$this->get_tree_option($d, $value, $option, $tree, $prefix."　", true);
				} else {
					$this->get_tree_option($d, $value, $option, $tree, $prefix."　", false);
				}
			}
		}
		return $tree;
	}
	public function check_pid_confilct($dbContex, $ids, $pid)
	{
		while($pid) {
			$this->db->select("id,{$dbContex->pid}");
			$this->db->from($dbContex->name);
			$this->db->where('id', $pid);
			$data = $this->db->get()->row_array();
			$pid = $data[$dbContex->pid];
			if (array_search($data['id'], $ids, false) !== false) {
				return false;
			}
		}
		return true;
	}
	public function grid_header($dbContext, &$data)
	{
		$data->cols = array();
		$data->setting = new stdClass ();
		foreach ( $dbContext->crud_field as $k => $f ) {
			if(!$f['_role_r']) {
				continue;
			}
			$data->cols[] = $f['name'];
			$setting = new stdClass ();
			$setting->width = $f['width'];
			$setting->x = (int)$f['x'];
			$setting->y = (int)$f['y'];
			$setting->w = (int)$f['w'];
			$setting->h = (int)$f['h'];
			$setting->type = (int)$f['type'];
			$form_class = "";
			$form_obj = (object)array(
					"properties" => (object)array(),
					"events" => (object)array()
			);
					
			switch ($f['type']) {
				case Crud_model::TYPE_LABEL :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "none";
					break;
				case Crud_model::TYPE_NUMBER :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "none";
					break;
					$form_obj->properties->type = "none";
				case Crud_model::TYPE_DATE :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "date";
					break;
				case Crud_model::TYPE_TIME :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "time";
					break;
				case Crud_model::TYPE_DATETIME :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "datetime";
					break;
				case Crud_model::TYPE_SELECT:
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "listbox";
					$form_obj->events->beforePopShow = "_select_beforepopshow";
					$data->cols[] = $f['_joined'];
					$setting->tag = $f['_joined'];
					break;
				case Crud_model::TYPE_MULTI :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "cmdbox";
					$form_obj->properties->cmd = "multi";
					$form_obj->events->beforeComboPop = "_select_beforecombopop";
					break;
				case Crud_model::TYPE_BOOL :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "bool";
					break;
				case Crud_model::TYPE_TEXTAREA :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "none";
					$form_obj->properties->multiLines = true;
					break;
				case Crud_model::TYPE_PASSWORD :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "password";
					break;
				case Crud_model::TYPE_BIT :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "cmdbox";
					$form_obj->properties->cmd = "bit";
					$form_obj->events->beforeComboPop = "_select_beforecombopop";
					break;
				default:
					break;
			}
			if ($f['prop'] & Crud_model::PROP_FIELD_PRIMARY) {
				$form_obj->properties->readonly = true;
			}
			if ($f['prop'] & Crud_model::PROP_FIELD_FILTER) {
				$setting->filter = true;
				$setting->filterOpts = $f['search_option'];
			}
			$form_obj->key = $form_class;
			$setting->form = "new {$form_class}(".json_encode($form_obj).")";
			$setting->caption[] = $f['caption'];
			$name = $f['name'];
			$data->setting->$name = $setting;
		}
		if($dbContext->pid){
			$data->cols[] = "_sub";
		}
	}
	public function request_grid($dbContext, $paras)
	{
		if (!$this->prepare($dbContext)) {
			return false;
		}
		$this->load->library( 'xui' );
		$ret = new stdClass ();
		$header = new stdClass();
		$this->grid_header($dbContext, $header);
		$ret->gridName = $dbContext->name;
		$ret->gridForm = "App.GridForm";
		$ret->gridFilter = "App.GridFilter";
		$ret->gridCols = $header->cols;
		$ret->gridSetting = $header->setting;
		$ret->gridFormDlg = $this->xui->form($dbContext);
		$ret->gridFormWidth = (int)$dbContext->crud_table['w'];
		$ret->gridFormHeight = (int)$dbContext->crud_table['h'];
		$ret->gridTreeMode = ($dbContext->pid != "");
		return $ret;
	}
	public function request_getlist($dbContext, $paras)
	{
		$table = $dbContext->name;
		$ret = new stdClass ();
		
		$ret->rows = array();
		$data= array();
		$ret->count = array(array($dbContext->db->count_all_results()));
		//paging
		$pageIndex = (int)$paras->page;
		$pageRows = (int)$paras->size;
		if ($pageRows>0) {
			if ($pageRows < 20) {
				$pageRows = 20;
			}
			$start = ($pageIndex - 1) * $pageRows;
			$dbContext->db->limit($pageRows, $start);
		}
	
		//sort
		if(isset($paras->sidx) && isset($paras->sord)) {
			$sort = $paras->sidx;
			$sord = $paras->sord;
			$sord = ($sord === 'asc')?'asc':'desc';
			if (isset($dbContext->crud_field[$sort])) {
				if ($dbContext->crud_field[$sort]['type'] == Crud_model::TYPE_SELECT) {
					$dbContext->db->order_by("{$dbContext->crud_field[$sort]['_joined']}", $sord);
				} else {
					$dbContext->db->order_by("{$dbContext->name}.{$sort}", $sord);
				}
			}
		}
		//search
		if ($this->input->post_get("_search") === "true") {
			$dbContext->search = true;
			$filters = json_decode($this->input->post_get("filters"));
			$this->parse($dbContext, $filters);
		} else {
			$dbContext->search = false;
		}
		/*		
		//group
		if ($dbContext->group) {
			$gid = (int)$this->input->post_get("_gid");
			$subgroup = (int)$this->input->post_get("_subgroup");
			if (!$subgroup) {
				$dbContext->db->where("{$dbContext->name}.gid", $gid);
			} else {
				$gids = $this->get_group_ids($gid, true);
				$dbContext->db->where_in("{$dbContext->name}.gid", $gids);
			}
		}
*/		
		$dbContext->db->stash_cache();
		
		if ($dbContext->pid) {
			$nodeid = isset($paras->nodeid)?(int)$paras->nodeid:0;
			if ($dbContext->search == false) {
				$dbContext->db->where("{$table}.{$dbContext->pid}", $nodeid);
			}
		}
		$data = $dbContext->db->get ()->result_array ();
		$ret->sql = $dbContext->db->get_compiled_select();
		foreach ( $data as $d ) {
			$tmp = array ();
			foreach ( $dbContext->crud_field as $k => $f ) {
				if(!$f['_role_r']) {
					continue;
				}
				// if (function_exists ( $f['process'] )) {
				// $d [$k] = $f['process'] ( $d [$k] );
				// }
				if ($f ['type'] == Crud_model::TYPE_SELECT) {
					array_push ( $tmp, $d [$f ['name']] );
					array_push ( $tmp, $d [$f ['_joined']] );
				} else if ($f ['type'] == Crud_model::TYPE_PASSWORD) {
					array_push ( $tmp, "******" );
// 				} else if ($f ['type'] == Crud_model::TYPE_MULTI && 0) {
// 					$new_val = array ();
// 					$select_data = $f['_joined_data'];
// 					$vals = explode ( ",", $d [$k] );
// 					foreach ( $vals as $v ) {
// 						if (isset ( $select_data [$v] )) {
// 							$new_val [] = $select_data [$v]['_option'];
// 						}
// 					}
// 					array_push ( $tmp, implode ( ",", $new_val ) );
// 				} else if ($f ['type'] == Crud_model::TYPE_BIT && 0) {
// 					$new_val = array ();
// 					$select_data = $f['_joined_data'];
// 					foreach ($select_data as $sk=>$sv) {
// 						if (((int)$d [$k]) & ((int)$sk)) {
// 							$new_val[] = $sv['_option'];
// 						}
// 					}
						
// 					array_push ( $tmp, implode ( ",", $new_val ) );
				} else {
					array_push ( $tmp, $d [$f ['name']] );
				}
			}
			if ($dbContext->pid) {
// 				array_push ( $tmp, $level + 1 );						// level
// 				if ($nodeid) {
// 					array_push ( $tmp, $nodeid );						//parent id
// 				} else {
// 					array_push ( $tmp, NULL );							// no parent
// 				}
				$dbContext->db->pop_cache();
				$dbContext->db->where("{$table}.{$dbContext->pid}", $d['id']);
				$child_count = $dbContext->db->count_all_results();
				if ($child_count > 0) {
					array_push ( $tmp, true );
				} else {
					array_push ( $tmp, false );
				}
					
// 				array_push($tmp, false); //expanded
					
			}
			$ret->rows[] = $tmp;
		}
		return $ret;
	}
	function request_get($dbContext, $paras)
	{
		$id = $paras->id;
		$ret = (object) array(
				"cols" => array(),
				"rows" => array(),
				"caps" => (object)array()
		);
		$dbContext->db->where("{$dbContext->name}.id", $id);
		$data = $dbContext->db->get ()->row_array ();
		if ($data) {
			foreach ( $dbContext->crud_field as $k => $f ) {
				if(!$f['_role_r']) {
					continue;
				}
				$ret->cols[] = $f ['name'];
				if ($f['type']==Crud_model::TYPE_SELECT){
					$ret->rows[0][] = $data [$f ['name']];
					$ret->cols[] = $f ['_joined'];
					$ret->rows[0][] = $data [$f ['_joined']];
					$method=$f ['name'];
					$ret->caps->$method = $f ['_joined'];
				} else if ($f ['type'] == Crud_model::TYPE_PASSWORD) {
					$ret->rows[0][] = "******";
				} else {
					$ret->rows[0][] = $data [$f ['name']];
				}
			}
			if ($dbContext->pid) {
				$ret->pid=$data[$dbContext->pid];
			}
		} else {
			$ret->warn = (object) array(
				"message" => "No Such Data"	
			);
		}
		return $ret;
	}
	function request_set($dbContext, $paras)
	{
		$ret = $this->edit($dbContext, $paras->action, array($paras->id), (array)$paras->fields);
		if ($ret->message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $ret->message
					)
			);
		} else {
			return 1;
		}
	}
	
	function request_create($dbContext, $paras)
	{
		$ret = $this->edit($dbContext, $paras->action, null, (array)$paras->fields);
		if ($ret->message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $ret->message
					)
			);
		} else if($ret->id){
			return $this->request_get($dbContext, $ret);
		}else{
			return 0;
		}
	}
	
	function request_delete($dbContext, $paras)
	{
		$ret = $this->edit($dbContext, $paras->action, $paras->ids, null);
		if ($ret->message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $ret->message
					)
			);
		} else {
			return 1;
		}
	}
	
	function request_tables($paras)
	{
		$this->db->select("id,caption,w,h");
		$this->db->from(Crud_model::CRUD_TABLE);
		$ret = $this->db->get ()->result();
		return $ret;
	}
	function request_fields($paras)
	{
		$this->db->select("id,caption,x,y,w,h");
		$this->db->from(Crud_model::CRUD_FIELD);
		$this->db->where("tid", $paras->tid);
		$this->db->order_by ( 'seq', 'asc' );
		$ret = $this->db->get ()->result();
		return $ret;
	}
	function request_setting($paras)
	{
		$this->db->trans_start ();
		foreach ($paras->fields as $k=>$f) {
			$this->db->set("seq", $k);
			$this->db->set("x", $f->x);
			$this->db->set("y", $f->y);
			$this->db->set("w", $f->w);
			$this->db->set("h", $f->h);
			$this->db->where( 'id', $f->id );
			$this->db->update ( Crud_model::CRUD_FIELD );
		}
		$this->db->set("w", $paras->table_w);
		$this->db->set("h", $paras->table_h);
		$this->db->where( 'id', $paras->tid );
		$this->db->update ( Crud_model::CRUD_TABLE );
		$this->db->trans_complete ();
		return 1;
	}
	function request_get_select($dbContext, $paras)
	{
		$data=$this->get_left_join($dbContext->name, $paras->field, $dbContext->crud_field[$paras->field]);
		return $data;
	}
	function request_getlist_sel($dbContext, $paras)
	{
		$ret = new stdClass ();
		$data=$this->get_left_join($dbContext->name, $paras->field, $dbContext->crud_field[$paras->field]);
		$ret->count = array(array(count($data)));
		$ret->cols = array("value", "caption");
		$ret->setting = (object)array(
				"value" => (object)array(
						"width"=>60
				),
				"caption" => (object)array(
						"width"=>140
				)
		);
		$ret->rows = $data;
		return $ret;
	}
}