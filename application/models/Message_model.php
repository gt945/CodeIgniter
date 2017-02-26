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

	public function show($id)
	{
		$ret = 1;
		$msg = $this->db->get_where('messages', array('id' => $id, 'ReceiverID' => $_SESSION['userinfo']['id']))->result_array();
		if (count($msg)) {
			$this->db->where('id', $id);
			$this->db->update('messages', array('IsRead' => 'Y'));
			$ret = 0;
		}
		return $ret;
		
	}
	public function send($receiver, $message)
	{
		$to = explode(";", $receiver);
		foreach($to as $t) {
			if(substr($t, 0, 1) == 'u') {
				$save = array(
					'AID' => $_SESSION['userinfo']['id'],
					'ReceiverID' => (int) substr($t, 1),
					'Priority' => 0,
					'IsRead' => 'N',
					'Content' => $message
					
				);
				$this->db->insert('messages', $save);
			}
		}
	}
}