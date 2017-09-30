<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xui extends MY_Controller {
	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->reply(403, "Forbid");
	}

	public function request()
	{
		$this->load->model('grid_model');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		if (!$paras) {
			$this->reply(400, "表单参数错误");
		}
		$method = "request_{$paras->action}";
		if (method_exists($this, $method)) {
			$error = null;
			if ($table != "null") {
				if ($this->grid_model->table($table)) {
					if ($this->grid_model->prepare()) {
						$data = $this->$method($paras);
						$this->reply(200, "Success", $data);
					} else {
						$this->reply(500, "内部错误");
					}
				} else {
					$this->reply(400, "数据表错误");
				}
			} else{
				$data = $this->$method($paras);
				$this->reply(200, "Success", $data);
			}
		} else {
			$this->reply(501, "不支持的操作");
		}
	}
	
	private function request_grid($paras = null)
	{
		
		$this->load->library( 'xui_utils' );
		$ret = new stdClass ();
		$grid_info = $this->grid_model->grid_info();
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->gridRole = array(
			"c" => $this->grid_model->crud_table['_role_c'],
			"r" => $this->grid_model->crud_table['_role_r'],
			"u" => $this->grid_model->crud_table['_role_u'],
			"d" => $this->grid_model->crud_table['_role_d']
		);
		if ($this->grid_model->crud_table['_role_c'] || $this->grid_model->crud_table['_role_u']) {
			$ret->gridForm = "App.GridForm";
		} else {
			$ret->gridForm = null;
		}
		if ($this->grid_model->filter) {
			$ret->gridFilter = "App.GridFilter";
		}
		if ($this->grid_model->export) {
			$ret->gridExporter = "App.GridExporter";
		}
		if ($this->grid_model->import) {
			$ret->gridExporter = "App.GridImporter";
		}
		$ret->gridFormGroups=array();
		$groups = explode(";", $this->grid_model->crud_table['groups']);
		if(count($groups)) {
			foreach ($groups as $g) {
				$info = explode(":", $g);
				if (count($info) == 5) {
					$ret->gridFormGroups[] = (object)array(
						"name" => $info[0],
						"x" => $info[1],
						"y" => $info[2],
						"w" => $info[3],
						"h" => $info[4]
					);
				}
			}
		}

		$this->db->from("custom_item");
		$mid = 0;
		if (isset($paras->mid)) {
			$mid = substr($paras->mid, 1);
		}
		$this->db->where('mid', $mid);
		$custom_items = $this->db->sheet();

		$flow_items = $this->grid_model->get_flow_items();
		$ret->gridHeaders = $grid_info->headers;
		$ret->gridId = (int)$this->grid_model->crud_table['id'];
		$ret->gridSetting = $grid_info->setting;
		$ret->gridFormWidth = (int)$this->grid_model->crud_table['w'];
		$ret->gridFormHeight = (int)$this->grid_model->crud_table['h'];
		$ret->gridTreeMode = ($this->grid_model->pid != null)?$this->grid_model->pid:null;
		$ret->gridToolBarItems = $this->grid_model->grid_toolbar_items($flow_items, $custom_items);
		$ret->gridGroup = ($this->grid_model->group != null)?"gid":null;
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->dataFilter = $this->grid_model->crud_table['filter'];
		return $ret;
	}
	
	private function request_getlist($paras)
	{
		$ret = new stdClass();
		if (!$paras) {
			$paras = new stdClass();
		}
		$pageIndex = isset($paras->page) ? (int)$paras->page : 1;
		$pageRows = isset($paras->size) ? (int)$paras->size : 20;
		if ($pageRows>0) {
// 			if ($pageRows < 20) {
// 				$pageRows = 20;
// 			}
			$paras->page = $pageIndex;
			$paras->size = $pageRows;
		}
		
		
		$data = $this->grid_model->wrapper_sheet($paras);
		$ret->count = $data->count;
		$ret->sql = $data->sql;
		$ret->rows = $this->grid_model->sheet_to_grid($data->data);

		return $ret;
	}
	
	private function request_get($paras)
	{

		$ret = (object) array(
				"rows" => array(),
		);
		if (!isset($paras->id)) {
			$this->reply(400, "参数错误");
		}
		$id = $paras->id;
		$paras->sub = true;
		$paras->page = 1;
		$paras->size = 1;
		$paras->search = true;
		if (is_array($id)) {
			$paras->filters = (object) array(
				"groupOp" => "AND",
				"rules" => array(
					(object) array(
						"data" => $id,
						"op" => "in",
						"field" => "id"
					)
				)
			);
		} else {
			$paras->filters = (object) array(
				"groupOp" => "AND",
				"rules" => array(
					(object) array(
						"data" => $id,
						"op" => "eq",
						"field" => "id"
					)
				)
			);
		}

		
// 		json_decode("{'groupOp':'AND', 'rules':[{'data':'{$id}' , 'op':'eq', 'field':'id'}]}");
		
		$data = $this->grid_model->wrapper_sheet($paras);
		$ret->sql = $data->sql;
		if (count($data->data)) {
			$ret->rows = $this->grid_model->sheet_to_grid($data->data);
				
			if ($this->grid_model->pid) {
				$ret->pid = $data->data[0][$this->grid_model->pid];
			}
		} else {
			$this->reply(400, "无此数据");
		}
		return $ret;
	}
	
	private function request_set($paras)
	{
		$data = array();
		if ($this->grid_model->crud_table['_role_u']) {
			$ret = $this->grid_model->edit($paras->action, $paras->id, (array)$paras->fields);
			if (!$ret->result) {
				$this->reply(400, $ret->message);
			}
		} else {
			$this->reply(500, "无此权限");
		}
		if ($ret->message) {
			$this->warn($ret->message);
		}
		return $ret->data;
	}
	
	private function request_create($paras)
	{
		if ($this->grid_model->crud_table['_role_c']) {
			$ret = $this->grid_model->edit($paras->action, null, (array)$paras->fields);
			if (!$ret->result) {
				$this->reply(400, $ret->message);
			}
		} else {
			$this->reply(500, "无此权限");
		}
		if ($ret->message) {
			$this->warn($ret->message);
		}
		return $ret->data;
	}
	
	private function request_delete($paras)
	{
		if ($this->grid_model->crud_table['_role_d']) {
			$ret = $this->grid_model->edit($paras->action, $paras->ids, null);
			if (!$ret->result) {
				$this->reply(400, $ret->message);
			}
		} else {
			$this->reply(500, "无此权限");
		}
		if ($ret->message) {
			$this->warn($ret->message);
		}
		return null;
	}
	
	private function request_tables($paras)
	{
		$this->db->select("id,name,caption,w,h");
		$this->db->from(Crud_model::CRUD_TABLE);
		$ret = $this->db->sheet();
		return $ret;
	}
	
	private function request_fields($paras)
	{
		$ret = new stdClass ();
		$ret->fields = array();
		$ret->groups = array();
		foreach($this->grid_model->crud_field as $f){
			if ( ($f['prop'] & Crud_model::PROP_FIELD_PRIMARY)
				|| ($f['prop'] & Crud_model::PROP_FIELD_HIDE)) {
				continue;
			}
			
			$position = explode(":", $f['position']);
			if (count($position) == 4) {
				$ret->fields[] = (object)array(
					"id" => $f['id'],
					"caption" => $f['caption'],
					"x" => $position[0],
					"y" => $position[1],
					"w" => $position[2],
					"h" => $position[3]
				);
			} else {
				$ret->fields[] = (object)array(
					"id" => $f['id'],
					"caption" => $f['caption'],
					"x" => 0,
					"y" => 0,
					"w" => 0,
					"h" => 0
				);
			}
			
			
		}
		$groups = explode(";", $this->grid_model->crud_table['groups']);
		if(count($groups)) {
			foreach ($groups as $g) {
				$info = explode(":", $g);
				if (count($info) == 5) {
					$ret->groups[] = array(
						$info[0],
						$info[1],
						$info[2],
						$info[3],
						$info[4]
					);
				}
			}
		}
		$ret->id = $this->grid_model->crud_table['id'];
		return $ret;
	}
	
	private function request_setting($paras)
	{
		$this->grid_model->trans_start ();


		foreach ($paras->fields as $k=>$f) {
			$this->grid_model->set("seq", $k);
// 			$this->grid_model->set("x", $f->x);
// 			$this->grid_model->set("y", $f->y);
// 			$this->grid_model->set("w", $f->w);
// 			$this->grid_model->set("h", $f->h);
			$this->grid_model->set("position", "{$f->x}:{$f->y}:{$f->w}:{$f->h}");
			$this->grid_model->where( 'id', $f->id );
			$this->grid_model->update ( Crud_model::CRUD_FIELD );
		}
		foreach ($paras->groups as $k=>$g){
			$paras->groups[$k]=implode(":",$g);
		}
		$this->grid_model->set("groups", implode(";",$paras->groups));
		$this->grid_model->set("w", $paras->table_w);
		$this->grid_model->set("h", $paras->table_h);
		$this->grid_model->where( 'id', $paras->tid );
		$this->grid_model->update ( Crud_model::CRUD_TABLE );
		$this->grid_model->trans_complete ();
		return 1;
	}
	
	private function request_get_select($paras)
	{
		$data=$this->grid_model->get_left_join_for_list($paras->field);
		if ($data) {
			return $data->data;
		} else {
			return null;
		}
	}
	
	private function request_advance_input($paras)
	{
		$ret = new stdClass ();
		$data = $this->grid_model->get_left_join_for_advance_input($paras->field, $paras );
		if ($data) {
			$ret->headers = array(
				( object ) array (
					"id" => "value",
					"width" => 40,
					"caption" => "值",
					"type" => $data->type
				),
				( object ) array (
					"id" => "caption",
					"width" => 180,
					"caption" => "名称"
				)
			);
			$ret->rows = $data->data;
		}
		
		return $ret;
	}
	
	private function request_advance_select($paras)
	{
		$this->load->library( 'xui_utils' );
		$ret = new stdClass ();
		if ($paras->field === "gid") {
			$db = "db_".__LINE__;
			$this->load->model ('Crud_model', $db);
			$this->$db->table("user_group");
			$this->$db->prepare();
			$tree = $this->$db->get_tree_data_by_id($_SESSION['userinfo']['gid'], true);
			$data = array($this->xui_utils->build_tree($tree, $this->grid_model->crud_field[$paras->field]['join_caption']));
			$ret->items = $data;
		} else if(isset($this->grid_model->pid) && $paras->field == $this->grid_model->pid){
			$tree = $this->grid_model->get_tree_data_by_pid();
			$data = array($this->xui_utils->build_tree($tree[0], $this->grid_model->crud_field[$paras->field]['join_caption']));
			$ret->items = $data;
		} else {
			$data = $this->grid_model->get_left_join_for_list($paras->field, $paras);
			$ret->count = $data->count;
			$ret->items = $data->data;
			$ret->sql = $data->sql;
		}
		return $ret;
	}
	
	private function request_auto_complete($paras)
	{
		$ret = new stdClass ();
		$data = $this->grid_model->get_left_join_for_autocomplete ($paras->field, $paras);
		if ($data) {
			$ret->headers = array(
				( object ) array (
					"id" => "value",
					"width" => 180,
					"caption" => "值"
				),
				( object ) array (
					"id" => "caption",
					"width" => 180,
					"caption" => "名称"
				)
			);
			$ret->count = $data->count;
			$ret->rows = $data->data;
		}
		
		return $ret;
	
	}

	private function request_table_select($paras)
	{
		$ret = new stdClass ();
		$join_field = array();
		$field_info = $this->grid_model->crud_field[$paras->field];
		$join_field[] = 'id';
		$join_field[] = $field_info['join_value'];
		$join_field[] = $field_info['join_caption'];
		$join_field[] = $field_info['join_condition'];

		$extra_field = explode(',', $field_info['join_extra']);
		$map_pair_info = explode(',', $field_info['join_extra_map']);
		$map_field = array();
		$map_info_array = array();
		$map_info = new stdClass();
		foreach ($map_pair_info as $p) {
			$pair = explode(':', $p);
			if (is_array($pair) && count($pair) == 2) {
				$map_field[] = $pair[1];
				foreach($this->grid_model->crud_field as $k=>$f) {
					if(strtolower($k) == $pair[0] || $k == $pair[0]) {
						$field = $k;
						$map_info->$field = $pair[1];
					}
				}
			}
		}
		$join_field = array_merge($join_field, $extra_field, $map_field);
		$db = "db_".__LINE__;
		$this->load->model('grid_model', $db);
		$this->$db->table($field_info['join_table'], $join_field);
		if (!$this->$db->prepare(true)) {
			$this->reply(500, "内部错误");
		}
		if ($field_info['type'] != Crud_model::TYPE_SELECT) {
			$this->reply(500, "内部错误");
		}
		if (isset($paras->like) && strlen($paras->like)){
			if(isset($paras->match) && $paras->match) {
				$op = "eq";
			} else {
				$op = "cn";
			}
			$paras->search = true;
			$paras->filters = (object) array(
				"groupOp" => "OR",
				"rules" => array(
					(object) array(
						"data" => $paras->like,
						"op" => "eq",
						"field" => $field_info['join_value']
					),
					(object) array(
						"data" => $paras->like,
						"op" => $op,
						"field" => $field_info['join_caption']
					)
				)
			);
		}
		if ($field_info['join_condition'] && $field_info['join_condition_value']) {
			$paras->search = true;
			if ($paras->filters) {
				$filter_like = $paras->filters;
			} else {
				$filter_like = array();
			}
			$paras->filters = (object) array(
				"groupOp" => "AND",
				"rules" => array(
					(object) array(
						"data" => $field_info['join_condition_value'],
						"op" => "eq",
						"field" => $field_info['join_condition']
					),
					(object)$filter_like
				)
			);
		}
		$data = $this->$db->wrapper_sheet($paras);

		$grid_info = $this->$db->grid_info();
		$ret->sql = $data->sql;
		$ret->caption = $field_info['join_caption'];
		$ret->count = $data->count;
		$ret->headers = $grid_info->headers;
		$ret->setting = $grid_info->setting;
		$ret->gridToolBarItems = $this->grid_model->grid_filter_items();
		$ret->gridFilter = "App.GridFilter";
		$ret->gridSetting = $grid_info->setting;
		$ret->rows = $this->$db->sheet_to_grid($data->data);
		foreach($ret->headers as &$h) {
			if( !in_array($h->id, $extra_field )
				&& !in_array(strtolower($h->id), $extra_field )
				&& strcasecmp($h->id, $field_info['join_caption'])) {
				$h->hidden = true;
			} else{
				$h->hidden = false;
			}
			if(strtolower($h->id) == $field_info['join_caption']) {
				$ret->caption = $h->id;
			}
			foreach($map_info as $k=>$p) {
				if(strtolower($h->id) == $p || $h->id == $p ) {
					$map_info_array[]= (object) array(
						"id1" => $k,
						"id2" => $h->id
					);
				}
			}
		}
		$ret->map = $map_info_array;
		return $ret;

	}
	
	private function request_add_table($paras)
	{
		return $this->grid_model->install($paras->table);
	}
	
	private function request_resize($paras)
	{
		if (isset($this->grid_model->crud_field[$paras->name])) {
			$this->db->set("width", $paras->width);
			$this->db->where("id", $this->grid_model->crud_field[$paras->name]['id']);
			$this->db->update ( Crud_model::CRUD_FIELD );
			return 1;
		}
		return 0;
	}

	private function request_inline_grid($paras)
	{
		$field_info = $this->grid_model->crud_field[$paras->field];
		$db = "db_".__LINE__;
		$this->load->model('grid_model', $db);
		$this->$db->table($field_info['join_table'], explode(',',$field_info['join_extra']));
		$this->$db->prepare();
		$ret = new stdClass ();
		$grid_info = $this->$db->grid_info("inline", $field_info);
		$ret->gridId = (int)$this->$db->crud_table['id'];
		$ret->gridPrimary = $this->$db->primary;
		$ret->gridHeaders = $grid_info->headers;
		$ret->gridSetting = $grid_info->setting;

		if (is_array($paras->ids) && count($paras->ids)) {
			$paras->search = true;
			$paras->filters = (object) array(
				"groupOp" => "AND",
				"rules" => array(
					(object) array(
						"data" => $paras->ids[0],
						"op" => "in",
						"field" => $field_info['join_value']
					)
				)
			);

			$data = $this->$db->wrapper_sheet($paras);
			$ret->count = $data->count;
			$ret->sql = $data->sql;
			$ret->rows = $this->$db->sheet_to_grid($data->data);
		
		}
		
		return $ret;
	}

	private function request_form($paras)
	{
		$field_info = $this->grid_model->crud_field[$paras->field];
		$db = "db_".__LINE__;
		$this->load->model('grid_model', $db);
		$this->$db->table($field_info['join_table']);
		$this->$db->prepare();
		$ret = new stdClass ();
		$grid_info = $this->$db->grid_info();
		$ret->gridId = (int)$this->$db->crud_table['id'];
		$ret->gridPrimary = $this->$db->primary;
		$ret->gridSetting = $grid_info->setting;
		return $ret;
	}

	private function request_inline_save($paras)
	{
		$ret = new stdClass();
		$db = "db_".__LINE__;
		$this->load->model('grid_model', $db);
		$this->$db->table($paras->grid);
		$field_info = $this->$db->crud_field[$paras->field];
		foreach($paras->data as $d) {
			$row = (object) array(
				"rowid" => $d->rowid,
				"error" => 1
			);
			if (isset($d->id) && count($d->id)) {
				if ($this->grid_model->crud_table['_role_u']) {
					$data = $this->grid_model->edit("set", $d->id, (array)$d->fields);
					if(!$data->message) {
						$row->error = 0;
					} else {
						$this->reply(400, $data->message);
					}
				}
			} else {
				if ($this->grid_model->crud_table['_role_c']) {
					$set = $field_info['join_value'];
					$d->fields->$set=$paras->ids[0];
					$data = $this->grid_model->edit("create", null, (array)$d->fields);
					if(!$data->message){
						$row->error = 0;
						$row->id = (string)$data->id[0];
					} else {
						$this->reply(400, $data->message);
					}
				}
			}
			$ret->rows[] = $row;
		}
		return $ret;
	}

	private function request_flow_action($paras)
	{
		$flow_items = $this->grid_model->get_flow_items($paras->actionId);
		if (count($flow_items)) {
			$item = $flow_items[0];
			$this->db->where_in($this->grid_model->primary, $paras->ids);
			$this->db->where($item['field'], $item['stage']);
			$this->db->set($item['field'], $item['actionStage']);
			$this->db->update($this->grid_model->name);
		}

		return 1;
	}
	
	private function request_data_validate($paras)
	{
		$result = "结果：\n";
		ini_set('max_execution_time', 0);
		$setting = $paras->setting;
		$fields = array();
		$paras->page = 1;
		$paras->size = 500;
		$fields = array();
		
		$count = 0;
		$total = 0;
		
		foreach($setting as $s){
			if (isset($this->grid_model->crud_field[$s[0]]) && $this->grid_model->crud_field[$s[0]]['_role_r']) {
				$fields[$s[0]] = $s[1]?1:0;
			}
		}
				
		while ($count == 0 || $count < $total) {
			$data = $this->grid_model->wrapper_sheet($paras);
			$this->grid_model->pop_cache();
			if (!count($data->data)) {
				break;
			}
			$total = $data->count;
			$paras->page++;
			foreach($data->data as $d) {
				foreach($fields as $k=>$e) {
					if ($e) {
						$f = $this->grid_model->crud_field[$k];
						if ($f ['type'] == Crud_model::TYPE_SELECT) {
							if (!$d[$f['_caption']]) {
								$result .= "{$d[$this->grid_model->caption]} {$f['caption']} 填写不正确 \n";
							}
						}
					}
				}
			}
			$count += count($data->data);
		}
		return $result;
	}
	
	private function request_update_pinyin($paras)
	{
		ini_set('max_execution_time', 0);
		$this->load->library('py');
		
		$fields = array();
		$paras->page = 1;
		$paras->size = 20;
		
		$count = 0;
		$total = 0;
		
		while ($count == 0 || $count < $total) {
			$save = array();
			$data = $this->grid_model->wrapper_sheet($paras);
			$this->grid_model->pop_cache();
			if (!count($data->data)) {
				break;
			}
			$total = $data->count;
			$paras->page++;
			foreach($data->data as $d) {
				$row = array();
				foreach($this->grid_model->crud_field as $k=>$f) {
					if ($f ['prop'] & Crud_model::PROP_FIELD_PINYIN) {
						$row ['id'] = $d['id'];
						$row["{$k}_py"] = $this->py->abbr($d[$k]);
					}
				}
				if (count($row)) {
					$save[] = $row;
				}
			}
			$count += count($data->data);
			$this->grid_model->save($save);
			unset($save);
		}
		return 1;
	}
	
}
