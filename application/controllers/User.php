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
		
		$js = array (
				"assets/js/jquery.min.js" 
		);
		foreach ( $js as &$j ) {
			$j = "<script type=\"text/javascript\" src=\"{$base_url}{$j}\"></script>";
		}
		$cap = $this->auth_model->captcha();
		$data ['js'] = implode ( "\n", $js );
		$data ['captcha'] = $cap['image'];
		$data ['captcha_time'] = $cap['time'];
		$data ['captcha_url'] = site_url("user/captcha");
		$this->load->helper ( 'form' );
		$this->load->library ( 'form_validation' );
		
		$this->form_validation->set_rules ( 'username', 'Username', 'required|alpha_numeric' );
		$this->form_validation->set_rules ( 'password', 'Password', 'required' );
		
		if ($this->form_validation->run () == false) {
			$this->load->view ( 'user/login', $data );
		} else {
			$username = $this->input->post ( 'username' );
			$password = $this->input->post ( 'password' );
			
			$result = $this->auth_model->login ( $username, $password );
			if ($result == 0) {
				$url = $this->input->get ( "redirect" );
				redirect ( rawurldecode ( $url ) );
			} else {
				$error = array(
					1 => "验证码错误",
					2 => "用户名或密码错误"
				);
				$data ['error'] = $error[$result];
				$this->load->view ( 'user/login', $data );
			}
		}
	}
	
	public function captcha()
	{
		$cap = $this->auth_model->captcha();
		echo $cap['image'];
		echo "<input type=\"hidden\" name=\"captcha_time\" value=\"{$cap['time']}\">";
	}
	/**
	 * logout function.
	 * 
	 * @access public
	 * @return void
	 */
	public function logout() {
		$this->auth_model->login_out();
		redirect('/');
	}
	
}
