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
	
	public function index3()
	{
		$this->load->helper("url");
		$base_url = base_url();
		$js = array (
				"assets/js/jquery.min.js",
				"assets/js/jquery-ui.min.js",
				"assets/js/jquery-ui-timepicker-addon.js",
				"assets/js/jquery.layout-latest.js",
				"assets/js/jquery.jqGrid.js",
				"assets/js/i18n/grid.locale-cn.js",
				"assets/js/select2.full.js",
				"assets/js/main.js",
		);
		$css = array (
				"assets/css/jquery-ui.min.css",
				"assets/css/jquery-ui-timepicker-addon.css",
				"assets/css/layout-default-latest.css",
				"assets/css/ui.jqgrid.css",
				"assets/css/select2.min.css",
				"assets/css/main.css",
		);
		foreach ( $js as &$j ) {
			$j = "<script type=\"text/javascript\" src=\"{$base_url}{$j}\"></script>";
		}
		$data ['js'] = implode ( "\n", $js );
		
		foreach ( $css as &$c ) {
			$c = "<link rel=\"stylesheet\" href=\"{$base_url}{$c}\" type=\"text/css\" />";
		}
		$data ['css'] = implode ( "\n", $css );
		$data['title'] = "main";
		$data['baseurl'] = $base_url;
		
		if ( ! $menus = $this->cache->get('menus')) {
			
			$this->load->model('crud_model');
			$dbContext = $this->crud_model->table("menu");
			$menus = $this->crud_model->get_tree_data_by_pid($dbContext, 0, true);
			$this->cache->save('menus', $menus);
		}
		$data['menus'] = $menus[0]['children'];
		$data['username'] = $_SESSION['userinfo']['username'];
		$data['logout'] = site_url('user/logout');
		$this->load->view('main', $data);
	}

	public function crud($name)
	{
		$this->load->model('crud_model');
		$dbContext = $this->crud_model->table($name);
		$data = $this->crud_model->jqgrid($dbContext);
		if ($data) {
			$this->load->view('crud_jqgrid', $this->crud_model->jqgrid($dbContext));
		} else {
			echo 'No such page';
		}
	}
	public function index()
	{
		$this->load->helper("url");
		$base_url = base_url();
		$js = array (
				"assets2/js/xui-debug.js",
				"assets2/App/ajax.js",
				"assets2/App/main.js",
		);
		$css = array (
				"assets2/appearance/vista/theme.css",
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
		$data['appPath'] = "{$base_url}assets2/";
		$data['gridRPC'] = site_url('crud2/request');
		
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
		$this->load->view('main2', $data);
	}
}
