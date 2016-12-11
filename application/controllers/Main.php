<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	
	function __construct()
	{
		parent::__construct ();
		$this->load->model ( 'auth_model' );
		$this->load->library( 'xui_utils' );
		if (! $this->auth_model->check( true, uri_string() )) {
			die ();
		}
		$this->load->driver('cache', array('adapter' => 'file', 'key_prefix' => "u{$_SESSION['userinfo']['id']}_"));
	}
	
	public function index()
	{
		$this->load->helper("url");
		$base_url = base_url();
		$js = array (
				"assets/App/log.js",
				"assets/js/md5.min.js",
				"assets/js/jsencrypt.min.js",
				"assets/js/xui-debug.js",
				"assets/App/ajax.js",
				"assets/App/main.js",
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
		$data['css'] = implode ( "\n", $css );
		
		$data['title'] = $this->config->item('sys_title');
		$data['siteurl'] = site_url("/");
		$data['appPath'] = "{$base_url}assets/";
		$data['xuiRPC'] = site_url('xui/request');
		if ( ! $menus = $this->cache->get('menus')) {

			$this->load->model('crud_model');
			$this->crud_model->table("menu");
			$menu_array = $this->crud_model->get_tree_data_by_pid(0, true);
			$menus = $this->xui_utils->menus($menu_array[0]['children'], $this->auth_model->role());
			$this->cache->save('menus', $menus);
		}
		$data['menus'] = $menus;
		$data['username'] = $_SESSION['userinfo']['username'];
		$this->load->view('main', $data);
	}
	
// 	public function install()
// 	{
// 		$this->load->model('crud_model');
// 		$this->crud_model->install('user');
// 		$this->crud_model->install('user_group');
// 		$this->crud_model->install('user_role');
// 		$this->crud_model->install('crud_join');
// 		$this->crud_model->install('crud_table');
// 		$this->crud_model->install('crud_field');
// 		$this->crud_model->install('menu');
// 		$this->crud_model->install('test1');
// 		$this->crud_model->install('test2');
// 	}
}
