<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');  
 
require_once APPPATH."/third_party/pinyin/src/DictLoaderInterface.php";
require_once APPPATH."/third_party/pinyin/src/FileDictLoader.php";
require_once APPPATH."/third_party/pinyin/src/Pinyin.php";
use Overtrue\Pinyin\Pinyin;
class Py extends Pinyin {
	public function __construct() {
		parent::__construct();
	}
}
