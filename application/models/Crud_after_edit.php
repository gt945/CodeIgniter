<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH.'models/Crud_hook.php');

class Crud_after_edit extends Crud_hook {


	public function __construct()
	{
		parent::__construct ();
	}

	public function publishnotify_edit($oper, $model, $save, $ids)
	{
		if ($oper == "create") {
			$d = $save[0];
				
			$sql = <<<EOF
select '编辑部' as DeliverTarget, orderCount DeliverCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$d['JID']} and saleStyle = 5 and jyear = {$d['Year']} and nostart <= {$d['No']} and noend >= {$d['No']}) a
union
select '邮局外埠' as DeliverTarget, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$d['JID']} and saleStyle = 4 and jyear ={$d['Year']}  and nostart <= {$d['No']} and noend >={$d['No']}) b
union
select '邮局本市' as DeliverTarget, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount  from `qkzx_journalorders`  where JID = {$d['JID']} and saleStyle = 3 and jyear ={$d['Year']} and nostart <= {$d['No']} and noend >= {$d['No']}) c
union
SELECT '邮局本市东' AS DeliverTarget, orderCount DeliverCount FROM (SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$d['JID']} AND saleStyle =20 AND Jyear ={$d['Year']}  AND nostart = {$d['No']}) e
union
SELECT '邮局本市西' AS DeliverTarget, orderCount DeliverCount FROM(SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$d['JID']} AND saleStyle = 21 AND Jyear ={$d['Year']} AND nostart ={$d['No']} ) f
union
select '本社' as DeliverTarget, orderCount DeliverCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$d['JID']} and saleStyle in(1,2,6,8) and jyear = {$d['Year']} and nostart <= {$d['No']} and noend >= {$d['No']}) d

EOF;
			$ret = $this->db->query($sql);
			$rows = $ret->result_array();
			$total = 0;
			foreach($rows as $r) {
				$total += $r['DeliverCount'];
				$r['PNID'] = $ids[0];
				$r['CreateTime'] = date('Y-m-d H:i:s');
				$ret = $this->db->insert('publishnotifydeliver', $r);
			}

			$journal = $this->db->get_where('journalbaseinfo', array('id' => $d['JID']))->row_array();
			$publishrecords = $this->db->get_where('publishrecords', array('JID' => $d['JID'], 'No' => $d['No']))->row_array();
			
			$paper_map = array(
				array(
					"id"	=> 'CoverPaper',
					"name"	=> '封面',
					"count"	=> 4,
					"pages"	=> '1-4'
				),
				array(
					"id"	=> 'ContentPaper',
					"name"	=> '正文',
					"count"	=> $publishrecords['TotalPageCount'],
					"pages"	=> "1-{$publishrecords['TotalPageCount']}"
				),
				array(
					"id"	=> 'PicturePaper',
					"name"	=> '彩版',
					"count"	=> $publishrecords['PicPageCount'],
					"pages"	=> "1-{$publishrecords['PicPageCount']}"
				)
			);
			
			foreach($paper_map as $map) {
				$save = array();
				$paper = $this->db->get_where('paperstock', array('id' => $journal[$map['id']]))->row_array();
				if ($paper) {
					$save['PNID'] = $ids[0];
					$save['PublishContent'] = $map['name'];
					$save['Pages'] = $map['pages'];
					$save['PublishCount'] = $d['PublishCounts'];
					$save['paperDeduceID'] = $paper['id'];
//					$save['SizeId'] = $paper['size'];
					$save['Size'] = $paper['size'];
					$save['KaiShu'] = $journal['FormatId'];
					$save['colourCount'] = 2;
					$paperCount = $d['PublishCounts'] * $map['count'] / $save['KaiShu'];
					$paperCount = round($paperCount * 2 + 0.4999, 0) / 2 / 1000;
					$save['PaperCount'] = $paperCount;
					$save['ZoomPercent'] = 60;
					$save['ZoomPaperCount'] = $save['colourCount'] * $save['ZoomPercent'] / 1000;
					$save['TotalPaper'] = $save['PaperCount'] + $save['ZoomPaperCount'];
					$save['CreateTime'] = date('Y-m-d H:i:s');
					$ret = $this->db->insert('publishnotifydetails', $save);
				}
			}
		}
		return $this->result();
	}
}
