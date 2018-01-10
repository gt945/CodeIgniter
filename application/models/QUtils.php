<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QUtils extends CI_Model {
	
	public function get_deliver_target($JID, $Year, $No)
	{
		$sql = <<<EOF
select '编辑部' as orderUnit, orderCount DeliverCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle = 5 and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) a
union
select '邮局外埠' as orderUnit, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle = 4 and jyear ={$Year}  and nostart <= {$No} and noend >={$No}) b
union
select '邮局本市' as orderUnit, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount  from `qkzx_journalorders`  where JID = {$JID} and saleStyle = 3 and jyear ={$Year} and nostart <= {$No} and noend >= {$No}) c
union
SELECT '邮局本市东' AS orderUnit, orderCount DeliverCount FROM (SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} AND saleStyle =20 AND Jyear ={$Year}  AND nostart = {$No}) e
union
SELECT '邮局本市西' AS orderUnit, orderCount DeliverCount FROM(SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} AND saleStyle = 21 AND Jyear ={$Year} AND nostart ={$No} ) f
union
select '本社' as orderUnit, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle in(1,2,6,7,8) and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) d

EOF;
		$ret = $this->db->query($sql);
		return $ret->result();
	}
	
	public function calc_paper_details(&$data)
	{
		
		$page = 1;
		$pages = explode('-', $data['Pages']);
		if (count($pages) == 2) {
			$page = (int)$pages[1];
		} else if (count($pages) == 1){
			$page = (int)$pages[0];
		}
		if ($page < 1) {
			$page = 1;
		}
		
		
		$kai = (int)$data['KaiShu'];
		$count = (int)$data['PublishCount'];
		$color = (int)$data['colourCount'];
		$zoom = (int)$data['ZoomPercent'];
		if ($kai < 1) {
			$data['KaiShu'] = 1;
			$kai = 1;
		}
		if ($data['PublishContent'] == '封面'){
			$page = 4;
			$paper = round($page * $count / $kai * 2 + 0.4999, 0) / 4;
		} else {
			$paper = round($page * $count / $kai * 2 + 0.4999, 0) / 2;
		}
		
		if ($data['PublishContent'] == '彩版' || $data['PublishContent'] == '封面') {
			$zoompaper = round($color * $zoom * 2 + 0.4999, 0) / 2;
		} else {
			$zoompaper = round($page * $zoom / $kai * 2 + 0.4999, 0) / 2;
		}
				
		$totalpaper = $paper + $zoompaper;
		$data['TotalPaper'] = number_format($totalpaper / 1000, 4);
		$data['PaperCount'] = number_format($paper / 1000, 4);
		$data['ZoomPaperCount'] = number_format($zoompaper / 1000, 4);
		$data['Pages'] = "1-{$page}";
	}
	
}

