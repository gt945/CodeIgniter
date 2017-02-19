 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaperUseDetail extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->id = -1;
		$this->name = 'paperusedetail';
	}
	
	public function prepare($id)
	{
		$this->data = null;
		$this->db->from($this->name);
		$this->db->where('id', $id);

		$this->data = $this->db->row();
		if ($this->data) {
			$this->id = $id;
			return $this->data;
		} else {
			return null;
		}

	}
	
	public function create($d, $j, $n)
	{
		$save = array(
			"PaperStyleID" => $d['paperDeduceID'],
			"Counts" => $d['TotalPaper'],
			"KeepCounts" => $d['TotalPaper'],
			"Price" => $d['Price'],
			"Note" => "印制单用纸",
			"Year" => date('Y'),
			"JID" => $j['id'],
			"Month" => date('m'),
			"Volume" => "",
			"No" => $n['No'],
			"Type" => 0,
		);
		$this->db->insert($this->name, $save);
		return $this->db->insert_id ();
	}
	
	public function delete(){
		if ($this->data) {
			$this->db->delete($this->name, array('id' => $this->id));
		}
	}
	
}
