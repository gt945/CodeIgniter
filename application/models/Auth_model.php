<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Auth_model class.
 *
 * @extends CI_Model
 */
class Auth_model extends CI_Model {
	
	const EXPIRATION = 300; 
	const CERT_PRIV = "cert_priv";
	const CERT_PUB = "cert_pub";
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
		$this->load->driver('cache', array('adapter' => 'file'));
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
	public function login($username, $password, $captcha, $captcha_time)
	{
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
		if ($this->verify_password_hash ( $password, $hash, $captcha)) {
			
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
				'img_url'       => "{$base_url}captcha/",
				'font_path'     => BASEPATH.'../'.'assets/font/vera.ttf',
				'word_length'   => 4,
				'font_size'     => 20,
				'img_width'     => '120',
				'img_height'     => '50',
				'expiration'    => Auth_model::EXPIRATION,
		);
		
		$cap = create_captcha($vals);
		$cap['time'] = (int)$cap['time'];
		$data = array(
				"cap_{$cap['time']}" => array(
						"word" => $cap['word'],
						"ip" => $this->input->ip_address()
				)
		);
		$this->session->set_userdata ( $data );
		return (object) array(
				"error" => null,
				"url" => "{$base_url}captcha/{$cap['filename']}",
				"time" => $cap['time'],
				"pubkey" => $this->get_pubkey()
		);
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
		if (strtolower($_SESSION["cap_{$captcha_time}"]["word"]) !== strtolower($word)) {
			return false;
		}
		if ($_SESSION["cap_{$captcha_time}"]["ip"] != $this->input->ip_address()) {
			return false;
		}
		unset($_SESSION["cap_{$captcha_time}"]);
		return true;
	}
	
	public function userinfo()
	{
		$response = (object) array(
				"error" => null
		);
		if (!$this->check()){
			$response->error="未登录";
		} else {
			$this->db->select('contact,name');
			$this->db->from('user');
			$this->db->where('id', $_SESSION['userinfo']['id']);
			$userinfo = $this->db->get ()->row_array();
			$response->contact = $userinfo['contact'];
			$response->name = $userinfo['name'];
			$response->pubkey = $this->get_pubkey();
		}
		return $response;
	}
	
	public function updateinfo($password, $newpassword, $name, $contact)
	{
		$response = (object) array(
				"error" => null
		);
		if (!$this->check()){
			$response->error="未登录";
		} else {
			$decrypted = $this->decrypt_data($password);
			
			$this->db->select('password');
			$this->db->from('user');
			$this->db->where('id', $_SESSION['userinfo']['id']);
			$userinfo = $this->db->get ()->row_array();
			if ($userinfo['password'] != $decrypted) {
				$response->error="密码错误";
			} else {
				if(strlen($newpassword)){
					$decrypted = $this->decrypt_data($newpassword);
					$this->db->set("password", $decrypted);
				}
				$this->db->set("name", $name);
				$this->db->set("contact", $contact);
				$this->db->where( 'id', $_SESSION['userinfo']['id']);
				$this->db->update ( "user");
				$response->ok = true;
				$response->error = "修改成功";
			}
		}
		return $response;
	}
	
	private function decrypt_data($encrypted)
	{
		$pubKey = $this->get_pubkey();
		$privKey = $this->cache->get(Auth_model::CERT_PRIV);
		if (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $privKey)) {
			return $decrypted;
		}
		return null;
	}
	/**
	 * verify_password_hash function.
	 *
	 * @access private
	 * @param string $password
	 * @param string $hash
	 * @param string $captcha
	 * @return bool
	 */
	private function verify_password_hash($password, $hash, $captcha)
	{
	
		$decrypted = $this->decrypt_data($password);
		if ($decrypted) {
			$passHash = hash_hmac('md5', $hash, $captcha);
			return ($decrypted === $passHash);
		}
		return false;
	
	}
	
	/**
	 * gen_certificate
	 * 
	 * generate rsa key pair and save to cache
	 * 
	 */
	private function gen_certificate()
	{
		$config = array(
				"digest_alg" => "sha512",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
			
		// Create the private and public key
		$res = openssl_pkey_new($config);
		
		// Extract the private key from $res to $privKey
		openssl_pkey_export($res, $privKey);
		
		// Extract the public key from $res to $pubKey
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];
		
		$this->cache->save(Auth_model::CERT_PRIV, $privKey, 3600);
		$this->cache->save(Auth_model::CERT_PUB, $pubKey, 3600);
		return $pubKey;
	}
	
	/**
	 * get current public key, it not exist, generate one
	 * 
	 */
	private function get_pubkey()
	{
		$pubKey = $this->cache->get(Auth_model::CERT_PUB);
		if (!$pubKey) {
			$pubKey = $this->gen_certificate();
		}
		return $pubKey;
	}
	
}