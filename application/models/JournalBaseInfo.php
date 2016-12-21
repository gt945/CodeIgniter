 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JournalBaseInfo extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->id = -1;

	}
	
	public function prepare($id)
	{
		$this->data = null;
		$this->db2->from('journalbaseinfo');
		$this->db2->where('id', $id);
		$this->data = $this->db2->row();
		if ($this->data) {
			$this->id = $id;
			return $this->data;
		} else {
			return null;
		}

	}
	
}
