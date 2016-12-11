 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JournalStockManage extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->jid = -1;
		$this->year = -1;
		$this->no = -1;
		
	}
	
	public function prepare($jid, $year, $no)
	{
		$this->stock = null;
		$this->db2->from('journalstockmanage');
		$this->db2->where('JID', $jid);
		$this->db2->where('Year', $year);
		$this->db2->where('No', $no);
		$this->stock = $this->db2->row();
		if ($this->stock) {
			$this->jid = $jid;
			$this->year = $year;
			$this->no = $no;
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
	
	public function stock_out($count, $tag, $cid = null, $note = null)
	{
		//TODO lock stock
		if ($this->stock) {
			$save = array(
				'JID'	=>	$this->jid,
				'CID'	=>	$cid,
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'StockTag'	=>	$tag
			);
			$this->db2->insert('stockmanagedetails', $save);
			$this->stock['Counts'] -= $count;
			$save = array(
				'Counts' => $this->stock['Counts'];
			);
			$this->db2->where('id', $this->stock['id']);
			$this->db2->update('journalstockmanage', $save);
		}
		
	}
	
	public function stock_in($count, $tag)
	{
		//TODO lock stock
		if ($this->stock) {
			$save = array(
				'JID'	=>	$this->jid,
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'StockTag'	=>	$tag
			);
			$this->db2->insert('stockmanagedetails', $save);
			$this->stock['Counts'] += $count;
			$save = array(
				'Counts' => $this->stock['Counts'];
			);
			$this->db2->where('id', $this->stock['id']);
			$this->db2->update('journalstockmanage', $save);
		}
		
	}
	
	
	
}
