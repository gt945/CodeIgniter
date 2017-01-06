 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PublishNotify extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->id = -1;
        $this->name = 'publishnotify';
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
	
}
