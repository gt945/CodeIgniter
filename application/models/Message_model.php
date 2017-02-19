<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Message_model class.
 *
 * @extends CI_Model
 */
class Message_model extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->load->database ();
	}

	public function send_message($to, $group = false, $sub = false)
	{

	}
}