<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Auth_model class.
 *
 * @extends CI_Model
 */
class Auth_model extends CI_Model {
	
	const EXPIRATION = 300; 
	
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct ();
		$this->load->helper(array('url'));
		$this->load->library(array('session'));
		$this->load->database ();
	}
	
	/**
	 * create_user function
	 * @param mixed $username
	 * @param mixed $password
	 * @param mixed $contact
	 * @param mixed $remark
	 */
	public function create_user($username, $password, $contact, $remark)
	{
	
		$data = array(
				'username'   => $username,
				'password'   => hash_password($password),
				'contact'    => $contact,
				'remark'	 => $remark,
				'rid'		 => 0,
				'gid'		 => 0,
				'state'		 => 0,
				'create_date' => date('Y-m-j H:i:s'),
		);
	
		return $this->db->insert('user', $data);
	
	}
	
	/**
	 * login function.
	 *
	 * @access public
	 * @param mixed $username
	 * @param mixed $password
	 * @return bool true on success, false on failure
	 */
	public function login($username, $password)
	{
		$captcha = $this->input->post('captcha');
		$captcha_time = $this->input->post('captcha_time');
		if (!$this->captcha_verify($captcha, $captcha_time)) {
			return 1;
		}
		$this->db->from ( 'user' );
		$this->db->where ( 'username', $username );
		$userinfo = $this->db->get ()->row_array();
		if (! $userinfo) {
			return 2;
		}
		$hash = $userinfo ['password'];
		unset($userinfo ['password']);
		if ($this->verify_password_hash ( $password, $hash )) {
			
			$this->session->set_userdata ( array( "userinfo" => $userinfo) );
			$this->db->from ( 'user_group' );
			$this->db->where ( 'id', $userinfo['gid'] );
			$groupinfo = $this->db->get ()->row_array();
			$this->session->set_userdata ( array( "groupinfo" => $groupinfo) );
			return 0;
		} else {
			return 2;
		}
	}
	
	/**
	 * check login
	 * @param unknown $url
	 */
	public function check($rd = false, $url = "/")
	{
		if (!isset( $_SESSION['userinfo'])) {
			if ($rd) {
				redirect('user/login?redirect='.rawurlencode($url));
				die();
			}
			return false;
		}  else {
			return true;
		}
	}
	
	/**
	 * log out
	 */
	public function login_out()
	{
		$this->session->sess_destroy();
	}
	
	/**
	 * get_user_id_from_username function.
	 *
	 * @access public
	 * @param mixed $username
	 * @return int the user id
	 */
	public function get_user_id_from_username($username)
	{
	
		$this->db->select('id');
		$this->db->from('user');
		$this->db->where('username', $username);
	
		return $this->db->get()->row('id');
	
	}
	
	/**
	 * get_user function.
	 *
	 * @access public
	 * @param mixed $user_id
	 * @return object the user object
	 */
	public function get_user($user_id)
	{
	
		$this->db->from('user');
		$this->db->where('id', $user_id);
		return $this->db->get()->row();
	
	}
	
	public function captcha()
	{
		$this->load->helper('captcha');
		$base_url = base_url ();
		$vals = array(
				'img_path'      => BASEPATH.'../'.'captcha/',
				'img_url'       => "{$base_url}/captcha/",
				'font_path'     => BASEPATH.'../'.'assets/font/vera.ttf',
				'word_length'   => 4,
				'font_size'     => 20,
				'img_width'     => '130',
				'img_height'     => '40',
				'expiration'    => Auth_model::EXPIRATION,
		);
		
		$cap = create_captcha($vals);
		$cap['time'] = (int)$cap['time'];
		$data = array("cap_{$cap['time']}" => $cap['word']);
		$this->session->set_userdata ( $data );
		
		return $cap;
	}
	
	public function captcha_verify($word, $captcha_time)
	{
		$expiration = time() - Auth_model::EXPIRATION;
		foreach ($_SESSION as $k=>$s) {
			if (substr( $k, 0, 4 ) === "cap_") {
				$captcha = explode ( "_", $k );
				if ($captcha[1] < $expiration) {
					unset($_SESSION[$k]);
				}
			}
		}
		if ($captcha_time < $expiration) {
			return false;
		}
		if (!isset($_SESSION["cap_{$captcha_time}"])) {
			return false;
		}
		if (strtolower($_SESSION["cap_{$captcha_time}"]) !== strtolower($word)) {
			return false;
		}
		unset($_SESSION["cap{$captcha_time}"]);
		return true;
	}
	/**
	 * verify_password_hash function.
	 *
	 * @access private
	 * @param mixed $password
	 * @param mixed $hash
	 * @return bool
	 */
	private function verify_password_hash($password, $hash)
	{
	
		return password_verify($password, $hash);
	
	}
	
}