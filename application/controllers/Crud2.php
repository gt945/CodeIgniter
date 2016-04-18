<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud2 extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->model ( 'auth_model' );
	}

	public function install()
	{
		$this->load->model('crud_model2');
		$this->crud_model2->install('user');
		$this->crud_model2->install('user_group');
		$this->crud_model2->install('user_role');
		$this->crud_model2->install('crud_join');
		$this->crud_model2->install('crud_table2');
		$this->crud_model2->install('crud_field2');
		$this->crud_model2->install('menu');
		$this->crud_model2->install('test1');
		$this->crud_model2->install('test2');
	}
	public function request()
	{
		$response = new stdClass();
		if (! $this->auth_model->check( false )) {
			$response->error = (object) array("message" => "请重新登录");
			goto exit1;
		}
		$this->load->model('crud_model2');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		$method = "request_{$paras->action}";
		if (method_exists($this->crud_model2, $method)) {
			$error = null;
			if ($table != "null") {
				$dbContext = $this->crud_model2->table($table);
				if ($dbContext) {
					if ($this->crud_model2->prepare($dbContext)) {
						$response->data = $this->crud_model2->$method($dbContext, $paras);
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
				$response->data = $this->crud_model2->$method($paras);
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
