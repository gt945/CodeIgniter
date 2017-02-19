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
			foreach($rows as $r) {
				$r['PNID'] = $ids[0];
				$r['CreateTime'] = date('Y-m-d h:i:s');
				$ret = $this->db->insert('publishnotifydeliver', $r);
			}


		}
		return $this->result();
	}
}
