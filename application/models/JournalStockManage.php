 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JournalStockManage extends CI_Model {

	public function __construct()
	{
		parent::__construct ();
		$this->jid = -1;
		$this->year = -1;
		$this->no = -1;
		$this->name = 'journalstockmanage';
		
	}
	
	public function prepare($jid, $year, $no)
	{
		$this->jid = $jid;
		$this->year = $year;
		$this->no = $no;
		$this->stock = null;
		$this->db->from($this->name);
		$this->db->where('JID', $jid);
		$this->db->where('Year', $year);
		$this->db->where('No', $no);
		$this->stock = $this->db->row();
		if ($this->stock) {
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
				"AID"	=>	$_SESSION['userinfo']['id'],
				'CID'	=>	$cid,
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'StockTag'	=>	$tag,
				'Note'  => $note
			);
			$this->db->insert('stockmanagedetails', $save);
			$this->stock['Counts'] -= $count;
			$save = array(
				'Counts' => $this->stock['Counts']
			);
			$this->db->where('id', $this->stock['id']);
			$this->db->update($this->name, $save);
		}
		
	}
	
	public function stock_in($count, $tag, $note = null)
	{
		//TODO lock stock
		if ($this->stock) {
			$save = array(
				'JID'	=>	$this->jid,
				"AID"	=>	$_SESSION['userinfo']['id'],
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'StockTag'	=>	$tag,
				'Note'  => $note
			);
			$this->db->insert('stockmanagedetails', $save);
			$this->stock['Counts'] += $count;
			$save = array(
				'Counts' => $this->stock['Counts']
			);
			$this->db->where('id', $this->stock['id']);
			$this->db->update($this->name, $save);
		} else if ($this->jid != -1) {
			$save = array(
				'JID'	=>	$this->jid,
				"AID"	=>	$_SESSION['userinfo']['id'],
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'StockTag'	=>	$tag,
				'Note'  => $note
			);
			$this->db->insert('stockmanagedetails', $save);
			$save = array(
				'JID'	=>	$this->jid,
				'Year'	=>	$this->year,
				'No'	=>	$this->no,
				'Counts'	=>	$count,
				'Note'  => $note
			);
			$this->db->insert($this->name, $save);
		}
		
	}
	
	
	
}
