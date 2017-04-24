<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: tao
 * Date: 16-11-27
 * Time: 下午9:14
 */
class MY_Controller extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model ( 'auth_model' );
		if (! $this->auth_model->check( false )) {
			$this->reply(401, "请重新登录");
		}
		$this->warn = null;
		
	}
	private function append($var, $msg)
	{
		if (isset($this->$var)) {
			if ($this->$var == null) {
				$this->$var = $msg;
			} else if ($msg){
				$this->$var = $this->$var . "<br>" . $msg;
			}
		}
	}

	public function reply($code = 200, $msg = "Success", $data = null, $warn = null)
	{
		$this->warn($warn);
		if ($this->input->is_ajax_request()) {
			$response = new stdClass();
			$response->code = $code;
			$response->msg = $msg;
			if ($data) {
				$response->data = $data;
			}
			if ($this->warn) {
				$response->warn = $warn;
			}
			echo json_encode($response);
		} else {
			echo $msg;
		}
		die();
	}
	
	public function warn($msg) {
		$this->append('warn', $msg);
	}
}