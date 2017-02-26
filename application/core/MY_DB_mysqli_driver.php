<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver {

	function __construct($params){
		parent::__construct($params);
		log_message('debug', 'Extended DB driver class instantiated!');
	}

	public function sheet()
	{
		return $this->get()->result_array();
	}

	public function row()
	{
		return $this->get()->row_array();
	}

	public function col($colname)
	{
		$result = array();
		$data = $this->sheet();

		if (count($data) && isset($data[0][$colname])) {
			foreach ($data as $d) {
				$result[] = $d[$colname];
			}
		}
		return $result;
	}

	public function cell($colname)
	{
		$result = null;

		$data = $this->row();

		if (count($data) && isset($data[$colname])) {
			$result = $data[$colname];
		}
		return $result;
	}

}
