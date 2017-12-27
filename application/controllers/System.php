<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System extends MY_Controller {
	function __construct()
	{
		parent::__construct();
		$this->key = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$this->paras = json_decode($paras_json);
	}

	public function index()
	{
		$this->reply(403, "Forbid");
	}

	public function request()
	{
		$method = "request_{$this->paras->action}";
		if (method_exists($this, $method)) {
			$data = $this->$method();
			$this->reply(200, "Success", $data);
		} else {
			$this->reply(404, "Not Found");
		}
	}

	function request_tables()
	{
		$this->db->select("id,name,caption,w,h");
		$this->db->from('crud_table');
		$ret = $this->db->sheet();
		return $ret;
	}

	
	private function request_table_permission()
	{
		$ret = new stdClass();
		$tr = $this->paras->type;
		$this->db->from('user_role');
		$this->db->order_by('id', 'ASC');
		$roles = $this->db->sheet();

		$this->db->from('crud_table');
		$tables = $this->db->sheet();

		$headers = array(
			(object) array(
				"id" => 'name',
				"caption" => "数据表",
				"editable" => false,
				"width" => 200
			)
		);
		$rows = array();
		foreach($roles as $r) {
			$h = (object) array(
				"id" => $r['id'],
				"caption" => $r['rolename'],
				"type"  => "checkbox",
				"editable"  => true
			);
			$headers[] = $h;
		}
		foreach($tables as $t){
			$row = (object) array(
				"id" => $t['id'],
				"cells" => array()
			);
			$row->cells[] = $t['caption'];
			foreach($roles as $r) {
				if (strpos(",{$t[$tr]},", ",{$r['id']},") !== false) {
					$row->cells[] = true;
				} else {
					$row->cells[] = false;
				}
			}
			$rows[] = $row;
		};

		$ret->headers = $headers;
		$ret->rows = $rows;
		return $ret;
	}

	private function request_table_permission_save()
	{
		$tr = $this->paras->type;
		foreach($this->paras->data as $d) {
			$data = $this->db->get_where('crud_table', array('id' => $d->id))->row_array();
			$role = explode(",", $data[$tr]) ;

			foreach($d->fields as $f) {
				if (!$f->value && in_array($f->role, $role)) {
					array_splice($role, array_search($f->role, $role), 1);
				} else if ($f->value && !in_array($f->role, $role)) {
					$role[] = $f->role;
				}
			}
			$this->db->update('crud_table', array($tr => implode(',', $role)), array('id' => $d->id));

			if ($tr == 'role_r' || $tr == 'role_u'){
				$data = $this->db->get_where('crud_field', array('tid' => $d->id))->result_array();
				foreach($data as $d2) {
					$role = explode(",", $d2[$tr]);
					foreach($d->fields as $f) {
						if (!$f->value && in_array($f->role, $role)) {
							array_splice($role, array_search($f->role, $role), 1);
						} else if ($f->value && !in_array($f->role, $role)) {
							$role[] = $f->role;
						}
						$this->db->update('crud_field', array($tr => implode(',', $role)), array('id' => $d2['id']));
					}
				}
			}

		}
		return 1;
	}
	private function request_field_permission()
	{
		$ret = new stdClass();
		$tid = $this->paras->tid;
		$tr = $this->paras->type;
		$this->db->from('user_role');
		$this->db->order_by('id', 'ASC');
		$roles = $this->db->sheet();

		$this->db->from('crud_field');
		$this->db->where('tid', $tid);
		$fields = $this->db->sheet();

		$headers = array(
			(object) array(
				"id" => 'name',
				"caption" => "数据字段",
				"editable"  => false,
				"width" => 200
			)
		);
		$rows = array();
		foreach($roles as $r) {
			$h = (object) array(
				"id" => $r['id'],
				"caption" => $r['rolename'],
				"type"  => "checkbox",
				"editable"  => true
			);
			$headers[] = $h;
		}
		foreach($fields as $f){
			$row = (object) array(
				"id" => $f['id'],
				"cells" => array()
			);
			$row->cells[] = $f['caption'];
			foreach($roles as $r) {
				if (strpos(",{$f[$tr]},", ",{$r['id']},") !== false) {
					$row->cells[] = true;
				} else {
					$row->cells[] = false;
				}
			}
			$rows[] = $row;
		};

		$ret->headers = $headers;
		$ret->rows = $rows;
		return $ret;
	}

	private function request_field_permission_save()
	{
		$tr = $this->paras->type;
		foreach($this->paras->data as $d) {
			$data = $this->db->get_where('crud_field', array('id' => $d->id))->row_array();
			$role = explode(",", $data[$tr]) ;

			foreach($d->fields as $f) {
				if (!$f->value && in_array($f->role, $role)) {
					array_splice($role, array_search($f->role, $role), 1);
				} else if ($f->value && !in_array($f->role, $role)) {
					$role[] = $f->role;
				}
			}
			$this->db->update('crud_field', array($tr => implode(',', $role)), array('id' => $d->id));
		}
		return 1;
	}

	private function request_user_group()
	{
		$ret = new stdClass ();
		$this->load->library( 'xui_utils' );
		$this->load->model ('grid_model');
		$this->grid_model->table("user_group");
		$this->grid_model->prepare();
		$tree = $this->grid_model->get_tree_data_by_id(2, true);
		$data = $this->build_user_tree($tree, 'groupname');
		$ret->items = $data;
		return $ret;
	}
	
	private function build_user_tree($data, $caption)
	{
		$ret = array(
			"id" => "g{$data['id']}",
			"caption" => $data[$caption],
		);
		if (isset($data['children']) && count($data['children'])) {
			$ret['sub'] = array();
			foreach ($data['children'] as $c) {
				$ret['sub'][] = $this->build_user_tree($c,$caption);
			}
		}
		
		$users = $this->db->get_where('user', array('gid' => $data['id']))->result_array();
		if ($users) {
			if (!isset($ret['sub'])) {
				$ret['sub'] = array();
			}
			foreach ($users as $u) {
				$ret['sub'][] = array(
					'id' => "u{$u['id']}",
					'caption' => $u['username']
				);
			}
		}
		
		return $ret;
	}
	
	private function request_setting_get()
	{
		$ret = new stdClass();
		$settings = $this->auth_model->user_get_settings();
		$ret->workyear = $settings->workyear;
		return $ret;
	}
	
	private function request_setting_set()
	{
		$settings = $this->auth_model->user_get_settings();
		$settings->workyear = (int)$this->paras->workyear;
		$this->auth_model->user_update_settings($settings);
		return 1;
	}
	
	private function request_notify_setting_get()
	{
		$ret = new stdClass();
		$setting = $this->db->get("setting", array('id'=>1))->row_array();
		unset($setting['id']);
		foreach($setting as $k=>$v) {
			if (strstr($k, "Enable")) {
				$v = ($v == 1);
			}
			$ret->$k = (object) array(
				"value" => $v
			);
		}
		return $ret;
	}
	
	private function request_notify_setting_set()
	{
		unset ($this->paras->action);
		$save = (array)$this->paras;
		$this->db->update('setting', $save, array('id'=>1));
		return 1;
	}
	
	private function request_get_userlist()
	{
		return $this->auth_model->userlist();
	}
	
	private function request_user_switch_to()
	{
		$uid = (int)substr($this->paras->user, 1);
		$msg = $this->auth_model->user_switch_to($uid);
		if ($msg) {
			$this->reply(403, $msg);
		}
		return 0;
	}
	
	private function request_user_switch_back()
	{
		$msg = $this->auth_model->user_switch_back();
		if ($msg) {
			$this->reply(403, $msg);
		}
		return 0;
	}
	
	private function request_clear_cache()
	{
		if ($this->auth_model->is_admin() || $this->auth_model->is_super()) {
			$this->cache->clean();
		}
	}
	
	private function request_update_shortkey()
	{
		$settings = $this->auth_model->user_get_settings();
		$settings->shortkey = $this->paras->shortkey;
		$this->auth_model->user_update_settings($settings);
	}
	
	private function request_toolbar()
	{
		$rsp = new stdClass();
		$items = array(
			(object) array(
				"id" => "grp1",
				"sub" => array(),
				"caption" => "grp1"
			)
		);
		if ($this->auth_model->is_switch()) {
			$items[0]->sub[] = (object) array(
				"id" => "switch_back",
				"image" => "@xui_ini.appPath@image/switch.png",
				"caption" => "返回原始用户"
			);
		} else if ($this->auth_model->is_admin() || $this->auth_model->is_super()) {
			$items[0]->sub[] = (object) array(
				"id" => "switch_to",
				"image" => "@xui_ini.appPath@image/switch.png",
				"caption" => "切换用户"
			);
			$items[0]->sub[] = (object) array(
				"id" => "clear_cache",
				"image" => "@xui_ini.appPath@image/clear_cache.png",
				"caption" => "清除缓存"
			);
		}
		
		$items[0]->sub[] = (object) array(
			"id" => "setting",
			"image" => "@xui_ini.appPath@image/setting.png",
			"caption" => "设置"
		);
		$items[0]->sub[] = (object) array(
			"id" => "userinfo",
			"image" => "@xui_ini.appPath@image/user.png",
			"caption" => $_SESSION['userinfo']['username']
		);
		$items[0]->sub[] = (object) array(
			"id" => "logout",
			"image" => "@xui_ini.appPath@image/logout.png",
			"caption" => "退出"
		);
		$rsp->toolbar = $items;
		$rsp->settings = $this->auth_model->user_get_settings();
		return $rsp;
	}
}
