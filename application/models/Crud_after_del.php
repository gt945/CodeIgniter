<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH.'models/Crud_hook.php');

class Crud_after_del extends Crud_hook {


    public function __construct()
	{
		parent::__construct ();
	}
}
