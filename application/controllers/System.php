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

	private function request_workyear()
	{
		$_SESSION['userinfo']['workyear'] = (int)$this->paras->value;
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
}
