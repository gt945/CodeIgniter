<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud_hook extends CI_Model {


    public function __construct()
	{
		parent::__construct ();
	}

	function result($result = true, $message = "", $data = null)
    {
        $ret = new stdClass();
        $ret->result = $result;
        $ret->message = $message;
        $ret->data = $data;
        return $ret;
    }

}
