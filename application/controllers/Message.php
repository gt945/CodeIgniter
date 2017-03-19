<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends MY_Controller {
	function __construct()
	{
		parent::__construct();
		$this->key = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$this->paras = json_decode($paras_json);
		$this->load->model('message_model');
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
	
	private function request_message_show()
	{
		$id = (int) $this->paras->id;
		$ret = $this->message_model->show($id);
		return $ret;
		
	}

	private function request_message_send()
	{
		$message = $this->paras->message;
		$receiver = $this->paras->receiver;
		
		$to = explode(";", $receiver);
		if (is_array($to) && count($to)) {
			foreach($to as $t) {
				if(substr($t, 0, 1) == 'u') {
					$this->message_model->send( (int) substr($t, 1), $message);
				}
			}
		}
		
		
		
		return 1;
	}
	
}