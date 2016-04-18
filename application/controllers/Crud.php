<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->model('crud_model');
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
// 		$this->crud_model->install('user');
		$dbContext = $this->crud_model->table("user");
		print_r($this->crud_model->jqgrid($dbContext));
	}
	
	public function data()
	{
// 		$this->load->helper("input");
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_rules('_table', 'Table', 'required');
		$this->form_validation->set_rules('_search', 'Search', 'required');
// 		$this->form_validation->set_rules('filters', 'Filters', 'required');
// 		$this->form_validation->set_rules('nd', 'Timestamp', 'numeric');
		$this->form_validation->set_rules('rows', 'Rows', 'numeric');
		$this->form_validation->set_rules('sidx', 'Sort ID', 'required');
		$this->form_validation->set_rules('sord', 'Sort Order', 'required');
		$this->form_validation->set_rules('page', 'Page', 'numeric');
		if ($this->form_validation->run() === false) {
			$response = array("error");
		} else {
			$table = $this->input->post_get("_table");
// 			$page = $this->input->post_get("page");
// 			$rows = $this->input->post_get("rows");
			$dbContext = $this->crud_model->table($table);
			$response = $this->crud_model->data($dbContext);
		}
		echo json_encode($response);
	}
	
	public function edit()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_rules('_table', 'Table', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required');
		$this->form_validation->set_rules('oper', 'Operate', 'required');
		if ($this->form_validation->run() === false) {
			echo "error";
		} else {
			$table = $this->input->post_get("_table");
			$oper = $this->input->post_get("oper");
			$ids = explode(",", $this->input->post_get("id"));
			$data = $this->input->post(NULL, TRUE);

			$dbContext = $this->crud_model->table($table);
			$this->crud_model->edit($dbContext, $oper, $ids, $data);
		}
		
	}
	public function editdata()
	{
		$table = $this->input->get("t");
		$field = $this->input->get("f");
		$options = $this->crud_model->get_left_join($table, $field);
// 		print_r($options);
		echo "<select>";
		foreach ($options as $o) {
			echo "<option value=\"{$o['_value']}\">{$o['_option']}</option>";
		}
		echo "</select>";
	}
	public function install()
	{
		$this->crud_model->install('user');
		$this->crud_model->install('user_group');
		$this->crud_model->install('user_role');
		$this->crud_model->install('crud_join');
		$this->crud_model->install('crud_table');
		$this->crud_model->install('crud_field');
		$this->crud_model->install('menu');
		$this->crud_model->install('test1');
		$this->crud_model->install('test2');
	}
	public function seq($table = '', $field='', $inc = 0)
	{
		$this->load->helper('url');
		$dbContext = $this->crud_model->table($table);
		if (!$dbContext) {
			$tables = $this->db->get('crud_table')->result_array();
			foreach($tables as $t) {
				$url = site_url("crud/seq/{$t['name']}");
				echo "<a href='{$url}'>{$t['caption']}</a><br>";
			}
		} else {
			$last = '';
			$seq = 0;
			foreach($dbContext->crud_field as $k=>&$f) {
				$f['seq'] = $seq;
				if ($f['name'] == $field) {
					if ($inc < 0) {
						if ($last != '') {
							$dbContext->crud_field[$last]['seq']++;
							$f['seq']--;
						}
					} else if ($inc > 0) {
						$f['seq']++;
					}
				}
				if ($last != '') {
					if ($dbContext->crud_field[$last]['seq'] == $f['seq']) {
						$f['seq']--;
					}
				}
				$last = $k;
				$seq++;
			}
			$this->db->trans_start();
			foreach($dbContext->crud_field as $k=>&$f) {
				$this->db->set ( 'seq', $f['seq'] );
				$this->db->where ( 'id', $f['id'] );
				$this->db->update ( 'crud_field');
			}
			$this->db->trans_complete();
			$dbContext = $this->crud_model->table($table);
			foreach($dbContext->crud_field as $k=>&$f) {
				$url_u = site_url("crud/seq/{$table}/{$f['name']}/-1");
				$url_d = site_url("crud/seq/{$table}/{$f['name']}/1");
				echo "{$f['caption']} &nbsp {$f['seq']} &nbsp <a href='{$url_u}'>上移</a>&nbsp<a href='{$url_d}'>下移</a><br>";
			}
			$url = site_url("crud/seq");
			echo "<a href='{$url}'>返回</a>";
		}
		
	}
}
