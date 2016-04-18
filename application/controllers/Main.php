<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	
	function __construct()
	{
		parent::__construct ();
		$this->load->model ( 'auth_model' );
		$this->load->library( 'xui' );
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
		
		$data['title'] = "main";
		$data['baseurl'] = $base_url;
		$data['appPath'] = "{$base_url}assets/";
		$data['gridRPC'] = site_url('crud/request');
		
		if ( ! $menus = $this->cache->get('menus')) {
				
			$this->load->model('crud_model');
			$dbContext = $this->crud_model->table("menu");
			$menu_array = $this->crud_model->get_tree_data_by_pid($dbContext, 0, true);
			$menus = $this->xui->menus($menu_array[0]['children']);
			$this->cache->save('menus', $menus);
		}
		$data['menus'] = $menus;
		$data['username'] = $_SESSION['userinfo']['username'];
		$data['logout'] = site_url('user/logout');
		$this->load->view('main', $data);
	}
}
