<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
include_once(APPPATH.'models/Crud_model.php');

class Grid_model extends Crud_Model {

    public function table($name, $select = array())
    {
        return parent::table($name, $select);
    }
    
	public function prepare($join = true)
	{
		$result = parent::prepare($join);

		if ($result && $join) {
			foreach ( $this->crud_field as &$f ) {
				switch($f ['type']) {
					case Crud_model::TYPE_SELECT:
					case Crud_model::TYPE_MULTI:
					case Crud_model::TYPE_BIT:
						$f ['_joined_data'] = $this->get_left_join_for_check ( $f ['name']);
						break;
					default:
						break;
				}
			}
		}
		return $result;
		
	}


	public function get_left_join($field, $paras = null)
	{
		$ret = new stdClass();
		$field_info = $this->crud_field[$field];
		$db = "db_".__LINE__;
		$this->load->model('Crud_model', $db);
		$this->$db->table($field_info['join_table']);
		$this->$db->prepare(false);

		if (isset($paras->relate)){
			$relates = explode(',', $field_info['relate']);
			$dep = array();
			foreach($relates as $k=>&$d) {
				$tmp = explode(':', $d);
				$dep[$tmp[0]] = $tmp;
			}
			foreach($paras->relate as $key=>$value){
				if(isset($this->$db->crud_field[$key])) {
					array_unshift($dep[$key], $value);
					call_user_func_array(array(&$this->$db, 'parse_relate'), $dep[$key]);
				}
			}
		}

		switch ($field_info['type']) {
			case Crud_model::TYPE_SELECT :
			case Crud_model::TYPE_MULTI :
			case Crud_model::TYPE_BIT :
			    if ($field_info['prop'] & Crud_model::PROP_FIELD_STRING) {
                    $ret->type = "string";
                } else {
                    $ret->type = "number";
                }
				$this->$db->select ( "{$field_info['join_value']} _value");
				$this->$db->select ( "{$field_info['join_caption']} _caption" );
// 				if($this->$db->pid) {
// 					$this->$db->select ( "{$$this->$db->pid} _pid" );
// 				}
				if ($field_info['join_condition'] != '' && $field_info['join_condition_value'] != '') {
					$this->$db->where ( $field_info['join_condition'], $field_info['join_condition_value'] );
				}
				if (isset($paras->like)) {
					$this->$db->group_start();
					$this->$db->like($field_info['join_caption'], $paras->like, 'both');
                    $this->$db->or_like($field_info['join_value'], $paras->like, 'both');
					$this->$db->group_end();
				}
				if ($field_info['type'] == Crud_model::TYPE_SELECT ) {
     				$ret->count = $this->$db->count_all_results();
                }
				if (isset($paras->page) && isset($paras->size)) {
					$start = ($paras->page - 1) * $paras->size;
					$this->$db->limit($paras->size, $start);
				}
				$this->$db->order_by("_value", "asc");
				$ret->data = $this->$db->sheet();
				break;
			case Crud_model::TYPE_AUTOCOMPLETE :
				$this->$db->select ( "{$field_info['join_value']} _value");
				$this->$db->select ( "{$field_info['join_caption']} _caption");
				if (isset($paras->like)) {
					$this->$db->group_start();
					$this->$db->like($field_info['join_value'], $paras->like, 'both');
                    $this->$db->or_like($field_info['join_caption'], $paras->like, 'both');
					$this->$db->group_end();
				}
				$ret->count = $this->$db->count_all_results();
				if (isset($paras->page) && isset($paras->size)) {
					$start = ($paras->page - 1) * $paras->size;
					$this->$db->limit($paras->size, $start);
				}
				$this->$db->order_by("_caption", "asc");
				$ret->data = $this->$db->sheet();
                break;
			default :
				return 3;
				break;
		}
		$this->$db->flush_cache();
		return $ret;
	}
	
	public function get_left_join_for_check($field, $paras = null)
	{
		$data = $this->get_left_join($field, $paras);
		if (is_object($data)){
			$response = array();
			foreach ( $data->data as $k => $d ) {
				$response[$d ['_value']] = $d;
			}
			$data->data = $response;
		} else {
			$data = null;
		}
		return $data;
	}
	
	public function get_left_join_for_list($field, $paras = null)
	{
		$data = $this->get_left_join($field, $paras);
		if (is_object($data)){
			$response = array();
			foreach ( $data->data as $k => $d ) {
				$r = array(
				    "id" => $d ['_value'],
                    "caption" => $d ['_caption']
				);
				$response[] = $r;
			}
			$data->data = $response;
		} else {
			$data = null;
		}
		return $data;
	}

	public function get_left_join_for_advance_input($field, $paras = null)
	{
		$data = $this->get_left_join($field, $paras);
		if (is_object($data)){
			$response = array();
			foreach ( $data->data as $k => $d ) {
				$r = (object)array(
				    "id" => $d ['_value'],
                    "cells" => array(
						$d ['_value'],
						$d ['_caption']
                    )
				);
				$response[] = $r;
			}
			$data->data = $response;
		} else {
			$data = null;
		}
		return $data;
	}
    public function get_left_join_for_autocomplete($field, $paras = null)
	{
		$data = $this->get_left_join($field, $paras);
		if (is_object($data)){
			$response = array();
			foreach ( $data->data as $k => $d ) {
                $r = (object)array(
                    "id" => $d ['_value'],
                    "cells" => array(
                        $d ['_value'],
                        $d ['_caption']
                    )
                );
				$response[] = $r;
			}
			$data->data = $response;
		} else {
			$data = null;
		}
		return $data;
	}
	
	public function get_left_join_for_tree($field, $paras = null)
	{
		$data = $this->get_left_join($field, $paras);
		$response = array();
		foreach ( $data->data as $k => $d ) {
			$r = array(
					'id' => $d ['_value'],
					'caption' => $d ['_caption']
			);
			$response[] = $r;
		}
		$data->data = $response;
		return $data;
	}
	
	public function wrapper_caption($f, &$d)
	{
		$k = $f['name'];
		if ($f ['type'] == Crud_model::TYPE_MULTI) {
			$new_val = array ();
			$select_data = $f ['_joined_data']->data;
			$vals = explode ( ",", $d [$k] );
			foreach ( $vals as $v ) {
				if (isset ( $select_data [$v] )) {
					$new_val [] = $select_data [$v] ['_caption'];
				}
			}
			$d[$f['_caption']] = implode ( ",", $new_val );
		} else if ($f ['type'] == Crud_model::TYPE_BIT) {
			$new_val = array ();
			$select_data = $f ['_joined_data']->data;
			foreach ( $select_data as $sk => $sv ) {
				if ((( int ) $d [$k]) & (( int ) $sk)) {
					$new_val [] = $sv ['_caption'];
				}
			}
			$d[$f['_caption']] = implode ( ",", $new_val );
		}
	}
	
	
	public function wrapper_sheet($paras)
	{
		$table = $this->name;
		$db = "db_".__LINE__;
		$this->load->model('Crud_model', $db);
		$this->$db->table($table);
		$this->$db->from("{$table} b");
		$this->$db->select("b.{$this->primary}");
		
		$ret = (object) array(
			"count" => 0,
			"data"  => array(),
			"sql"   => array()
		);
		
		if (!$this->crud_table['_role_r']) {
			return $ret;
		}
		//search
		if (isset($paras->search) && $paras->search == true) {
			$this->search = true;
			if (isset($paras->filters->rules) && isset($paras->filters->groupOp)) {
				$this->$db->parse($paras->filters);
			}
		} else {
			$this->search = false;
		}
		
		//group
		if ($this->group) {
			if (isset($paras->gid) && in_array($paras->gid, $this->$db->group)){
				$gid = (int)$paras->gid;
			}else{
				$gid = $_SESSION['userinfo']['gid'];
			}
			if (isset($paras->sub)){
				$subgroup = (int)$paras->sub;
			}else{
				$subgroup = 0;
			}
			if (!$subgroup) {
				$this->$db->where("b.gid", $gid);
			} else {
// 				$gids = $this->get_group_ids($gid, true);
// 				$this->$db->where_in("b.gid", $gids);
				
				$tree_code = $this->get_group_tree_code($gid);
				$this->db2->select('1');
				$this->db2->from('user_group');
				$this->db2->like('tree_code', $tree_code, 'after', false);
				$this->db2->where("id=b.gid", null, false);
				
				$exist_sql = $this->db2->get_compiled_select();
				$this->$db->where("EXISTS({$exist_sql})");
			}
		}
		
		$ret->count = $this->$db->count_all_results('', false);
		
		//paging
		if (isset($paras->page) && isset($paras->size) ) {
			$pageIndex = (int)$paras->page;
			$pageRows = (int)$paras->size;
			if ($pageRows > 0){
				$start = ($pageIndex - 1) * $pageRows;
				$this->$db->limit($pageRows, $start);
			}
		}
		
		//sort
		if(isset($paras->sidx) && isset($paras->sord)) {
			$sort = $paras->sidx;
			$sord = $paras->sord;
			$sord = ($sord === 'asc')?'asc':'desc';
			if (isset($this->crud_field[$sort]) && ($this->crud_field[$sort]['prop'] & Crud_model::PROP_FIELD_SORT)) {
				$this->$db->order_by("b.{$sort}", $sord);
				$this->order_by("a.{$sort}", $sord);
/*
				if ($this->crud_field[$sort]['type'] == Crud_model::TYPE_SELECT) {
					$this->order_by("{$this->crud_field[$sort]['_caption']}", $sord);
				} else {
					$this->$db->order_by("b.{$sort}", $sord);
				}
*/
			}
		}
		$this->stash_cache();
		
		if ($this->pid) {
			$nodeid = isset($paras->nodeid)?(int)$paras->nodeid:0;
			if ($this->search == false) {
				$this->$db->where("b.{$this->pid}", $nodeid);
			}
		}
		$this->join("(".$this->$db->get_compiled_select().") c", "`a`.`{$this->primary}`=`c`.`{$this->primary}`");
		$ret->data = $this->sheet();
		$ret->sql = array(
				array($this->elapsed_time(),$this->total_queries(),$this->db->queries),
				array($this->$db->elapsed_time(),$this->$db->total_queries(),$this->$db->db->queries),
				array($this->db2->elapsed_time(),$this->db2->total_queries(), $this->db2->db->queries)
		);

		return $ret;
	}

	function row_to_grid($d, $dataonly = false, $keypair = false, $select = array())
    {
        $row = (object)array();
        $tmp = array ();

        if (isset ( $this->primary )) {
            $primary = $d [$this->primary];
        } else if (isset ( $d ['id'] )) {
            $primary = $d ['id'];
        }
        foreach ( $this->crud_field as $k => $f ) {
            $cell = (object)array ();
            if ( !$f ['_role_r'] || $f['type'] >= Crud_model::TYPE_BUTTON ) {
                continue;
            }
            if (($f ['prop'] & Crud_model::PROP_FIELD_PRIMARY)
                || ($f ['prop'] & Crud_model::PROP_FIELD_HIDE)) {
                continue;
            }

            if (count($select) && !in_array($f['name'], $select)) {
                continue;
            }
            // if (function_exists ( $f['process'] )) {
            // $d [$k] = $f['process'] ( $d [$k] );
            // }
            if ($f ['type'] == Crud_model::TYPE_SELECT
                || $f ['type'] == Crud_model::TYPE_MULTI
                || $f ['type'] == Crud_model::TYPE_BIT) {
                $this->wrapper_caption($f, $d);
                $cell->value = $d [$f ['name']];
                if (isset($f ['_caption'])) {
                    $cell->caption = $d [$f ['_caption']];
                }
            } elseif ($f ['type'] == Crud_model::TYPE_PASSWORD) {
                $cell->value = "******";
            } else {
                $cell->value = $d [$f ['name']];
            }
// 				if (strlen($f['depend']) && !eval ("return {$f['depend']};")) {
// 					$cell->readonly = true;
// 				}
            if ($keypair) {
                $tmp[$f['name']] = $cell;
            } else {
                array_push ( $tmp, $cell );
            }
        }
        $row->id = $primary;
        $row->cells = $tmp;
        if ($this->pid && !$dataonly) {
            $this->pop_cache ();
            $this->where ( "a.{$this->pid}", $d ['id'] );
            $child_count = $this->count_all_results ();
            if ($child_count > 0) {
                $row->sub = true;
            } else {
                $row->sub = false;
            }
        }
        return $row;
    }
	
	function sheet_to_grid($data, $dataonly = false, $keypair = false, $select = array())
	{
		$rows = array ();
		foreach ( $data as $d ) {
		    $rows[] = $this->row_to_grid($d, $dataonly, $keypair, $select);
		}
		return $rows;
	}
	
	function grid_info($mode="dialog")
	{
		$data = new stdClass();
		$data->headers = array();
		$data->cols = array();
		$data->setting = new stdClass ();

		$this->filter = false;
		$this->export = false;
		$this->import = false;
		
		if ($this->crud_table['prop'] & Crud_model::PROP_TABLE_EXPORT) {
			$this->export = true;
		}
		if ($this->crud_table['prop'] & Crud_model::PROP_TABLE_IMPORT) {
			$this->import = true;
		}
		foreach ( $this->crud_field as $k => $f ) {
			if(!$f['_role_r']) {
				continue;
			}
			if ( ($f['prop'] & Crud_model::PROP_FIELD_PRIMARY)
					|| ($f['prop'] & Crud_model::PROP_FIELD_HIDE)) {
				continue;
			}
			
			$setting = new stdClass ();
			$header = new stdClass ();


			$position = explode(":", $f['position']);
			if (count($position) == 4) {
				$setting->x = (int)$position[0];
				$setting->y = (int)$position[1];
				$setting->w = (int)$position[2];
				$setting->h = (int)$position[3];
			} else {
				$setting->x = 0;
				$setting->y = 0;
				$setting->w = 1;
				$setting->h = 1;
			}
			
			$setting->type = (int)$f['type'];
			$setting->caption[] = $f['caption'];
            $setting->relate = $f['relate'];
            $header->id = $f['name'];
            $header->caption = $f['caption'];
            $header->width = $f['width'];

            if ( $f['prop'] & Crud_model::PROP_FIELD_VIRTUAL) {
                $setting->virtual = true;
            } else {
                $setting->virtual = false;
            }
            if ( $f['type'] >= Crud_model::TYPE_BUTTON) {
                $setting->object = true;
            } else {
                $setting->object = false;
                $setting->search_option = (int)$f['search_option'];
                $setting->mask = $f['mask'];
                $setting->format = $f['format'];
                $setting->template = $f['template'];
                $setting->currency = $f['currency'];
                $setting->width = (int)$f['width'];
                $setting->tree = ($this->pid == $f['name'] || $f['name']=="gid")?true:false;
                if($setting->tree){
                    $setting->tree_field = $f['join_caption'];
                }
            }
			$form_class = "";
			$form_obj = (object)array(
					"properties" => (object)array(),
					"events" => (object)array()
			);
            if ($f['prop'] & Crud_model::PROP_FIELD_SORT) {
                $header->sort = true;
            } else {
                $header->sort = false;
            }
            if (($f['prop'] & Crud_model::PROP_FIELD_MINIFY)
                && $mode  == "dialog" ) {
                $header->hidden = true;
            } else {
                $header->hidden = false;
            }
			$header_inline = clone $header;
//            $header_inline->format="[^.*]";
			switch ($f['type']) {
				case Crud_model::TYPE_LABEL :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "none";
                    $header_inline->type="input";
					break;
				case Crud_model::TYPE_NUMBER :
					$form_class = "xui.UI.ComboInput";
                    if($f['prop']&Crud_model::PROP_FIELD_CURRENCY) {
                        $form_obj->properties->type = "currency";
                        $form_obj->properties->currencyTpl = $f['currency'];
                        $header_inline->type= "currency";
                    } else {
                        $form_obj->properties->type = "none";
                        $header_inline->type= "input";
                    }

                    $header->type= "number";
					break;
				case Crud_model::TYPE_DATE :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "date";
					$form_obj->properties->dateEditorTpl = "yyyy-mm-dd";
                    $header_inline->type="date";
                    $header_inline->dateEditorTpl="yyyy-mm-dd";
					break;
				case Crud_model::TYPE_TIME :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "time";
                    $header_inline->type="time";
					break;
				case Crud_model::TYPE_DATETIME :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "datetime";
					$form_obj->properties->dateEditorTpl = "yyyy-mm-dd hh:nn:ss";
                    $header_inline->type="datetime";
                    $header_inline->dateEditorTpl="yyyy-mm-dd hh:nn:ss";
					break;
				case Crud_model::TYPE_SELECT:
					$form_class = "xui.UI.ComboInput";
					if ($setting->tree || ($f['prop'] & Crud_model::PROP_FIELD_ADVANCE)) {
						$form_obj->properties->type = "cmdbox";
						$form_obj->properties->app = "App.AdvSelect";
						$form_obj->events->beforeComboPop = "_select_beforecombopop";
                        $header_inline->type="cmdbox";
                        $header_inline->app="App.AdvSelect";
					} else if ($f['prop'] & Crud_model::PROP_FIELD_TABLE) {
                        $form_obj->properties->type = "cmdbox";
                        $form_obj->properties->app = "App.TableSelect";
                        $form_obj->events->beforeComboPop = "_select_beforecombopop";
                        $header_inline->type="cmdbox";
                        $header_inline->app="App.TableSelect";
                    } else{
						$form_obj->properties->type = "listbox";
						$form_obj->events->beforePopShow = "_select_beforepopshow";
                        $header_inline->type="listbox";
					}
					break;
				case Crud_model::TYPE_MULTI :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "cmdbox";
					$form_obj->properties->app = "App.AdvInput";
					$form_obj->properties->cmd = "multi";
					$form_obj->events->beforeComboPop = "_select_beforecombopop";
                    $header_inline->type="cmdbox";
                    $header_inline->app="App.AdvInput";
					break;
				case Crud_model::TYPE_BOOL :
					$form_class = "xui.UI.CheckBox";
					$form_obj->properties->caption= "{$f['caption']} ";
					$setting->type= "checkbox";
                    $header_inline->type= "checkbox";
                    $header->type= "checkbox";
					break;
				case Crud_model::TYPE_TEXTAREA :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "none";
					$form_obj->properties->multiLines = true;
                    $header_inline->type="textarea";
					break;
				case Crud_model::TYPE_PASSWORD :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "password";
                    $header_inline->type="password";
					break;
				case Crud_model::TYPE_BIT :
					$form_class = "xui.UI.ComboInput";
					$form_obj->properties->type = "cmdbox";
					$form_obj->properties->cmd = "bit";
					$form_obj->properties->app = "App.AdvInput";
					$form_obj->events->beforeComboPop = "_select_beforecombopop";
                    $header_inline->type="cmdbox";
                    $header_inline->app="App.AdvInput";
					break;
                case Crud_model::TYPE_AUTOCOMPLETE :
                    $form_class = "xui.UI.ComboInput";
                    $form_obj->properties->type = "helpinput";
                    $form_obj->properties->app = "App.AutoComplete";
                    $form_obj->events->beforeComboPop = "_select_beforecombopop";
                    $header_inline->type="helpinput";
                    $header_inline->app="App.AutoComplete";
                    break;
                case Crud_model::TYPE_HELPER:
                    $form_class = "xui.UI.ComboInput";
                    $form_obj->properties->type = "helpinput";
                    $form_obj->properties->app = $f['app'];
                    $form_obj->events->beforeComboPop = "_select_beforecombopop";
                    $header_inline->type="helpinput";
                    $header_inline->app=$f['app'];
                    break;
                default:
			}

            if ($f['type'] < Crud_model::TYPE_BUTTON) {
                $form_obj->properties->labelSize = 110;
                $form_obj->properties->labelCaption = "{$f['caption']} ";
                $form_obj->CS = (object) array(
                    "LABEL" => (object) array(
                        "text-align" => "center"
                    )
                );

                if (!$f['_role_u'] || ($f['prop'] & Crud_model::PROP_FIELD_READONLY)) {
                    $form_obj->properties->readonly = true;
                }
                if ($f['prop'] & Crud_model::PROP_FIELD_FILTER) {
                    $this->filter = true;
                    $setting->filter = true;
                    $setting->filterOpts = $f['search_option'];
                }

                if (($f['prop'] & Crud_model::PROP_FIELD_REQUIRED) && $setting->format == "") {
                    $setting->format = "[^.*]";
                }

                $form_obj->key = $form_class;
                if($mode == "dialog") {
                    $setting->form = "new {$form_class}(".json_encode($form_obj).")";
                    $setting->form_properties = $form_obj;
                    $data->headers[] = $header;
                }else if($mode == "inline") {
                    $data->headers[] = $header_inline;
                }
                $data->cols[] = $f['name'];
            } else {
                $setting->app = $f['app'];
            }

			$name = $f['name'];
			$data->setting->$name = $setting;

		}
		return $data;
	}
	public function edit($oper, $ids, $post)
	{
		$ret = (object)array(
				"message" => "未知错误"
		);
		
		$this->load->model('crud_before_edit');
        $this->load->model('crud_after_edit');
        $this->load->model('crud_before_del');
        $this->load->model('crud_after_del');
		$table = $this->name;
		switch ($oper) {
        case 'set' :
        case 'create' :
            $data = array ();
            foreach ( $this->crud_field as $k => $f ) {
                if(!$f['_role_u']) {
                    continue;
                }
                if ( (!isset( $post [$k] ) && !isset( $post->$k ))
                        || ($f['prop'] & Crud_model::PROP_FIELD_PRIMARY)
                        || ($f['prop'] & Crud_model::PROP_FIELD_READONLY)) {
                    continue;
                }
                if (isset( $post [$k] )){
                    $data [$k] = $post [$k];
                } else if(isset( $post->$k ))  {
                    $data [$k] = $post->$k;
                } else {
                    continue;
                }

                if ($f ['type'] == Crud_model::TYPE_PASSWORD && $data [$k] === "******") {
                    unset ( $data [$k] );
                    continue;
                }

                if ($f ['type'] == Crud_model::TYPE_SELECT) { 		// 如果为选择项，但是为-1，那么去掉
                    if (! isset ( $f ['_joined_data']->data[$data [$k]] )) {
                        unset ( $data [$k] );
                    }
                    continue;
                }
                if ($f ['type'] == Crud_model::TYPE_MULTI) {
                    $data_in = explode(',', $data [$k]);
                    $data_new = array();
                    foreach ($data_in as $d) {
                        if (isset($f ['_joined_data']->data[$d])) {
                            $data_new[] = $d;
                        }
                    }
                    $data [$k] = implode ( ",", $data_new );
                }
/*					
                if ($f ['type'] == Crud_model::TYPE_BIT) {
                    $data_in = explode(',', $data [$k]);
                    $data [$k] = 0;
                    foreach ($data_in as $d) {
                        if (isset($f ['_joined_data']->data[$d])) {
                            $data [$k] |= (int)$d;
                        }
                    }
                }
*/			
                if ($f ['type'] == Crud_model::TYPE_BOOL) {
                    $data [$k] = $data [$k] == 'true' ? 1 : 0;
                }
                if ($f ['prop'] & Crud_model::PROP_FIELD_UNIQUE) {
                    $this->db2->select("*");
                    $this->db2->from($this->name);
                    $this->db2->where($k, $data [$k]);
                    $exist = $this->db2->sheet();

                    if ($oper=='set') {
                        if (count($ids) > 1) {
                            unset ( $data [$k] );
                            continue;
                        } else {
                            if (count($exist) && $exist[0][$this->primary] != $ids[0]) {
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

            if (!count($data)) {
                $ret->message = "无修改";
                return $ret;
            }
            $save = array();
            if ($oper == 'create') {
                unset($data[$this->primary]);
                $save[] = $data;
                $old = array();
            } else {
                foreach($ids as $id) {
                    $data[$this->primary] = $id;
                    $save[] = $data;
                }
            }
            if (method_exists($this->crud_before_edit, $this->crud_table['before_edit'])) {
                $method = $this->crud_table['before_edit'];
                if ($oper == 'set') {
                    $this->db2->select("*");
                    $this->db2->from($this->name);
                    $this->db2->where_in($this->primary, $ids );
                    $old = $this->db2->sheet();
                }
                $hook = $this->crud_before_edit->$method($oper, $this, $save, $old);
                if ( !$hook->result ) {
                    $ret->message = $hook->message;
                    return $ret;
                }
            }

            if ($oper == 'create') {
                $ids = $this->save($save);
                $ret->id = $ids;
                if ($this->tree && $this->tree) {
                    foreach($ids as $id) {
                        $this->build_all_tree_code($table, $id, $this->pid);
                    }
                }
                $ret->message = null;
            } else if ($oper == 'set') {
                if ($this->pid && isset($data[$this->pid])) {
                    if (! $this->check_pid_confilct ( $ids, $data [$this->pid] )) {
                        $ret->message = "数据冲突";
                        return $ret;
                    }
                }
                $this->save($save);
                if ($this->pid && $this->tree) {
                    foreach ( $ids as $id ) {
                        $this->build_all_tree_code ( $table, $id, $this->pid );
                    }
                }
                $ret->message = null;
            }

            if (method_exists($this->crud_after_edit, $this->crud_table['after_edit'])) {
                $method = $this->crud_table['after_edit'];
                $hook = $this->crud_after_edit->$method($oper, $this, $save, $ids);
                if(!$hook->result) {
                    $ret->message = $hook->message;
                    return $ret;
                }
            }

            if ($this->crud_table['fields_return'] || $oper=="create") {
                $this->pop_cache();
                $this->where_in("a.{$this->primary}", $ids);
                $result = $this->sheet();
                if ($oper =="set"){
                    $fields = explode(",", $this->crud_table['fields_return']);
                    $newdata = $this->sheet_to_grid($result, false, true, $fields);
                } else {
                    $newdata = $this->sheet_to_grid($result);
                }

                $ret->data = $newdata;
            } else {
                $ret->data = null;
            }
            break;
        case 'delete' :
            //TODO: change delete behavior
            if($this->crud_table['_role_d']) {
                if ($this->tree) {
                    //TODO check depends
                    $ret->message = "无法删除";
                    return $ret;
                }
                if (method_exists($this->crud_before_del, $this->crud_table['before_del'])) {
                    $method = $this->crud_table['before_del'];
                    $this->db2->select("*");
                    $this->db2->from($this->name);
                    $this->db2->where_in($this->primary, $ids );
                    $old = $this->db2->sheet();
                    $hook = $this->crud_before_del->$method($oper, $this, $ids, $old);
                    if ( !$hook->result ) {
                        $ret->message = $hook->message;
                        return $ret;
                    }
                }
                if (count($ids)) {
                    $this->where_in ( 'id', $ids );
                    $this->delete ( $table );
                    if (method_exists($this->crud_after_del, $this->crud_table['after_del'])) {
                        $method = $this->crud_table['after_del'];
                        $hook = $this->crud_after_del->$method($oper, $this, $ids);
                        if ( !$hook->result ) {
                            $ret->message = $hook->message;
                            return $ret;
                        }
                    }
                    $ret->message = null;
                } else {
                    $ret->message = "无法删除";
                }
            }
            break;
        default :
            $ret->message = "不支持的操作";
            break;
		}
		return $ret;
	}
	
	
    function grid_filter_items()
    {
        $items =array(
            (object)array(
                "id" => "grp1",
                "sub" => array(),
                "caption" => "grp1"
            )
        );
        $items[0]->sub[] = (object) array(
            "id" => "filter",
            "image" => "@xui_ini.appPath@image/filter.png",
            "caption" => "搜索"
        );
        $json = json_encode($items);

// 		return $this->filter($json);
        return $json;
    }

	function grid_toolbar_items($extra_items = array())
	{
		$items =array(
				(object)array(
						"id" => "grp1",
						"sub" => array(),
						"caption" => "grp1"
				)
		);
		if ($this->crud_table['_role_c']) {
			$items[0]->sub[] = (object) array(
					"id" => "new",
					"image" => "@xui_ini.appPath@image/new.png",
					"caption" => "增加"
			);
		}
		if ($this->crud_table['_role_u']) {
			$items[0]->sub[] = (object) array(
					"id" => "edit",
					"image" => "@xui_ini.appPath@image/edit.png",
					"caption" => "修改",
					"disabled" => true
			);
		}
		if ($this->crud_table['_role_d']) {
			$items[0]->sub[] = (object) array(
					"id" => "delete",
					"image" => "@xui_ini.appPath@image/delete.png",
					"caption" => "删除",
					"disabled" => true
			);
		}
		if ($this->filter) {
			$items[0]->sub[] = (object) array(
					"id" => "filter",
					"image" => "@xui_ini.appPath@image/filter.png",
					"caption" => "搜索"
			);
		}
		if ($this->group) {
			$items[0]->sub[] = (object) array(
					"id" => "group",
					"image" => "@xui_ini.appPath@image/group.png",
					"caption" => $_SESSION['groupinfo']['groupname'],
					"gid" => $_SESSION['groupinfo']['id'],
					"type" => "dropButton"
			);
			$items[0]->sub[] = (object) array(
					"id" => "sub",
					"image" => "@xui_ini.appPath@image/sub.png",
					"caption" => "显示子组数据",
					"type" => "statusButton"
			);
		}
		
		if ($this->export) {
			$items[0]->sub[] = (object) array(
					"id" => "export",
					"image" => "@xui_ini.appPath@image/export.png",
					"caption" => "导出"
			);
		}
		
		if ($this->import) {
			$items[0]->sub[] = (object) array(
					"id" => "import",
					"image" => "@xui_ini.appPath@image/export.png",
					"caption" => "导入"
			);
		}
		if (count($extra_items) > 0) {
            $items[1] = (object)array(
                    "id" => "grp2",
                    "sub" => array(),
                    "caption" => "grp2"
                );
            foreach($extra_items as $k=>$item){
                $items[1]->sub[] = (object) array(
                    "id" => "custom{$k}",
                    "image" => "@xui_ini.appPath@image/{$item['icon']}",
                    "caption" => "{$item['name']}",
                    "app" => "{$item['app']}"
                );
            }
        }
		$json = json_encode($items);
		
// 		return $this->filter($json);
        return $json;
	}

}
