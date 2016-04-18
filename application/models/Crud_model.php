<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );

/**
 * Crud_model class.
 *
 * @extends CI_Model
 */
class Crud_model extends CI_Model {

	var $dbContext = null;
	const TYPE_TEXT = 0;
	const TYPE_READONLY = 1;
	const TYPE_SELECT = 2;
	const TYPE_MULTI = 3;
	const TYPE_BOOL = 4;
	const TYPE_TEXTAREA = 5;
	const TYPE_PASSWORD = 6;
	const TYPE_DATE = 7;
	const TYPE_DATETIME = 8;
	
	const ALIGN_LEFT = 0;
	const ALIGN_CENTER = 1;
	const ALIGN_RIGHT = 2;
	
	const PROP_FIELD_FILTER = 0x0001;
	const PROP_FIELD_HIDDEN = 0x0002;
	const PROP_FIELD_LITE = 0x0004;
	const PROP_FIELD_UNIQUE = 0x0008;
	
	public function __construct() {
		parent::__construct ();
		$this->load->database ();
		$this->load->helper ( 'bool' );
		$this->load->library( 'session' );
		$this->load->driver('cache', array('adapter' => 'file', 'key_prefix' => "u{$_SESSION['userinfo']['id']}_"));
	}
	public function install($table) {
		$fields = $this->db->field_data ( $table );						//get all fields in table
		if (count ( $fields ) > 0) {
			$this->db->select ( 'id' );
			$this->db->where ( 'name', $table );
			
			$tid = $this->db->get ( 'crud_table' )->row ( 'id' );
			if (! $tid) {
				$data = array (
						'name' => $table,
						'caption' => $table 
				);
				foreach ( $fields as $f ) {
					if ($f->name === 'pid') {							//if table have pid field, set pid_field
						$data['pid_field'] = 'pid';
					}
				}
				$this->db->insert ( 'crud_table', $data );
				$tid = $this->db->insert_id ();
			}
			if ($tid) {
				$this->db->select ( 'id,name' );
				$this->db->where ( 'tid', $tid );						//get all fields in crud_field table
				$exist_fields = $this->db->get ( 'crud_field' )->result_array ();
				
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
								'type' => Crud_model::TYPE_TEXT,
								'prop' => 0,
								'search_option' => 0x0003 
						);
						if ($f->name === 'id') {
							$data ['type'] = Crud_model::TYPE_READONLY;
							$data ['prop'] |= Crud_model::PROP_FIELD_HIDDEN;
						}

						if ($f->name === 'uid') {
							$data ['type'] |= Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'username';
						}
						if ($f->name === 'gid') {
							$data ['type'] |= Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_group';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'groupname';
						}
						if ($f->name === 'rid') {
							$data ['type'] |= Crud_model::TYPE_SELECT;
							$data['join_table'] = 'user_role';
							$data['join_value'] = 'id';
							$data['join_caption'] = 'rolename';
						}
						if ($f->name === 'tree_code') {
							$data ['type'] = Crud_model::TYPE_READONLY;
							$data ['prop'] |= Crud_model::PROP_FIELD_HIDDEN;
						}
						if ($f->name === 'pid') {
							$data ['type'] |= Crud_model::TYPE_SELECT;
							$data['join_table'] = $table;
							$data['join_value'] = 'id';
							$data['join_caption'] = 'id';
						}
						$this->db->insert ( 'crud_field', $data );
					}
				}
				foreach ( $exist_fields as $k => $ef ) {				//delete not exist field
					$this->db->where ( 'id', $ef ['id'] );
					$this->db->delete ( 'crud_field' );
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
	public function table($name) {
		$cache_id = "table_{$name}";
		if ( ! $dbContext = $this->cache->get($cache_id)) {
			$this->db->select ( '*' );
			$this->db->where ( 'name', $name );
			$crud_field = array();
			$crud_table = $this->db->get ( 'crud_table', 1 )->row_array ();
			if ($crud_table) {
				$crud_table['_role_c'] = $this->check_role($crud_table['role_c'] );
				$crud_table['_role_r'] = $this->check_role($crud_table['role_r'] );
				$crud_table['_role_u'] = $this->check_role($crud_table['role_u'] );
				$crud_table['_role_d'] = $this->check_role($crud_table['role_d'] );
				$this->db->select ( '*' );
				$this->db->where ( 'tid', $crud_table ['id'] );
				$this->db->order_by ( 'seq', 'asc' );
				$crud_field = $this->db->get ( 'crud_field' )->result_array ();
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
						|| $f ['type'] == Crud_model::TYPE_BOOL) {
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
	
	public function jqgrid($dbContext)
	{
		if (!$dbContext) {
			return false;
		}
		$table = $dbContext->name;
		$data = array();
		$data['jqgrid'] = "jqgrid_{$table}";
		$data['jqpager'] = "jqpager_{$table}";
		$data['jqdata'] = "jqdata_{$table}";
		$data['jqgid'] = "jqgid_{$table}";
		$data['jqgid_value'] = $_SESSION['userinfo']['gid'];
		
		$this->load->helper ( 'url' );
		$base_url = base_url ();
		
		$dataurl = site_url ( "crud/data" );
		$editurl = site_url ( "crud/edit" );
		$editdataurl = site_url ( "crud/editdata" );
		$resizeurl = site_url ( "crud/resize" );
		
		$recreateForm = false;
		$colNames = array ();
		$colModel = array ();
		
		$search = false;
		$field_index = 0;
		foreach ( $dbContext->crud_field as &$f ) {
			$editOptions = array ();
			$formOptions = array ();
			
			if(!$f['_role_r']) {
				continue;
			}
			$colNames [] = "'{$f['caption']}'";
			$cm = array ();
			$fp = array ();
			if ($f ['type'] != Crud_model::TYPE_READONLY) {
				$cm [] = "editable:true";
			}
			if (!$f['_role_u'] ) {
				$editOptions[] = "readonly: true";
			}
			if ($f ['name'] === 'id') {
				$cm [] = "key:true";
			}
			if ($f ['prop'] & Crud_model::PROP_FIELD_HIDDEN) {
				$cm [] = "hidden:true";
			}
			if ($f ['prop'] & Crud_model::PROP_FIELD_LITE ) {
				$cm [] = "hidden:true";
				$cm [] = "editrules: {edithidden:true}";
			}
			$cm [] = "name:'{$f['name']}'";
			$cm [] = "index:'{$f['name']}'";
			$cm [] = "width:{$f['width']}";
			switch ($f ['align']) {
				case Crud_model::ALIGN_RIGHT :
					$cm [] = "align:'right'";
					break;
				case Crud_model::ALIGN_CENTER :
					$cm [] = "align:'center'";
					break;
				case Crud_model::ALIGN_LEFT :
				default :
					$cm [] = "align:'left'";
					break;
			}
			switch ($f ['type']) {
				case Crud_model::TYPE_PASSWORD :
					$cm [] = "edittype:'password'";
					break;
				case Crud_model::TYPE_SELECT :
					// if ($this->dm->grade && 'gid' == $f ['name']) {
					// $editOptions[] = "defaultValue:function(){ return gid;}";
					// } else if ($this->dm->tree && $this->dm->pfield == $f ['name']) {
					if ($dbContext->pid && $dbContext->pid == $f ['name']) {
						$editOptions [] = "defaultValue:function(){ var id=$(this).jqGrid('getGridParam', 'selrow');id=id?id:0;return id;}";
					}
				// NO BREAK HERE
				case Crud_model::TYPE_BOOL :
				case Crud_model::TYPE_MULTI :
					if ($f ['join_table'] === $dbContext->name) {
						$recreateForm = true;
					}
					$cm [] = "edittype:'select'";
					$cm [] = "stype:'select'";
					if ($f ['type'] == Crud_model::TYPE_MULTI || $f ['type'] == Crud_model::TYPE_BOOL) {
						$editOptions [] = "multiple:true";
					}
					$editOptions [] = "dataUrl:\"{$editdataurl}?t={$table}&f=\"+encodeURIComponent(\"{$f['name']}\")";
					$editOptions [] = "dataInit: select2_init";
					if (!$recreateForm) {
						$editOptions [] = "cacheUrlData:true";
					}
					break;
				case Crud_model::TYPE_TEXTAREA :
					$cm [] = "edittype:'textarea'";
					break;
				case Crud_model::TYPE_DATE :
					$cm[] = "formatter:'date'";
					$fp[] = "reformatAfterEdit:true";
					$fp[] = "srcformat:'Y-m-d'";
					$fp[] = "newformat:'Y-m-d'";
					$editOptions[] = "dataInit:
							function(elm){setTimeout(function(){
								jQuery(elm).datepicker({dateFormat:'yy-mm-dd',beforeShow: function(i) { if ($(i).attr('readonly')) { return false; } } });
                    			jQuery('.ui-datepicker').css({'font-size':'75%'});
                			},200);}";
					break;
				case Crud_model::TYPE_DATETIME :
					$cm[] = "formatter:'date'";
					$fp[] = "reformatAfterEdit:true";
					$fp[] = "srcformat:'Y-m-d H:i:s'";
					$fp[] = "newformat:'Y-m-d H:i:s'";
					$editOptions[] = "dataInit:
					function(elm){setTimeout(function(){
						jQuery(elm).datetimepicker({dateFormat:'yy-mm-dd', timeFormat:'HH:mm:ss',beforeShow: function(i) { if ($(i).attr('readonly')) { return false; } } });
                    	jQuery('.ui-datepicker').css({'font-size':'75%'});
                	},200);}";
					break;
				default :
					break;
			}
			
			$cm [] = "formatoptions:{" . implode ( ",", $fp ) . "}";
			$cm [] = "editoptions:{" . implode ( ",", $editOptions ) . "}";
			if ($f ['prop'] & Crud_model::PROP_FIELD_FILTER) {
				$searchOptions = Array ();
				if ($f ['type'] == Crud_model::TYPE_SELECT) {
					$searchOptions [] = "dataUrl:\"{$editdataurl}?t={$table}&f=\"+encodeURIComponent(\"{$f['name']}\")";
					$searchOptions [] = "dataInit: select2_init";
					// } else if ($f ['type'] == Crud_model::TYPE_DATE) {
					// $searchOptions[] = "dataInit:function(el){search_datetime($(el), 'yyyy-MM-dd'); }";
					// } else if ($f ['type'] == Crud_model::TYPE_DATETIME) {
					// $searchOptions[] = "dataInit:function(el){ $(el).addClass('filter_datetime').attr('data-format', 'yyyy-MM-dd hh:mm:ss')}";
				}
				$sopt = array();
				$sopts = array(
						'eq' => 0x0001,
						'ne' => 0x0002,
						'lt' => 0x0004,
						'le' => 0x0008,
						'gt' => 0x0010,
						'ge' => 0x0020,
						'cn' => 0x0040,
						'nc' => 0x0080,
						'bw' => 0x0100,
						'bn' => 0x0200,
						'ew' => 0x0400,
						'en' => 0x0800,
						'in' => 0x1000,
						'ni' => 0x2000,
						'nu' => 0x4000,
						'nn' => 0x8000,
				);
				foreach ( $sopts as $k => $opt ) {
					if ( $f ['search_option']  & $opt) {
						array_push($sopt, "'{$k}'");
					}
				}
				$searchOptions [] = "sopt:[" . implode ( ",", $sopt ) . "]";
				$cm [] = "searchoptions:{" . implode ( ",", $searchOptions ) . "}";
				$search = true;
			} else {
				$cm [] = "search:false";
			}
			$formOptionsRow = ($field_index >> 1)  + 1;
			$formOptionsCol = ($field_index % 2)  + 1;
			$formOptions[] = "rowpos: {$formOptionsRow}";
			$formOptions[] = "colpos: {$formOptionsCol}";
			$cm [] = "formoptions:{".implode ( ",", $formOptions ) . "}";
			$colModel [] = "{" . implode ( ",", $cm ) . "}";
			
			if ($f ['type'] != Crud_model::TYPE_READONLY && !($f ['prop'] & Crud_model::PROP_FIELD_HIDDEN)) {
				$field_index++;
			}
		}
		
		$colNames = implode ( ",", $colNames );
		$colModel = implode ( ",\n\t\t\t\t", $colModel );
		
		$parm = Array ();
		$parm [] = "mtype: 'POST'";
		$parm [] = "width: $('#tab_main').width()";
		$parm [] = "url:'{$dataurl}'";
		$parm [] = "datatype:'json'";
		$parm [] = "multiselect:true";
		$parm [] = "colNames:[{$colNames}]";
		$parm [] = "colModel:[{$colModel}]";
		$parm [] = "rowNum: 20";
		$parm [] = "rowList:[20,50,100,500]";
		$parm [] = "height: $('#tabs_div').height() - 170";
		$parm [] = "pager: '#{$data['jqpager']}'";
		$parm [] = "sortname: 'id'";
		$parm [] = "hidegrid: false";
		$parm [] = "viewrecords: true";
		$parm [] = "shrinkToFit: true";
		$parm [] = "sortorder: 'asc'";
		$parm [] = "editurl: '{$editurl}'";
		$parm [] = "serializeGridData: {$data['jqdata']}.grid_data";
		$parm [] = "onSelectRow: {$data['jqdata']}.select_row";
		$parm [] = "resizeStop:{$data['jqdata']}.resize";
		$parm [] = "storeNavOptions:true";
		$parm [] = "ondblClickRow:function(id){ $(this).jqGrid('editGridRow', id, $('#{$data['jqgrid']}').jqGrid('getGridParam', 'editOptions') ); }";
		if ($dbContext->group) {
			$parm [] = "caption: \" {$dbContext->crud_field['gid']['caption']}<select id='{$data['jqgid']}_s'>".implode ( "", $dbContext->groupTreeOption )."</select>\"";
		}

		if ($dbContext->pid) {
			$parm[] = "treeGrid:true";
			$parm[] = "treedatatype:'json'";
			$parm[] = "treeGridModel:'adjacency'";
			$parm[] = "ExpandColumn:'{$dbContext->crud_table['expand_field']}'";
		}
		// $parm[] = "treeIcons:";
		$prmedit = array ();
		$prmadd = array ();
		$prmdel = array ();
		$prmsearch = array ();
		$prmenable = array ();
		
		if ($dbContext->crud_table['_role_u']) {
			$prmedit [] = "width:650";
			$prmedit [] = "onclickPgButtons: function(){ $(\"#{$data['jqgrid']}\").jqGrid(\"resetSelection\");}";
			$prmedit [] = "afterclickPgButtons:select2_change";
			$prmedit [] = "serializeEditData:{$data['jqdata']}.grid_data";
			$prmedit [] = "beforeShowForm: {$data['jqdata']}.before_show";
			$prmedit [] = "afterSubmit: {$data['jqdata']}.jqcallback";
			$prmedit [] = "closeAfterEdit:true";
			$prmedit [] = "closeOnEscape:true";
			// if ($this->dm->table_info ['prop'] & Crud_model::PROP_TABLE_NEW_WINDOW ) {
			// $prmedit[] = "beforeInitData: function(){debugger;return open_new_window(this, true);}";
			// }
			if ($recreateForm) {
				$prmedit [] = "recreateForm : true";
			}
		} else {
			$prmenable[] = "edit: false";
		}
		
		if ($dbContext->crud_table['_role_c']) {
			$prmadd [] = "width:650";
			$prmadd [] = "beforeShowForm: function(){
$('.CaptionTD .form-lock').remove();
$('.DataTD').children().prop('disabled', false).removeClass('datatd-disabled');
}";
			$prmadd [] = "serializeEditData:{$data['jqdata']}.grid_data";
			$prmadd [] = "afterSubmit: {$data['jqdata']}.jqcallback";
			$prmadd [] = "closeOnEscape:true";
			// if (check_bool ( $this->dm->table_info ['prop'], Crud_model::PROP_TABLE_NEW_WINDOW )) {
			// $prmadd[] = "beforeInitData: function(){return open_new_window(this, false);}";
			// }
			if ($recreateForm) {
				$prmadd [] = "recreateForm : true";
			}
		} else {
			$prmenable [] = "add: false";
		}
		
		if ($dbContext->crud_table ['_role_d']) {
			$prmdel [] = "serializeDelData:{$data['jqdata']}.grid_data";
			$prmdel [] = "afterSubmit: {$data['jqdata']}.jqcallback";
		} else {
			$prmenable [] = "del: false";
		}
		
		if ($search) {
			$prmsearch [] = "multipleSearch:true";
			$prmsearch [] = "multipleGroup:true";
			$prmsearch [] = "showQuery: false";
			$prmsearch [] = "width:600";
			// $prmsearch[] = "afterRedraw: filter_datetime";
			// $prmsearch[] = "afterChange: filter_datetime";
		} else {
			$prmenable [] = "search: false";
		}
		
		$prmenable [] = "addtext: '添加'";
		$prmenable [] = "deltext: '删除'";
		$prmenable [] = "edittext: '编辑'";
		$prmenable [] = "searchtext: '查找'";
		$prmenable [] = "refreshtext: '刷新'";
		$data ['base_url'] = $base_url;
		$data ['resizeurl'] = $resizeurl;
		$data ['table'] = $table;
		$data ['parm'] = implode ( ",\n\t\t", $parm );
		$data ['prmedit'] = implode ( ",\n\t\t\t", $prmedit );
		$data ['prmadd'] = implode ( ",\n\t\t\t", $prmadd );
		$data ['prmdel'] = implode ( ",\n\t\t\t", $prmdel );
		$data ['prmsearch'] = implode ( ",\n\t\t\t", $prmsearch );
		$data ['prmenable'] = implode ( ",\n\t\t\t", $prmenable );
		
		if ($dbContext->group) {
			$data ['group'] = true;
		} else {
			$data ['group'] = false;
		}
		
		// $this->cache->save ( $cacheid, $data, $cache_time );
		
		return $data;
	}
	public function data($dbContext)
	{
		if (!$this->prepare($dbContext)) {
			return false;
		}
		$table = $dbContext->name;
		$response = new stdClass ();
		$rows = array ();
		
		//paging
		$pageIndex = (int)$this->input->post("page");
		$pageRows = $this->input->post_get("rows");
		$start = ($pageIndex - 1) * $pageRows;
		$data= array();
		$response->records = $dbContext->db->count_all_results();
		$dbContext->db->limit($pageRows, $start);
		
		//sort
		$sort = $this->input->post_get("sidx");
		$sord = $this->input->post_get("sord");
		$sord = ($sord === 'asc')?'asc':'desc';
		if (isset($dbContext->crud_field[$sort])) {
			if ($dbContext->crud_field[$sort]['type'] == Crud_model::TYPE_SELECT) {
				$dbContext->db->order_by("{$dbContext->crud_field[$sort]['_joined']}", $sord);
			} else {
				$dbContext->db->order_by("{$dbContext->name}.{$sort}", $sord);
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
		$dbContext->db->stash_cache();
		
		if ($dbContext->pid) {
			$nodeid = (int)$this->input->post('nodeid');
			$level = (int)$this->input->post('n_level');
			if ($dbContext->search == false) {
				$dbContext->db->where("{$table}.{$dbContext->pid}", $nodeid);
			}
		}
		
		$data = $dbContext->db->get ()->result_array ();
		$response->sql = $dbContext->db->get_compiled_select();
		foreach ( $data as $d ) {
			$row = array ();
			$tmp = array ();
			foreach ( $dbContext->crud_field as $k => $f ) {
				if(!$f['_role_r']) {
					continue;
				}
				// if (function_exists ( $f['process'] )) {
				// $d [$k] = $f['process'] ( $d [$k] );
				// }
				if ($f ['type'] == Crud_model::TYPE_SELECT) {
					array_push ( $tmp, $d [$f ['_joined']] );
				} else if ($f ['type'] == Crud_model::TYPE_PASSWORD) {
					array_push ( $tmp, "******" );
				} else if ($f ['type'] == Crud_model::TYPE_MULTI) {
					$new_val = array ();
					$select_data = $f['_joined_data'];
					$vals = explode ( ",", $d [$k] );
					foreach ( $vals as $v ) {
						if (isset ( $select_data [$v] )) {
							$new_val [] = $select_data [$v]['_option'];
						}
					}
					array_push ( $tmp, implode ( ",", $new_val ) );
				} else if ($f ['type'] == Crud_model::TYPE_BOOL) {
					$new_val = array ();
					$select_data = $f['_joined_data'];
					foreach ($select_data as $sk=>$sv) {
						if (((int)$d [$k]) & ((int)$sk)) {
							$new_val[] = $sv['_option'];
						}
					}
					
					array_push ( $tmp, implode ( ",", $new_val ) );
				} else {
					array_push ( $tmp, $d [$f ['name']] );
				}
			}
			if ($dbContext->pid) {
				array_push ( $tmp, $level + 1 );						// level
				if ($nodeid) {
					array_push ( $tmp, $nodeid );						//parent id
				} else { 
					array_push ( $tmp, NULL );							// no parent
				}
				$dbContext->db->pop_cache();
				$dbContext->db->where("{$table}.{$dbContext->pid}", $d['id']);
				$child_count = $dbContext->db->count_all_results();
				if ($child_count > 0) {
					array_push ( $tmp, false );							// not isLeaf
				} else {
					array_push ( $tmp, true );							// isLeaf
				}
			
				array_push($tmp, false); //expanded
			
			}
			$row ['id'] = $d ['id'];
			$row ['cell'] = $tmp;
			
			array_push ( $rows, $row );
		}
		$response->page = $pageIndex;
		$response->total = ( int ) ($response->records / $pageRows) + (($response->records % $pageRows) > 0 ? 1 : 0);
		$response->rows = $rows;
		return $response;
	}
	public function edit($dbContext, $oper, $ids, $post)
	{
		if (!$this->prepare($dbContext)) {
			return false;
		}
		$this->load->library('crud_hook');
		$table = $dbContext->name;
		switch ($oper) {
			case 'edit' :
			case 'add' :
				$data = array ();
				foreach ( $dbContext->crud_field as $k => $f ) {
					if(!$f['_role_u']) {
						continue;
					}
					if (! isset ( $post [$k] ) || $f ['type'] == Crud_model::TYPE_READONLY) {
						continue;
					}
					
					$data [$k] = $post [$k];
					if ($f ['type'] == Crud_model::TYPE_PASSWORD && $data [$k] === "******") {
						unset ( $data [$k] );
						continue;
					}
					
					if ($f ['type'] == Crud_model::TYPE_MULTI) {
					$data_in = explode(',', $data [$k]);
						$data [$k] = '';
						foreach ($data_in as $d) {
							if (isset($f ['_joined_data'][$d])) {
								$data [$k] .= "{$d},";
							}
						}
					}
					if ($f ['type'] == Crud_model::TYPE_BOOL) {
						$data_in = explode(',', $data [$k]);
						$data [$k] = 0;
						foreach ($data_in as $d) {
							if (isset($f ['_joined_data'][$d])) {
								$data [$k] |= (int)$d;
							}
						}
					}
					
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
						
						if ($oper=="edit") {
							if (count($ids) > 1) {
								unset ( $data [$k] );
								continue;
							} else {
								if (count($exist) && $exist[0]['id'] != $ids[0]) {
									die("已存在重复数据2");
								}
							}
						} else if ($oper=="add") {
							if (count($exist)) {
								die("已存在重复数据1");
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
				
				if ($oper == "add") {
					if (count($data)) {
						$dbContext->db->insert ( $table, $data );
						$id = $dbContext->db->insert_id ();
					}
					if ($dbContext->tree) {
						$this->build_all_tree_code($table, $id, $dbContext->pid);
					}
				} else if ($oper == "edit" && count($data)) {
					$dbContext->db->trans_start ();
					$dbContext->db->where_in ( 'id', $ids );
					$dbContext->db->update ( $table, $data );
					$dbContext->db->trans_complete ();
					
					if ($dbContext->pid) {
						if (! $this->check_pid_confilct ( $dbContext, $ids, $data [$dbContext->pid] )) {
							echo "Check Failed";
						} else if ($dbContext->tree) {
							foreach ( $ids as $id ) {
								$this->build_all_tree_code ( $table, $id, $dbContext->pid );
							}
						}
					}
					
				}
				break;
			case 'del' :
				//TODO: change delete behavior
				if($dbContext->crud_table['_role_d']) {
					if ($dbContext->tree) {
						//TODO check depends
						return null;
					}
					$dbContext->db->where_in ( 'id', $ids );
					$dbContext->db->delete ( $table );
				}
				break;
			default :
				break;
		}
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
				return NULL;
			}
			$field_info = $dbContext->crud_field[$field];
		}
		$join_dbContext = $this->table($field_info['join_table']);
		if (!$this->prepare($join_dbContext, false)) {
			return null;
		}
		switch ($field_info['type']) {
			case Crud_model::TYPE_SELECT :
			case Crud_model::TYPE_MULTI :
			case Crud_model::TYPE_BOOL :
				$join_dbContext->db->select ( "{$field_info['join_value']} _value,{$field_info['join_caption']} _option" );
				if ($field_info['join_condition'] != '' && $field_info['join_condition_value'] != '') {
					$join_dbContext->db->where ( $field_info['join_condition'], $field_info['join_condition_value'] );
				}
				$data = $join_dbContext->db->get ()->result_array ();
				$response = array ();
				foreach ( $data as $k => $d ) {
					$response [$d ['_value']] = $d;
				}
				if ($join_dbContext->pid) {
					$response [0] = array('_value' => 0, '_option' => '');
				}
				break;
			default :
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
		if (count ($filters->rules) != 0) {
			$is_blank = false;
		}
		foreach ($filters->rules as $r) {
			$this->_parse_rules($dbContext, $filters->groupOp, $r->field, $r->op, $r->data);
		}
		
		if (isset($filters->groups)) {
			if (count ($filters->groups) != 0) {
				$is_blank = false;
			}
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
}