<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud_hook extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->message = null;
	}

	function result($result = true, $message = null, $data = null)
	{
		$ret = new stdClass();
		$this->append($message);
		$ret->result = $result;
		$ret->message = $this->message;
		$ret->data = $data;
		return $ret;
	}
	
	function append($message)
	{
		if ($this->message == null) {
			$this->message = $message;
		} else if ($message){
			$this->message = $this->message . "<br>" . $message;
		}
	}
}
