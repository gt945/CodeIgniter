<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xui extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->model ( 'auth_model' );
	}

	public function request()
	{
		$response = (object)array(
				'code' => 200,
				'msg' => ''
		);
		
		if (! $this->auth_model->check( false )) {
			$response->code = 401;
			$response->msg = "请重新登录";
			goto exit1;
		}
		$this->load->model('crud_model');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		if (!$paras) {
			$response->code = 400;
			$response->msg = "格式错误";
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
							$error = "操作失败";
						} else if(is_string($response->data)) {
							$error=$response->data;
						}
					} else {
						$error = "内部错误";
					}
				} else {
					$error = "数据表错误";
				}
			} else{
				$response->data = $this->crud_model->$method($paras);
			}
			
			
			if ($error) {
				$response->code = 500;
				$response->msg = $error;
			}
		} else {
			$response->code = 501;
			$response->msg = "不支持的操作";
		}
exit1:
		echo json_encode($response);
	}
	
}
