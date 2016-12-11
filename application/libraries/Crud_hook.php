<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud_hook {
	
	function user_edit($oper, &$data, $field)
	{
		if (isset($data['password'])) {
			$data['password'] = md5($data['password']);
		}
		if ($oper == 'create') {
			$data['create_date'] = date('Y-m-j H:i:s');
		}
	}
	
	function group_edit($oper, &$data, $field)
	{
		
	}
	
	
	function calc_order($oper, &$data, $field)
	{
	
	
	}
}
