<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->model ( 'auth_model' );
	}

	public function install()
	{
		$this->load->model('crud_model');
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
	public function request()
	{
		$response = new stdClass();
		if (! $this->auth_model->check( false )) {
			$response->error = (object) array("message" => "请重新登录");
			goto exit1;
		}
		$this->load->model('crud_model');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		if (!$paras) {
			$response->error = (object) array("message" => "err");
			goto exit1;
		}
		$method = "request_{$paras->action}";
		if (method_exists($this->crud_model, $method)) {
			$error = null;
			if ($table != "null") {
				$dbContext = $this->crud_model->table($table);
				if ($dbContext) {
					if ($this->crud_model->prepare($dbContext)) {
						$response->data = $this->crud_model->$method($dbContext, $paras);
						if (!$response->data) {
							$error = "Error Process";
						} else if(is_string($response->data)) {
							$error=$response->data;
						}
					} else {
						$error = "Error Prepare";
					}
				} else {
					$error = "Error Table";
				}
			} else{
				$response->data = $this->crud_model->$method($paras);
			}
			
			
			if ($error) {
				$response->error = (object) array("message" => $error);
			}
		} else {
			$response->error = (object) array("message" => "Not Support Action");
		}
exit1:
		echo json_encode($response);
	}
	
}
