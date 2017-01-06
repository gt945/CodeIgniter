<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User class.
 * 
 * @extends CI_Controller
 */
class User extends CI_Controller {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->model('auth_model');
	}
	
	public function index()
	{
		redirect('user/login');
	}
	
	/**
	 * register function.
	 * 
	 * @access public
	 * @return void
	 */
	public function register()
	{
		if ($this->auth_model->check() ) {
			redirect('/');
		}
		// create the data object
		$data = new stdClass();
		
		// load form helper and validation library
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		// set validation rules
		$this->form_validation->set_rules('username', 'Username', 'trim|required|alpha_numeric|min_length[4]|is_unique[users.username]', array('is_unique' => 'This username already exists. Please choose another one.'));
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|required|min_length[6]|matches[password]');
		
		if ($this->form_validation->run() === false) {
			
			// validation not ok, send validation errors to the view
			$this->load->view('header');
			$this->load->view('user/register/register', $data);
			$this->load->view('footer');
			
		} else {
			
			// set variables from the form
			$username = $this->input->post('username');
			$email    = $this->input->post('email');
			$password = $this->input->post('password');
			
			if ($this->auth_model->create_user($username, $email, $password)) {
				
				// user creation ok
				$this->load->view('header');
				$this->load->view('user/register/register_success', $data);
				$this->load->view('footer');
				
			} else {
				
				// user creation failed, this should never happen
				$data->error = 'There was a problem creating your new account. Please try again.';
				
				// send error to the view
				$this->load->view('header');
				$this->load->view('user/register/register', $data);
				$this->load->view('footer');
				
			}
			
		}
		
	}
		
	/**
	 * login function.
	 * 
	 * @access public
	 * @return void
	 */
	public function login()
	{
		if ($this->auth_model->check() ) {
			redirect('/');
		}
		$this->load->database();
		$base_url = base_url ();
		
		$data = array ();
		$data ['error'] = "";
		$js = array (
				"assets/js/log.js",
				"assets/js/md5.min.js",
				"assets/js/jsencrypt.min.js",
				"assets/js/xui-debug.js",
				"assets/App/login.js",
		);
		$css = array (
				"assets/appearance/vista/theme.css",
		);
		foreach ( $js as &$j ) {
			$j = "<script type=\"text/javascript\" src=\"{$base_url}{$j}\"></script>";
		}
		$data['js'] = implode ( "\n", $js );
		
		foreach ( $css as &$c ) {
			$c = "<link rel=\"stylesheet\" href=\"{$base_url}{$c}\" type=\"text/css\" />";
		}
		$data['title'] = $this->config->item('sys_title');
		$data['siteurl'] = site_url("/");
		$data['css'] = implode ( "\n", $css );
		$data['appPath'] = "{$base_url}assets/";
		$this->load->helper ( 'form' );
		$this->load->library ( 'form_validation' );
		
		$this->form_validation->set_rules ( 'username', 'Username', 'required|alpha_numeric' );
		$this->form_validation->set_rules ( 'password', 'Password', 'required' );
		
		if ($this->form_validation->run () == false) {
			$this->load->view ( 'user/login', $data );
		} else {
			$ret = (object)array(
					"error" => "",
					"ok" => 0,
					"redirct" => ""
			);
			$username = $this->input->post ( 'username' );
			$password = $this->input->post ( 'password' );
			$captcha = $this->input->post('captcha');
			$captcha_time = $this->input->post('captcha_time');
			
			$result = $this->auth_model->login ( $username, $password, $captcha, $captcha_time);
			if ($result == 0) {
				$url = $this->input->get ( "redirect" );
				$ret->ok = 1;
				$ret->url = site_url(rawurldecode ($url));
			} else {
				$error = array(
					1 => "验证码错误",
					2 => "用户名或密码错误"
				);
				$ret->error = $error[$result];
			}
			echo json_encode($ret);
		}
	}
	
	public function captcha()
	{
		$cap = $this->auth_model->captcha();
		echo json_encode($cap);
	}
	/**
	 * logout function.
	 * 
	 * @access public
	 * @return void
	 */
	public function logout()
	{
		$this->auth_model->login_out();
		redirect('/');
	}
	
	public function pubkey()
	{
		$response = new stdClass();
		$response->data = $this->auth_model->get_pubkey();
		echo json_encode($response);
	}
	
	public function userinfo()
	{
// 		$name = $this->input->post ( 'name' );
// 		$contact = $this->input->post ( 'contact' );
		$response = $this->auth_model->userinfo();
		echo json_encode($response);
	}
	
	
	public function updateinfo()
	{
		$password = $this->input->post ( 'password' );
		$newpassword = $this->input->post ( 'newpassword' );
		$name = $this->input->post ( 'name' );
		$contact = $this->input->post ( 'contact' );
		$response = $this->auth_model->updateinfo ($password, $newpassword, $name, $contact);
		echo json_encode ( $response );
	}
}
