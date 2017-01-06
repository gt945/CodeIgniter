 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaperStock extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->id = -1;
        $this->name = 'paperstock';
	}
	
	public function prepare($id)
	{
		$this->stock = null;
		$this->db->from($this->name);
		$this->db->where('id', $id);

		$this->stock = $this->db->row();
		if ($this->stock) {
			$this->id = $id;
			return $this->stock;
		} else {
			return null;
		}

	}
	
	public function stock_count()
	{
		if ($this->stock) {
			return $this->stock['Counts'];
		} else {
			return null;
		}
	}
	
	public function stock_out($count)
	{
        //TODO lock stock
        if ($this->stock) {
            $save = array(
                'Counts'	=>	(double)$this->stock['Counts'] - (double)$count
            );
            $this->db->where('id', $this->id);
            $this->db->update($this->name, $save);
        }
	}
	
	public function stock_in($count)
	{
		//TODO lock stock
		if ($this->stock) {
			$save = array(
				'Counts'	=>	(double)$this->stock['Counts'] + (double)$count,
                'CreateTime'    => date('Y-m-j H:i:s')
			);
			$this->db->where('id', $this->id);
			$this->db->update($this->name, $save);
		}
	}
	
	
	
}
