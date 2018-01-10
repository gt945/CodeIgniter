<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QKZX extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->key = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$this->paras = json_decode($paras_json);
	}

	public function index()
	{
		$this->reply(403, "Forbid");
	}

	public function request()
	{
		$method = "request_{$this->paras->action}";
		if (method_exists($this, $method)) {
			$data = $this->$method();
			$this->reply(200, "Success", $data);
		} else {
			$this->reply(404, "Not Found");
		}
	}

	private function request_generate_new_plan()
	{
//		$this->load->model('crud_model');
//		$dbContext = $this->crud_model->table('journalbaseinfo');
//		$this->crud_model->prepare($dbContext, false);
//		$this->crud_model->parm($dbContext, 'where', 'year', '2014');
//		$data = $this->crud_model->sheet($dbContext);
//
//		foreach($data as &$d) {
//			unset($d['id']);
//			$d['year'] = '2015';
//			$this->crud_model->insert($dbContext, $d);
//
//		}
		$this->reply(200);
	}

	private function request_get_stock_and_order_info()
	{
		$data = new stdClass();
		$this->load->model('grid_model');
		if (!isset($this->paras->relate->JID)) {
			$this->reply(501, "未选择期刊");
		}else{
			$this->paras->relate->JID = (int) $this->paras->relate->JID;
		}
		$this->paras->search = true;
		$this->paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $this->paras->relate->JID,
					"op" => "eq",
					"field" => "JID"
				)
			)
		);
		switch($this->key) {
			case "a":
				$this->grid_model->table("journalorders", array("JID", "OrderCount", "Year", "NoStart", "NoEnd" ), true);
				$this->paras->filters->rules[] = (object) array(
					"data" => 2,
					"op" => "eq",
					"field" => "SaleStyle"
				);
				break;
			case "b":
				$this->grid_model->table("arrivalmanage", array("JID", "Year", "Counts", "No"), true);
				break;
			case "c":
				$this->grid_model->table("journalstockmanage",array("JID","Year","Counts", "No"), true);
				break;
			case "d":
				if (!isset($this->paras->relate->CID)){
					$this->reply(501, "未选择客户");
				}
				$this->grid_model->table("deliverydetails",array("JID", "CID", "Year","Counts", "No"), true);
				$this->paras->filters->rules[] = (object) array(
					"data" => $this->paras->relate->CID,
					"op" => "eq",
					"field" => "CID"
				);
				break;
			case "e":
				$this->grid_model->table("publishrecords",array("JID","Year", "No", "OffPrintCount", "EditOfficeNeedType", "EditOfficeNeedCount"), true);
				break;
		}

		$this->grid_model->prepare();
		foreach($this->grid_model->crud_field as &$f){
			$f['prop'] &= ~(Crud_model::PROP_FIELD_MINIFY | Crud_model::PROP_FIELD_HIDE);
//			$f['width'] = 70;
//			if ($f['name'] == 'JID' || $f['name'] == 'CID') {
//				$f['width'] = 180;
//			}
		}
		$grid_info = $this->grid_model->grid_info();
		$data->headers = $grid_info->headers;
		$ret = $this->grid_model->wrapper_sheet($this->paras);
		$data->rows = $this->grid_model->sheet_to_grid($ret->data);
		$data->count = $ret->count;
		$data->sql = $ret->sql;
		return $data;
	}

	private function  request_get_orders_counts ()
	{
		$data = new stdClass();
		$this->load->model('grid_model');
		if (!isset($this->paras->relate->JID)){
			$this->reply(501, "未选择期刊");
		}else{
			$this->paras->relate->JID = (int) $this->paras->relate->JID;
		}
		if (!isset($this->paras->relate->Year)){
			$this->reply(501, "未选择年份");
		}else{
			$this->paras->relate->Year = (int) $this->paras->relate->Year;
		}
		if (!isset($this->paras->relate->No)){
			$this->reply(501, "未选择期次");
		}else{
			$this->paras->relate->No = (int) $this->paras->relate->No;
		}
		$this->paras->search = true;
		$this->paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $this->paras->relate->JID,
					"op" => "eq",
					"field" => "JID"
				),
				(object) array(
					"data" => $this->paras->relate->Year,
					"op" => "eq",
					"field" => "Year"
				),
				(object) array(
					"data" => $this->paras->relate->No,
					"op" => "eq",
					"field" => "NoStart"
				),
				(object) array(
					"data" => $this->paras->relate->No,
					"op" => "eq",
					"field" => "NoEnd"
				),
//				(object) array(
//					"data" => 0,
//					"op" => "eq",
//					"field" => "ReportStatus"
//				)
				
			)
		);
		$this->grid_model->table("journalorders", array("JID", "CID", "OrderCount", "Year", "NoStart", "NoEnd", "ReportStatus" ), true);
		$this->grid_model->prepare();
		foreach($this->grid_model->crud_field as &$f){
			$f['prop'] &= ~(Crud_model::PROP_FIELD_MINIFY | Crud_model::PROP_FIELD_HIDE);
//			$f['width'] = 50;
//			if ($f['name'] == 'JID' || $f['name'] == 'CID') {
//				$f['width'] = 180;
//			}
		}
		$grid_info = $this->grid_model->grid_info();
		$data->headers = $grid_info->headers;
		$ret = $this->grid_model->wrapper_sheet($this->paras);
		$data->rows = $this->grid_model->sheet_to_grid($ret->data);
		$data->count = $ret->count;
		$data->sql = $ret->sql;
		$this->load->model('crud_model');
		$this->crud_model->table('journalorders');
		$this->crud_model->from('journalorders b');
		$this->crud_model->parse($this->paras->filters);
		$this->crud_model->select('SUM(b.OrderCount)');
		$data->totalcount = $this->crud_model->cell('SUM(b.OrderCount)');
		
		return $data;
	}

	private function  request_get_report_counts ()
	{
		$data = new stdClass();
		$this->load->model('grid_model');
		if (!isset($this->paras->relate->JID)){
			$this->reply(501, "未选择期刊");
		}else{
			$JID = (int) $this->paras->relate->JID;
		}
		if (!isset($this->paras->relate->Year)){
			$this->reply(501, "未选择年份");
		}else{
			$Year = (int) $this->paras->relate->Year;
		}
		if (!isset($this->paras->relate->No)){
			$this->reply(501, "未选择期次");
		}else {
			$No = (int) $this->paras->relate->No;
		}
		$this->paras->search = true;
		$this->paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $JID,
					"op" => "eq",
					"field" => "JID"
				),
				(object) array(
					"data" => $Year,
					"op" => "eq",
					"field" => "Year"
				),
				(object) array(
					"data" => $No,
					"op" => "eq",
					"field" => "No"
				)
			)
		);
		$this->grid_model->table("reportcounts", array("JID", "BatchID", "Count"));
		$this->grid_model->prepare();
//		foreach($this->grid_model->crud_field as &$f){
//			$f['prop'] &= ~(Crud_model::PROP_FIELD_MINIFY | Crud_model::PROP_FIELD_HIDE);
//			$f['width'] = 60;
//			if ($f['name'] == 'JID') {
//				$f['width'] = 180;
//			}
//		}
//		$grid_info = $this->grid_model->grid_info("inline");
//		$data->headers = $grid_info->headers;
		$ret = $this->grid_model->wrapper_sheet($this->paras);
		if (is_array($ret->data) && count($ret->data)) {
			$data->total = $ret->data[0]['Count'];
		} else {
			$data->total = 0;
		}
//		$data->ret = $ret;
//		$data->rows = $this->grid_model->sheet_to_grid($ret->data);
//		$data->count = $ret->count;
//		$data->sql = $ret->sql;
//		$this->load->model('crud_model');
//		$this->crud_model->table('journalorders');
//		$this->crud_model->from('journalorders b');
//		$this->crud_model->parse($this->paras->filters);
//		$this->crud_model->select('SUM(b.OrderCount)');
//		$data->totalcount = $this->crud_model->cell('SUM(b.OrderCount)');

		$data->headers = array(
			( object ) array (
				"id" => "orderUnit",
				"width" => 160,
				"caption" => "类型"
			),
			( object ) array (
				"id" => "orderCount",
				"width" => 80,
				"caption" => "数量"
			)
		);
		$sql = <<<EOF
select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and ReportStatus = 1 and saleStyle = 5 and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) a
union
select '邮局外埠' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and ReportStatus = 1 and saleStyle = 4 and jyear ={$Year}  and nostart <= {$No} and noend >={$No}) b
union
select '邮局本市' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount  from `qkzx_journalorders`  where JID = {$JID} and ReportStatus = 1 and saleStyle = 3 and jyear ={$Year} and nostart <= {$No} and noend >= {$No}) c
union
SELECT '邮局本市东' AS orderUnit, orderCount FROM (SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} and ReportStatus = 1 AND saleStyle =20 AND Jyear ={$Year}  AND nostart = {$No}) e
union
SELECT '邮局本市西' AS orderUnit, orderCount FROM(SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} and ReportStatus = 1 AND saleStyle = 21 AND Jyear ={$Year} AND nostart ={$No} ) f
union
select '本社' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and ReportStatus = 1 and saleStyle in(1,2,6,7,8) and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) d
EOF;
//		$sql = "select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = ? and saleStyle = 5 and jyear =? and nostart <= ? and noend >=?) a";
		$ret = $this->db->query($sql);
		$data->rows = $ret->result();
		
		$sql = <<<EOF
select count(*) as count from `qkzx_journalorders` where JID = {$JID} and ReportStatus = 0 and jyear = {$Year} and nostart <= {$No} and noend >= {$No}
EOF;
		$ret = $this->db->query($sql);
		$ret = $ret->row_array();
		$data->report = $ret['count'];
		return $data;
	}
	private function  request_get_arrive_counts ()
	{
		$data = new stdClass();
		$this->load->model('grid_model');
		if (!isset($this->paras->relate->JID)){
			$this->reply(501, "未选择期刊");
		}else{
			$JID = (int) $this->paras->relate->JID;
		}
		if (!isset($this->paras->relate->Year)){
			$this->reply(501, "未选择年份");
		}else{
			$Year = (int) $this->paras->relate->Year;
		}
		if (!isset($this->paras->relate->No)){
			$this->reply(501, "未选择期次");
		}else {
			$No = (int) $this->paras->relate->No;
		}

		$data->headers = array(
			( object ) array (
				"id" => "orderunit",
				"width" => 160,
				"caption" => "类型"
			),
			( object ) array (
				"id" => "counts",
				"width" => 80,
				"caption" => "数量"
			)
		);
		$sql = <<<EOF
select "预留库存" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$JID} and year = {$Year} and {$No} between nostart and noend and jo.saleStyle=2) a
UNION
select "补刊" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$JID} and year = {$Year} and {$No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle=9) b
UNION
select "赠刊" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$JID} and year = {$Year} and {$No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle=8) c
UNION
select "付费订购" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$JID} and year = {$Year} and {$No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle in (1,5,6,7)) d
EOF;

		$ret = $this->db->query($sql);
		$data->rows = $ret->result();
		$data->total = 0;
		foreach($data->rows as $row) {
			if($row->counts) {
				$data->total += $row->counts;
			}
		}
		return $data;
	}

	private function request_publishnotifydetails_grid()
	{
		$this->load->model('grid_model');
		$this->grid_model->table("publishnotifydetails", array(
			"PublishContent",
			"Pages",
			"pageCount",
			"colourCount",
			"PublishCount",
			"PaperName",
			"Size",
			"KaiShu",
			"PaperCount",
			"ZoomPercent",
			"ZoomPaperCount",
			"TotalPaper",
			"CreateTime",
			"Note"
		));
		$this->grid_model->prepare();
		$ret = new stdClass ();
		$grid_info = $this->grid_model->grid_info("inline");
		$ret->gridName = $this->grid_model->name;
		$ret->gridPrimary = $this->grid_model->primary;

		$ret->gridHeaders = $grid_info->headers;
		$ret->gridSetting = $grid_info->setting;
		return $ret;
	}

	private function request_publishrecords()
	{
		$data = new stdClass();
		$this->load->model('grid_model');
		$this->grid_model->table("责任卡(印制单)");
		$this->grid_model->prepare();

		if (!isset($this->paras->relate->JID)){
			$this->reply(501, "未选择期刊");
		}else{
			$this->paras->relate->JID = (int) $this->paras->relate->JID;
		}
		if (!isset($this->paras->relate->Year)){
			$this->reply(501, "未选择年份");
		}else{
			$this->paras->relate->Year = (int) $this->paras->relate->Year;
		}
		if (!isset($this->paras->relate->No)){
			$this->reply(501, "未选择期次");
		}else {
			$this->paras->relate->No = (int) $this->paras->relate->No;
		}
		$this->paras->search = true;
		$this->paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $this->paras->relate->JID,
					"op" => "eq",
					"field" => "JID"
				),
				(object) array(
					"data" => $this->paras->relate->Year,
					"op" => "eq",
					"field" => "Year"
				),
				(object) array(
					"data" => $this->paras->relate->No,
					"op" => "eq",
					"field" => "No"
				)
			)
		);
		$ret = $this->grid_model->wrapper_sheet($this->paras);
		$data->rows = $this->grid_model->sheet_to_grid($ret->data);
		return $data;
	}

	private function request_publishnotify_check()
	{
		if (isset($this->paras->ids)) {
			$this->db->where_in('id', $this->paras->ids);
			$this->db->where('Status', 0);
			$this->db->update('publishnotify', array('Status' => 1));
		}
		return 1;
	}

	private function request_publishnotify_check2()
	{
		if (isset($this->paras->ids)) {
			$this->db->where_in('id', $this->paras->ids);
			$this->db->where('Status', 3);
			$this->db->update('publishnotify', array('Status' => 2, 'finaceApprove' => 1));
		}
		return 1;
	}

	private function request_publishnotify_submit()
	{
		$change =array();
		if (isset($this->paras->ids)) {
			$this->db->where_in('id', $this->paras->ids);
			$this->db->where('Status', 1);
			$data = $this->db->get('publishnotify')->result_array();
			foreach($data as $d) {
				$this->db->query("set @AID ='{$d['AID']}'");
				$this->db->query("set @AName ='{$d['AName']}'");
				$this->db->query("set @PNID ={$d['id']}");
				$this->db->query("CALL SubmitFinanceCostProc(@AID, @AName, @PNID)");
				$change[] = $d['id'];
			}
			if (count($change)) {
				$this->db->where_in('id', $change);
				$this->db->update('publishnotify', array('Status' => 3));
			}
		}
		return 1;
	}

	private function request_paper_stock_in()
	{
		$ret = new stdClass ();

		$this->load->model('grid_model');
		$this->grid_model->table('纸张出入库记录(入库)');
		$this->grid_model->prepare();
		$grid_info = $this->grid_model->grid_info();
		$ret->gridName = $this->grid_model->name;
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->gridForm = "App.GridForm";
		if ($this->grid_model->filter) {
			$ret->gridFilter = "App.GridFilter";
		}
		if ($this->grid_model->export) {
			$ret->gridExporter = "App.GridExporter";
		}
		if ($this->grid_model->import) {
			$ret->gridExporter = "App.GridImporter";
		}
		$ret->gridFormGroups=array();
		$groups = explode(";", $this->grid_model->crud_table['groups']);
		if(count($groups)) {
			foreach ($groups as $g) {
				$info = explode(":", $g);
				if (count($info) == 5) {
					$ret->gridFormGroups[] = (object)array(
						"name" => $info[0],
						"x" => $info[1],
						"y" => $info[2],
						"w" => $info[3],
						"h" => $info[4]
					);
				}
			}
		}

		$ret->gridHeaders = $grid_info->headers;
		$ret->gridId = (int)$this->grid_model->crud_table['id'];
		$ret->gridSetting = $grid_info->setting;
		$ret->gridFormWidth = (int)$this->grid_model->crud_table['w'];
		$ret->gridFormHeight = (int)$this->grid_model->crud_table['h'];
		$ret->gridTreeMode = ($this->grid_model->pid != null)?$this->grid_model->pid:null;
		$ret->gridGroup = ($this->grid_model->group != null)?"gid":null;
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->dataFilter = $this->grid_model->crud_table['filter'];
		return $ret;
	}
	private function request_paper_stock_out()
	{
		$ret = new stdClass ();

		$this->load->model('grid_model');
		$this->grid_model->table('纸张出入库记录(出库)');
		$this->grid_model->prepare();
		$grid_info = $this->grid_model->grid_info();
		$ret->gridName = $this->grid_model->name;
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->gridForm = "App.GridForm";
		if ($this->grid_model->filter) {
			$ret->gridFilter = "App.GridFilter";
		}
		if ($this->grid_model->export) {
			$ret->gridExporter = "App.GridExporter";
		}
		if ($this->grid_model->import) {
			$ret->gridExporter = "App.GridImporter";
		}
		$ret->gridFormGroups=array();
		$groups = explode(";", $this->grid_model->crud_table['groups']);
		if(count($groups)) {
			foreach ($groups as $g) {
				$info = explode(":", $g);
				if (count($info) == 5) {
					$ret->gridFormGroups[] = (object)array(
						"name" => $info[0],
						"x" => $info[1],
						"y" => $info[2],
						"w" => $info[3],
						"h" => $info[4]
					);
				}
			}
		}

		$ret->gridHeaders = $grid_info->headers;
		$ret->gridId = (int)$this->grid_model->crud_table['id'];
		$ret->gridSetting = $grid_info->setting;
		$ret->gridFormWidth = (int)$this->grid_model->crud_table['w'];
		$ret->gridFormHeight = (int)$this->grid_model->crud_table['h'];
		$ret->gridTreeMode = ($this->grid_model->pid != null)?$this->grid_model->pid:null;
		$ret->gridGroup = ($this->grid_model->group != null)?"gid":null;
		$ret->gridPrimary = $this->grid_model->primary;
		$ret->dataFilter = $this->grid_model->crud_table['filter'];
		return $ret;
	}

	private function request_delivery_by_cid()
	{
		$CID = $this->paras->CID->value;
		$AID = $_SESSION['userinfo']['id'];
		$BatchID = date('Ymd');
		$this->db->query("set @CID={$CID}");
		$this->db->query("set @AID={$AID}");
		$this->db->query("set @BatchID='{$BatchID}'");
		$this->db->query("set @result=''");
		$result = $this->db->query("call DeliveryCustomProc2(@CID,@AID,@BatchID,@result)");
		//FIXME: 检查返回值
		return 1;
	}

	private function request_delivery_by_jid()
	{
		$JID = (int)$this->paras->JID->value;
		$Year = (int)$this->paras->Year->value;
		$No = (int)$this->paras->No->value;
		$AID = $_SESSION['userinfo']['id'];
		$BatchID = date('Ymd');
		$this->db->query("set @JID={$JID}");
		$this->db->query("set @Year={$Year}");
		$this->db->query("set @No={$No}");
		$this->db->query("set @AID={$AID}");
		$this->db->query("set @BatchID='{$BatchID}'");
		$this->db->query("set @result=''");
		$result = $this->db->query("call DeliveryBatchByJournal(@JID,@Year,@No,@BatchID,@AID,@result)");
		//FIXME: 检查返回值
		return 1;
	}
	private function request_delivery()
	{
		$BatchID = date('Ymd');
		foreach($this->paras->data as $d){
			$save = array(
				"batchID" => $BatchID,
				"AID" => $_SESSION['userinfo']['id'],
				"JID" => (int)$d->JID,
				"CID" => (int)$d->CID,
				"Year" => (int)$d->Year,
				"Volume" => "",
				"No" => (int)$d->No,
				"Counts" => (int)$d->RealCounts,
				"DeliveryTime" => date('Y-m-d H:i:s'),
				"DeliveStatus" => 1, //报订发货
				"Note" => "已发",
				"YingFa" => (int)$d->NeedCounts,
				"DaiFa" => (int)$d->NeedCounts - (int)$d->RealCounts,
				"yiFa" => (int)$d->SendCounts + (int)$d->RealCounts
			);
			$this->db->insert('deliverydetails', $save);
		}
		return 1;
	}

	private function request_delivery_retail()
	{
		$BatchID = date('Ymd');
		foreach($this->paras->data as $d){
			$this->db->where('JID', (int)$d->JID);
			$this->db->where('CID', (int)$d->CID);
			$this->db->where('Year', (int)$d->Year);
			$this->db->where('No', (int)$d->No);
			$this->db->where_in('StockTag', array(10,11));
			$smd = $this->db->get('stockmanagedetails')->row_array();
			if ($smd) {
				if ($smd['StockTag'] == '10') {
					$smd['StockTag'] = '2';
					$smd['Note'] = "零售出库(已发货)!";
				} else {
					$smd['StockTag'] = '3';
					$smd['Note'] = "补刊出库(已发货)!";
				}

				$save = array(
					"batchID" => $BatchID,
					"AID" => $_SESSION['userinfo']['id'],
					"JID" => $d->JID,
					"CID" => $d->CID,
					"Year" => $d->Year,
					"Volume" => "",
					"No" => $d->No,
					"Counts" => $d->Counts,
					"DeliveryTime" => date('Y-m-d H:i:s'),
					"DeliveStatus" => $smd['StockTag']
				);
				$this->db->insert('deliverydetails', $save);


				$this->db->where('id', $smd['id']);
				$this->db->update('stockmanagedetails', $smd);

			}
		}
		return 1;
	}

	private function request_delivery_stock()
	{
		$this->load->model('JournalStockManage');
		foreach($this->paras->data as $d){
			$this->JournalStockManage->prepare($d->JID, $d->Year, $d->No);
			$this->JournalStockManage->stock_in($d->RealCounts, 1);
		}
		return 1;
	}

	private function request_delivery_append()
	{
		$this->load->model('JournalStockManage');
		$BatchID = date('Ymd');
		foreach($this->paras->data as $d){
			if ($this->JournalStockManage->prepare($d->JID, $d->Year, $d->No)) {

				$save = array(
					"batchID" => $BatchID,
					"AID" => $_SESSION['userinfo']['id'],
					"JID" => $d->JID,
					"CID" => $d->CID,
					"Year" => $d->Year,
					"Volume" => "",
					"No" => $d->No,
					"Counts" => $d->RealCounts,
					"DeliveryTime" => date('Y-m-d H:i:s'),
					"DeliveStatus" => 3, //补发
					"Note" => "从库存补发!"
				);
				$this->db->insert('deliverydetails', $save);
				$this->JournalStockManage->stock_out($d->RealCounts, 5, $d->CID, "补发出库");
			}
		}
		return 1;
	}

	private function request_delivery_special()
	{
		$this->load->model('JournalStockManage');
		foreach($this->paras->data as $d){
			if ($this->JournalStockManage->prepare($d->JID, $d->Year, $d->No)) {
				$this->JournalStockManage->stock_out($d->Counts, 4, NULL, "特殊出库");
			}
		}
		return 1;
	}
	
	private function request_check_arrival()
	{
		$JID = (int)$this->paras->JID;
		$Year = (int)$this->paras->Year;
		$No = (int)$this->paras->No;
		$arrival = $this->db->get_where("arrivalmanage", array("JID" => $JID, "Year" => $Year, "No" => $No))->row_array();
		if ($arrival) {
			$this->reply(500, "请注意,该期刊已经有到货");
		}
		if ($No > 1) {
			$No = $No - 1;
			$arrival = $this->db->get_where("arrivalmanage", array("JID" => $JID, "Year" => $Year, "No" => $No))->row_array();
			if(!$arrival) {
				$this->reply(500, "请注意,该期刊第{$No}期还没有到货");
			}
		}
		return 1;
	}

	private function request_refunds()
	{
		$JID = (int)$this->paras->JID;
		$Year = (int)$this->paras->Jyear;
		$No = (int)$this->paras->NoStart;
		$Counts = (int)$this->paras->OrderCount;
		$CID = (int)$this->paras->CID;
		$this->load->model('JournalStockManage');
		if ($this->JournalStockManage->prepare($JID, $Year, $No)) {
			$this->JournalStockManage->stock_in($Counts, 6, NULL, "退订入库");
			
			$BatchID = date('Ymd');
			$save = array(
				"batchID" => $BatchID,
				"AID" => $_SESSION['userinfo']['id'],
				"JID" => $JID,
				"CID" => $CID,
				"Year" => $Year,
				"Volume" => "",
				"No" => $No,
				"Counts" => -$Counts,
				"DeliveryTime" => date('Y-m-d H:i:s'),
				"DeliveStatus" => 4, //退货
				"Note" => "退订退货"
//				"YingFa" => 0,
//				"DaiFa" => 0,
//				"yiFa" => 0
			);
			$this->db->insert('deliverydetails', $save);
		}
		return 1;
	}
	
	private function request_update_sales_stats()
	{
		ini_set('max_execution_time', 0);
		$settings = $this->auth_model->user_get_settings();
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders');
		$this->db->where(array('saleStyle' => 5, 'Jyear' => $settings->workyear));
		$this->db->group_by(array('JID', 'NoStart'));
		$sql1 = $this->db->get_compiled_select();
		
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders');
		$this->db->where('Jyear' , $settings->workyear);
		$this->db->where_in('saleStyle', array(20,21));
		$this->db->group_by(array('JID', 'NoStart'));
		$sql2 = $this->db->get_compiled_select();
		
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders');
		$this->db->where(array('saleStyle' => 4, 'Jyear' => $settings->workyear));
		$this->db->group_by(array('JID', 'NoStart'));
		$sql3 = $this->db->get_compiled_select();
		
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders');
		$this->db->where('Jyear' , $settings->workyear);
		$this->db->where_in('saleStyle', array(1,2,6,8));
		$this->db->group_by(array('JID', 'NoStart'));
		$sql4 = $this->db->get_compiled_select();
		
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders');
		$this->db->where(array('saleStyle' => 8, 'Jyear' => $settings->workyear));
		$this->db->group_by(array('JID', 'NoStart'));
		$sql5 = $this->db->get_compiled_select();
		
		$this->db->select(array('JID', 'NoStart', 'sum(orderCount) AS OrderCount'));
		$this->db->from('journalorders jo');
		$this->db->from('customers cu');
		$this->db->where('jo.year', $settings->workyear);
		$this->db->where('jo.cid = cu.id');
		$this->db->group_start();
		$this->db->group_start();
		$this->db->where(array('cu.IsDelivery' => 1, 'cu.isAlone' => 0, 'cu.Discount<' => 100));
		$this->db->where_not_in('jo.SaleStyle', array(2,5));
		$this->db->group_end();
		$this->db->or_where('jo.SaleStyle', 7);
		$this->db->group_end();
		$this->db->group_by(array('jo.JID', 'jo.NoStart'));
		$sql6 = $this->db->get_compiled_select();
		
		$this->db->from('deliverydetails a');
		//$this->db->select(array('a.JID', 'a.No', 'b.name'));
		$this->db->join('journalbaseinfo b', 'a.JID = b.id', 'LEFT');
		$this->db->join('publishnotify c', 'a.JID = c.JID and a.No = c.No', 'LEFT');
		$this->db->join('journalstockmanage d', 'a.JID = d.JID and a.No = d.No', 'LEFT');
		$this->db->join("({$sql1}) e", 'a.JID = e.JID and a.No = e.NoStart', 'LEFT');
		$this->db->join("({$sql2}) f", 'a.JID = f.JID and a.No = f.NoStart', 'LEFT');
		$this->db->join("({$sql3}) g", 'a.JID = g.JID and a.No = g.NoStart', 'LEFT');
		$this->db->join("({$sql4}) h", 'a.JID = h.JID and a.No = h.NoStart', 'LEFT');
		$this->db->join("({$sql5}) i", 'a.JID = i.JID and a.No = i.NoStart', 'LEFT');
		$this->db->join("({$sql6}) j", 'a.JID = j.JID and a.No = j.NoStart', 'LEFT');
		$this->db->select(array('a.JID', 'a.No', 'b.Name', 'b.NofPerYear', 
			'b.Price', 'b.SaleDiscount', 'b.Classify', 'b.PublishStyle', 'b.Year','b.Price',
			'ifnull(c.PublishCounts,0) as PublishCounts',
			'ifnull(d.Counts,0) as StockCount',
			'ifnull(e.OrderCount,0) as EditOrderCount',
			'ifnull(f.OrderCount,0) as PostInCity',
			'ifnull(g.OrderCount,0) as PostOutCity',
			'ifnull(h.OrderCount,0) as ToPress',
			'ifnull(i.OrderCount,0) as Gift',
			'ifnull(j.OrderCount,0) as SelfSale'
		
		));
		$this->db->where('a.year', $settings->workyear);
		$this->db->group_by(array('a.JID', 'a.No'));
		$this->db->order_by('a.JID, a.No');
		$data = $this->db->get()->result_array();
		$this->db->delete('jounalsale_statcache', array('Year'=>$settings->workyear));
		foreach($data as &$d) {
			$d['PublishTotalPrice'] = $d['Price'] * $d['PublishCounts'];
			$d['PostTotal'] = $d['PostInCity'] + $d['PostOutCity'];
			$d['DeliverCount'] = $d['EditOrderCount'] + $d['PostTotal'] + $d['SelfSale'] + $d['Gift'];
			$d['DeliverTotalCost'] = $d['DeliverCount'] * $d['Price'];
			$d['SaleCount'] = $d['PostTotal'] - 10 + $d['SelfSale'];			//销售数量
			$d['SaleCountCost'] = $d['SaleCount'] * $d['Price'];				//销售码洋
			$d['SaleDiscount'] = ((int)$d['SaleDiscount'] )? $d['SaleDiscount'] : 100;
			$d['RealCost'] = $d['SaleCountCost'] * $d['SaleDiscount'] / 100;			//销售实洋
			unset($d['Name']);
			unset($d['NofPerYear']);
			unset($d['DeliverTotalCost']);
			$this->db->insert('jounalsale_statcache', $d);
			
		}
		return "统计完成";
	}
	
	private function request_update_cost_stats()
	{
		ini_set('max_execution_time', 0);
		$settings = $this->auth_model->user_get_settings();
		
		$this->db->select(array('PNID','ifnull(sum((`Price` * `TotalPaper`)),0) as Fee'));
		$this->db->from('publishnotifydetails');
		$this->db->group_by('PNID');
		$sql1 = $this->db->get_compiled_select();
		
		$this->db->select(array('PNID','ifnull(sum((`Price` * `Counts`)),0) as Fee'));
		$this->db->from('publishnotifyprice');
		$this->db->group_by('PNID');
		$sql2 = $this->db->get_compiled_select();
		
		$this->db->select(array('y.JID', 'y.No', 'ifnull(sum((`x`.`Price` * `x`.`Count`)),0) as Fee'));
		$this->db->from('publishbefore x');
		$this->db->join('journalfeemaster y', 'x.JFMID = y.id and y.Type = 1', 'LEFT');
		$this->db->group_by(array('y.JID', 'y.No'));
		$sql3 = $this->db->get_compiled_select();
		
		$this->db->select(array('y.JID', 'y.No', 'ifnull(sum((`x`.`Price` * `x`.`PaperCount`)),0) as Fee'));
		$this->db->from('superfluityprocess x');
		$this->db->join('journalfeemaster y', 'x.JFMID = y.id and y.Type = 2', 'LEFT');
		$this->db->group_by(array('y.JID', 'y.No'));
		$sql4 = $this->db->get_compiled_select();
		
		$this->db->select(array('y.JID', 'y.No', 'ifnull(sum((((`x`.`PayFee`+`x`.`EditFee`)+`x`.`CheckFee`)+`x`.`DrawFee`)+`x`.`RemitFee`),0) as Fee'));
		$this->db->from('factjournalfee x');
		$this->db->join('journalfeemaster y', 'x.JFMID = y.id and y.Type = 3', 'LEFT');
		$this->db->group_by(array('y.JID', 'y.No'));
		$sql5 = $this->db->get_compiled_select();
		
		$this->db->from('publishnotify a');
		$this->db->join('journalbaseinfo b', 'a.JID = b.id', 'LEFT');
		$this->db->join('publishrecords c', 'a.JID = c.JID and a.No = c.No', 'LEFT');
		$this->db->join("({$sql1}) d", 'a.id = d.PNID', 'LEFT');
		$this->db->join("({$sql2}) e", 'a.id = e.PNID', 'LEFT');
		$this->db->join("({$sql3}) f", 'a.JID = f.JID and a.No = f.No', 'LEFT');
		$this->db->join("({$sql4}) g", 'a.JID = g.JID and a.No = g.No', 'LEFT');
		$this->db->join("({$sql5}) h", 'a.JID = h.JID and a.No = h.No', 'LEFT');
		$this->db->select(array('a.JID', 'a.No', 'a.PublishCounts', 'b.year' , 'c.PrintSheetCount',
			'ifnull(d.Fee,0) as PaperFee',
			'ifnull(e.Fee,0) as PrintFee',
			'ifnull(f.Fee,0) as PublishBeforeFee',
			'ifnull(g.Fee,0) as SuperFluityProcessFee',
			'ifnull(h.Fee,0) as FactJournalFee'
		));
		$this->db->group_by(array('a.JID', 'a.No'));
		$this->db->order_by('a.JID, a.No');
		$this->db->where('b.year',  $settings->workyear);

		$data = $this->db->get()->result_array();
		$this->db->delete('journalcost_statcache', array('Year'=>$settings->workyear));
		foreach($data as &$d) {
			$d['TotalFee'] = $d['PaperFee'] + $d['PrintFee'] + $d['PublishBeforeFee'] + $d['SuperFluityProcessFee'] + $d['FactJournalFee'];
			$this->db->insert('journalcost_statcache', $d);
			
		}
		return "统计完成";
	}

	public function request_calc_paper_details()
	{
		$this->load->model('QUtils');
		$data = $this->paras->data;
		
		$calc = array();
		$calc['PublishContent'] = $data->PublishContent->value;
		$calc['Pages'] = $data->Pages;
		$calc['paperDeduceID'] = $data->paperDeduceID->value;
		$calc['KaiShu'] = $data->KaiShu->value;
		$calc['Size'] = $data->Size->value;
		$calc['PublishCount'] = $data->PublishCount;
		$calc['colourCount'] = $data->colourCount;
		$calc['PaperCount'] = $data->PaperCount;
		$calc['ZoomPercent'] = $data->ZoomPercent;
		$calc['ZoomPaperCount'] = $data->ZoomPaperCount;
		$calc['TotalPaper'] = $data->TotalPaper;
		$calc['Note'] = $data->Note;
		$this->QUtils->calc_paper_details($calc);
		
		return $calc;
	}

}
