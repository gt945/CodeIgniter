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
	
	public function send($receiver, $message, $aid = null)
	{
		if (!$aid) {
			$aid = $_SESSION['userinfo']['id'];
		}
		$save = array(
			'AID' =>$aid,
			'ReceiverID' => (int) $receiver,
			'Priority' => 0,
			'IsRead' => 'N',
			'Content' => $message
			
		);
		$this->db->insert('messages', $save);
	}
	
	public function send_by_group($gid, $message, $sub = false, $aid = null)
	{
		$group_info = $this->db->get_where("user_group", array("id" => $gid))->row_array();
		if (!$group_info ) {
			return false;
		}
		if ($sub) {
			$this->db->from('user_group');
			$this->db->like("tree_code", $group_info['tree_code'], "after");
			$groups = $this->db->col('id');
		} else {
			$groups = array($group_info['id']);
		}
		if ($groups && count($groups)) {
			$this->db->from('user');
			$this->db->where_in('gid', $groups);
			$users = $this->db->col('id');
			if ($users && count($users)) {
				foreach($users as $u) {
//					$this->send($u, $message, $aid);
				}
			}
		}
	}
	
	public function send_by_group_name($group_name, $message, $sub = false, $aid = null)
	{
		$group_info = $this->db->get_where("user_group", array("groupname" => $group_name))->row_array();
		if ($group_info) {
			$this->send_by_group($group_info['id'], $message, $sub, $aid);
		}
	}
}