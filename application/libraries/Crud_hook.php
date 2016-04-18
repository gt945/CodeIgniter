<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud_hook {
	
	function user_edit($oper, &$data, $field)
	{
		if (isset($data['password'])) {
			$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
		}
		if ($oper == 'create') {
			$data['create_date'] = date('Y-m-j H:i:s');
		}
	}
	
	function group_edit($oper, &$data, $field)
	{
		
	}
	
}
