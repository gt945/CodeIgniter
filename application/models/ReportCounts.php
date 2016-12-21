<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportCounts extends CI_Model {
	public function __construct()
	{
		parent::__construct ();
		$this->jid = -1;
		$this->year = -1;
		$this->no = -1;
			
	}
	
	public function prepare($jid, $year, $no)
	{
		$this->report = null;
		$this->db2->from('reportcounts');
		$this->db2->where('JID', $jid);
		$this->db2->where('Year', $year);
		$this->db2->where('No', $no);
		$this->report = $this->db2->row();
		if ($this->report) {
			$this->jid = $jid;
			$this->year = $year;
			$this->no = $no;
			return $this->report;
		} else {
			return null;
		}

	}
	
	public function report_count()
	{
		if ($this->report) {
			return $this->report['Count'];
		} else {
			return null;
		}
	}
	
// 	public function report_out($count)
// 	{
// 		//TODO lock report
// 		if ($this->report) {
// 			$this->report['Count'] -= $count;
// 			$save = array(
// 				'Count' => $this->report['Count'];
// 			);
// 			$this->db2->where('id', $this->report['id']);
// 			$this->db2->update('reportcounts', $save);
// 		}
// 			
// 	}
	
	public function report_in($count)
	{
		//TODO lock report
		if ($this->report) {
			$this->report['Count'] += $count;
			$save = array(
				'Count' => $this->report['Count']
			);
			$this->db2->where('id', $this->report['id']);
			$this->db2->update('reportcounts', $save);
		}
			
	}
}
