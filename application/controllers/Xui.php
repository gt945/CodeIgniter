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
            $this->reply(400,  "格式错误");
		}
		$method = "request_{$paras->action}";
		if (method_exists($this, $method)) {
			$error = null;
			if ($table != "null") {
				if ($this->grid_model->table($table)) {
					if ($this->grid_model->prepare()) {
						$data = $this->$method($paras);
						if (!$data) {
							$error = "操作失败";
						} else if(is_string($data)) {
							$error=$data;
						}
					} else {
						$error = "内部错误";
					}
				} else {
					$error = "数据表错误";
				}
			} else{
				$data = $this->$method($paras);
			}

			if ($error) {
                $this->reply(500, $error);
			}
		} else {
            $this->reply(501, "不支持的操作");
		}
        $this->reply(200, "Success", $data);
	}
	
    function request_grid($paras = null)
	{
		
		$this->load->library( 'xui_utils' );
		$ret = new stdClass ();
		$grid_info = $this->grid_model->grid_info();
		$ret->gridName = $this->grid_model->name;
		$ret->gridForm = "App.GridForm";
		if ($this->grid_model->filter) {
			$ret->gridFilter = "App.GridFilter";
		}
		if ($this->grid_model->export) {
			$ret->gridExporter = "App.GridExporter";
		}
		if ($this->grid_model->import) {
			$ret->gridExporter = "App.GridImporter";
		}
        $db = "db_".__LINE__;
        $this->load->model ('Crud_model', $db);
        $this->$db->table("custom_item");
        $this->$db->prepare();
        $mid = 0;
        if (isset($paras->mid)) {
            $mid = substr($paras->mid, 1);
        }
        $this->$db->where('mid', $mid);
        $items = $this->$db->sheet();
		$ret->gridCols = $grid_info->cols;
		$ret->gridSetting = $grid_info->setting;
		$ret->gridFormWidth = (int)$this->grid_model->crud_table['w'];
		$ret->gridFormHeight = (int)$this->grid_model->crud_table['h'];
		$ret->gridTreeMode = ($this->grid_model->pid != null)?$this->grid_model->pid:null;
		$ret->gridToolBarItems = $this->grid_model->grid_toolbar_items($items);
		$ret->gridGroup = ($this->grid_model->group != null)?"gid":null;
		$ret->gridPrimary = $this->grid_model->primary;
        $ret->dataFilter = $this->grid_model->crud_table['filter'];
		return $ret;
	}
	
	function request_getlist($paras)
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
	
	function request_get($paras)
	{
		$id = $paras->id;
		$ret = (object) array(
				"rows" => array(),
		);
		$paras->sub = true;
		$paras->page = 1;
		$paras->size = 1;
		$paras->search = true;
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
		
// 		json_decode("{'groupOp':'AND', 'rules':[{'data':'{$id}' , 'op':'eq', 'field':'id'}]}");
		
		$data = $this->grid_model->wrapper_sheet($paras);
		$ret->sql = $data->sql;
		if (count($data->data)) {
			$ret->rows = $this->grid_model->sheet_to_grid($data->data);
				
			if ($this->grid_model->pid) {
				$ret->pid = $data->data[0][$this->grid_model->pid];
			}
		} else {
			$ret->warn = (object) array(
					//TODO
				"message" => "无此数据"	
			);
		}
		return $ret;
	}
	
	function request_set($paras)
	{
		$message = null;
		if ($this->grid_model->crud_table['_role_u']) {
			$ret = $this->grid_model->edit($paras->action, $paras->id, (array)$paras->fields);
			if ($ret->message) {
				$message = $ret->message;
			}
		} else {
			$message = "无此权限";
		}
		if ($message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $message
					)
			);
		} else {
			return 1;
		}
	}
	
	function request_create($paras)
	{
		$message = null;
		if ($this->grid_model->crud_table['_role_c']) {
			$ret = $this->grid_model->edit($paras->action, null, (array)$paras->fields);
			if ($ret->message) {
				$message = $ret->message;
			}
		} else {
			$message = "无此权限";
		}
		if ($message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $message
					)
			);
		} else if($ret->id){
			return $this->request_get($ret);
		}else{
			return 0;
		}
	}
	
	function request_delete($paras)
	{
		$message = null;
		if ($this->grid_model->crud_table['_role_c']) {
			$ret = $this->grid_model->edit($paras->action, $paras->ids, null);
			if ($ret->message) {
				$message = $ret->message;
			}
		} else {
			$message = "无此权限";
		}
		if ($message) {
			return ( object ) array (
					"warn" => ( object ) array (
							"message" => $message
					)
			);
		} else {
			return 1;
		}
	}
	
	function request_tables($paras)
	{
        $db = "db_".__LINE__;
        $this->load->model ('db_model', $db);
		$this->$db->select("id,name,caption,w,h");
		$this->$db->from(Crud_model::CRUD_TABLE);
		$ret = $this->$db->sheet();
		return $ret;
	}
	
	function request_fields($paras)
	{
		$ret = array();
		foreach($this->grid_model->crud_field as $f){
			if ( ($f['prop'] & Crud_model::PROP_FIELD_PRIMARY)
				|| ($f['prop'] & Crud_model::PROP_FIELD_HIDE)) {
				continue;
			}
			
			$ret[] = (object)array(
                "id" => $f['id'],
                "caption" => $f['caption'],
                "x" => $f['x'],
                "y" => $f['y'],
                "w" => $f['w'],
                "h" => $f['h']
			);
			
		}
		return $ret;
	}
	
	function request_setting($paras)
	{
		$this->grid_model->trans_start ();
		foreach ($paras->fields as $k=>$f) {
			$this->grid_model->set("seq", $k);
			$this->grid_model->set("x", $f->x);
			$this->grid_model->set("y", $f->y);
			$this->grid_model->set("w", $f->w);
			$this->grid_model->set("h", $f->h);
			$this->grid_model->where( 'id', $f->id );
			$this->grid_model->update ( Crud_model::CRUD_FIELD );
		}
		$this->grid_model->set("w", $paras->table_w);
		$this->grid_model->set("h", $paras->table_h);
		$this->grid_model->where( 'id', $paras->tid );
		$this->grid_model->update ( Crud_model::CRUD_TABLE );
		$this->grid_model->trans_complete ();
		return 1;
	}
	
	function request_get_select($paras)
	{
		$data=$this->grid_model->get_left_join_for_list($paras->field);
		if ($data) {
			return $data->data;
		} else {
			return null;
		}
	}
	
	function request_advance_input($paras)
	{
		$ret = new stdClass ();
		$data = $this->grid_model->get_left_join_for_advance_input($paras->field );
		if ($data) {
			$ret->headers = array(
                ( object ) array (
                    "id" => "value",
                    "width" => 40,
                    "caption" => "值",
                    "type" => "number"
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
	
	function request_advance_select($paras)
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
		} else if(isset($this->pid) && $paras->field == $this->pid){
			$tree = $this->grid_model->get_tree_data_by_pid();
			$data = array($this->xui_utils->build_tree($tree[0], $this->grid_model->crud_field[$paras->field]['join_caption']));
			$ret->items = $data;
		} else {
			$data = $this->grid_model->get_left_join_for_list($paras->field, $paras);
			$ret->count = $data->count;
			$ret->items = $data->data;
		}
		return $ret;
	}
	
	function request_auto_complete($paras)
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
            $ret->rows = $data->data;
        }
		
		return $ret;
	
	}

	function request_table_select($paras)
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
                    if(strtolower($k) == $pair[0]) {
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
            return 2;
        }
        if ($field_info['type'] != Crud_model::TYPE_SELECT) {
            return 3;
        }
        $data = $this->$db->wrapper_sheet($paras);

        $grid_info = $this->$db->grid_info();
        $ret->count = $data->count;
        $ret->headers = $grid_info->headers;
        $ret->setting = $grid_info->setting;
        $ret->rows = $this->$db->sheet_to_grid($data->data);
        foreach($ret->headers as &$h) {
            if( !in_array(strtolower($h->id), $extra_field ) && strtolower($h->id) != $field_info['join_caption']) {
                $h->hidden = true;
            }
            if(strtolower($h->id) == $field_info['join_caption']) {
                $ret->caption = $h->id;
            }
            foreach($map_info as $k=>$p) {
                if(strtolower($h->id) == $p) {
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
	
	function request_add_table($paras)
	{
		return $this->grid_model->install($paras->table);
	}
	
	function request_resize($paras)
	{
        $db = "db_".__LINE__;
        $this->load->model ('db_model', $db);
		if (isset($this->grid_model->crud_field[$paras->name])) {
			$this->$db->set("width", $paras->width);
			$this->$db->where("id", $this->grid_model->crud_field[$paras->name]['id']);
			$this->$db->update ( Crud_model::CRUD_FIELD );
			return 1;
		}
		return 0;
	}
	
}
