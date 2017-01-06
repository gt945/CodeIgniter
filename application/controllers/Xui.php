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
		$ret->gridPrimary = $this->grid_model->primary;
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
		$data = array();
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
		} else if ($ret->data){
			return $ret->data;
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
		if (isset($ret->data)) {
            return $ret->data;
        } else {
            return $message;
        }
//		if ($message) {
//			return ( object ) array (
//					"warn" => ( object ) array (
//							"message" => $message
//					)
//			);
//		} else if($ret->id){
//			return $this->request_get($ret);
//		}else{
//			return 0;
//		}
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
		$this->db->select("id,name,caption,w,h");
		$this->db->from(Crud_model::CRUD_TABLE);
		$ret = $this->db->sheet();
		return $ret;
	}
	
	function request_fields($paras)
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
		return $ret;
	}
	
	function request_setting($paras)
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
		} else if(isset($this->grid_model->pid) && $paras->field == $this->grid_model->pid){
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
			$ret->count = $data->count;
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
			return 2;
		}
		if ($field_info['type'] != Crud_model::TYPE_SELECT) {
			return 3;
		}
		if (isset($paras->like) && strlen($paras->like)){
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
                        "op" => "cn",
                        "field" => $field_info['join_caption']
                    )
                )
            );
        }
		$data = $this->$db->wrapper_sheet($paras);

		$grid_info = $this->$db->grid_info();
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
	
	function request_add_table($paras)
	{
		return $this->grid_model->install($paras->table);
	}
	
	function request_resize($paras)
	{
		if (isset($this->grid_model->crud_field[$paras->name])) {
			$this->db->set("width", $paras->width);
			$this->db->where("id", $this->grid_model->crud_field[$paras->name]['id']);
			$this->db->update ( Crud_model::CRUD_FIELD );
			return 1;
		}
		return 0;
	}

	function request_inline_grid($paras)
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

	function request_form($paras)
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

	function request_inline_save($paras)
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
                        return $data->message;
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
                        return $data->message;
                    }
                }
            }
            $ret->rows[] = $row;
        }
        return $ret;
    }

    function request_flow_action($paras)
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
}
