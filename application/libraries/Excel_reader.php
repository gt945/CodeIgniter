<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');  
 
require_once APPPATH."/third_party/PHPExcel/Classes/PHPExcel.php";
 
class Excel_reader extends PHPExcel_Reader_Excel5 {
    public function __construct() {
        parent::__construct();
    }
}
