<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->database ();
		$data = array(
			"line" => $this->input->post ( 'lineNumber' ),
			"message" => $this->input->post ( 'message' ),
			"url" => $this->input->post ( 'url' ),
			"time" => date('Y-m-j H:i:s')
		);
		
		$this->db->insert('log', $data);
	}
}
