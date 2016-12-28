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
	
	public function request_generate_new_plan()
	{
		$this->load->model('crud_model');
		$dbContext = $this->crud_model->table('journalbaseinfo');
		$this->crud_model->prepare($dbContext, false);
		$this->crud_model->parm($dbContext, 'where', 'year', '2014');
		$data = $this->crud_model->sheet($dbContext);

		foreach($data as &$d) {
			unset($d['id']);
			$d['year'] = '2015';
			$this->crud_model->insert($dbContext, $d);

		}
		$this->reply(200, "Success");
	}
	
	public function request_get_stock_and_order_info()
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
				$this->grid_model->table("arrivalmanage", array("JID", "Year", "Counts", "No", "KeepCounts"), true);
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
		}

		$this->grid_model->prepare();
		foreach($this->grid_model->crud_field as &$f){
			$f['prop'] &= ~(Crud_model::PROP_FIELD_MINIFY | Crud_model::PROP_FIELD_HIDE);
			$f['width'] = 60;
			if ($f['name'] == 'JID' || $f['name'] == 'CID') {
				$f['width'] = 180;
			}
		}
		$grid_info = $this->grid_model->grid_info();
		$data->headers = $grid_info->headers;
		$ret = $this->grid_model->wrapper_sheet($this->paras);
		$data->rows = $this->grid_model->sheet_to_grid($ret->data);
		$data->count = $ret->count;
		$data->sql = $ret->sql;
		return $data;
	}
	
	public function  request_get_orders_counts ()
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
				(object) array(
					"data" => 0,
					"op" => "eq",
					"field" => "ReportStatus"
				)
				
			)
		);
		$this->grid_model->table("journalorders", array("JID", "CID", "OrderCount", "Year", "NoStart", "NoEnd" ), true);
		$this->grid_model->prepare();
		foreach($this->grid_model->crud_field as &$f){
			$f['prop'] &= ~(Crud_model::PROP_FIELD_MINIFY | Crud_model::PROP_FIELD_HIDE);
			$f['width'] = 60;
			if ($f['name'] == 'JID' || $f['name'] == 'CID') {
				$f['width'] = 180;
			}
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

	public function  request_get_report_counts ()
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
select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$this->paras->relate->JID} and saleStyle = 5 and jyear = {$this->paras->relate->Year} and nostart <= {$this->paras->relate->No} and noend >= {$this->paras->relate->No}) a
union
select '邮局外埠' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$this->paras->relate->JID} and saleStyle = 4 and jyear ={$this->paras->relate->Year}  and nostart <= {$this->paras->relate->No} and noend >={$this->paras->relate->No}) b
union
select '邮局本市' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount  from `qkzx_journalorders`  where JID = {$this->paras->relate->JID} and saleStyle = 3 and jyear ={$this->paras->relate->Year} and nostart <= {$this->paras->relate->No} and noend >= {$this->paras->relate->No}) c
union
SELECT '邮局本市东' AS orderUnit, orderCount FROM (SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$this->paras->relate->JID} AND saleStyle =20 AND Jyear ={$this->paras->relate->Year}  AND nostart = {$this->paras->relate->No}) e
union
SELECT '邮局本市西' AS orderUnit, orderCount FROM(SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$this->paras->relate->JID} AND saleStyle = 21 AND Jyear ={$this->paras->relate->Year} AND nostart ={$this->paras->relate->No} ) f
union
select '本社' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$this->paras->relate->JID} and saleStyle in(1,2,6,8) and jyear = {$this->paras->relate->Year} and nostart <= {$this->paras->relate->No} and noend >= {$this->paras->relate->No}) d

EOF;
//        $sql = "select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = ? and saleStyle = 5 and jyear =? and nostart <= ? and noend >=?) a";
        $db = "db_".__LINE__;
        $this->load->model ('grid_model', $db);
        $ret = $this->$db->query($sql);
		$data->rows = $ret->result();
        return $data;
	}
	public function  request_get_arrive_counts ()
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
		}else {
            $this->paras->relate->No = (int) $this->paras->relate->No;
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
select "预留库存" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$this->paras->relate->JID} and year = {$this->paras->relate->Year} and {$this->paras->relate->No} between nostart and noend and jo.saleStyle=2) a
UNION
select "补刊" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$this->paras->relate->JID} and year = {$this->paras->relate->Year} and {$this->paras->relate->No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle=9) b
UNION
select "赠刊" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$this->paras->relate->JID} and year = {$this->paras->relate->Year} and {$this->paras->relate->No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle=8) c
UNION
select "付费订购" as orderunit,counts from (select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = {$this->paras->relate->JID} and year = {$this->paras->relate->Year} and {$this->paras->relate->No} between nostart and noend and jo.isneedDeliver = 1 and jo.saleStyle in (1,5,6,7)) d
EOF;
//        $sql = "select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = ? and saleStyle = 5 and jyear =? and nostart <= ? and noend >=?) a";
        $db = "db_".__LINE__;
        $this->load->model ('grid_model', $db);
        $ret = $this->$db->query($sql);
		$data->rows = $ret->result();
        $data->total = 0;
        foreach($data->rows as $row) {
            if($row->counts) {
                $data->total += $row->counts;
            }
        }
        return $data;
	}

    function request_publishnotifydetails_grid($paras = null)
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

    function request_publishrecords($paras = null)
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

    public function request_publishnotify_check()
    {
        $this->load->model('db_model');
        if (isset($this->paras->ids)) {
            $this->db_model->where_in('id', $this->paras->ids);
            $this->db_model->where('Status', 0);
            $this->db_model->update('publishnotify', array('Status' => 1));
        }
        return 1;
    }

    public function request_publishnotify_submit()
    {
        $change =array();
        $this->load->model('db_model');
        if (isset($this->paras->ids)) {
            $this->db_model->where_in('id', $this->paras->ids);
            $this->db_model->where('Status', 1);
            $data = $this->db_model->get('publishnotify')->result_array();
            foreach($data as $d) {
                $this->db_model->query("set @AID ='{$d['AID']}'");
                $this->db_model->query("set @AName ='{$d['AName']}'");
                $this->db_model->query("set @PNID ={$d['id']}");
                $this->db_model->query("CALL SubmitFinanceCostProc(@AID, @AName, @PNID)");
                $change[] = $d['id'];
            }
            if (count($change)) {
                $this->db_model->where_in('id', $change);
                $this->db_model->update('publishnotify', array('Status' => 3));
            }
        }
        return 1;
    }

    public function request_paper_stock_in()
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

        $db = "db_".__LINE__;
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
    public function request_paper_stock_out()
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

    public function request_delivery_by_cid()
    {
        $CID = $this->paras->CID->value;
        $AID = 0;
        $BatchID = date('ymd');
        $this->load->model('db_model');
        $this->db_model->query("set @CID={$CID}");
        $this->db_model->query("set @AID={$AID}");
        $this->db_model->query("set @BatchID='{$BatchID}'");
        $this->db_model->query("set @result=''");
        $result = $this->db_model->query("call DeliveryCustomProc2(@CID,@AID,@BatchID,@result)");
        //FIXME: 检查返回值
        return 1;
    }

    public function request_delivery_by_jid()
    {
        $JID = $this->paras->JID->value;
        $Year = $this->paras->Year->value;
        $No = $this->paras->No->value;
        $AID = 0;
        $BatchID = date('ymd');
        $this->load->model('db_model');
        $this->db_model->query("set @JID={$JID}");
        $this->db_model->query("set @Year={$Year}");
        $this->db_model->query("set @No={$No}");
        $this->db_model->query("set @AID={$AID}");
        $this->db_model->query("set @BatchID='{$BatchID}'");
        $this->db_model->query("set @result=''");
        $result = $this->db_model->query("call DeliveryBatchByJournal(@JID,@Year,@No,@BatchID,@AID,@result)");
        //FIXME: 检查返回值
        return 1;
    }
    public function request_delivery()
    {
        $this->load->model('db_model');
        $BatchID = date('ymd');
        foreach($this->paras->data as $d){
            $save = array(
                "batchID" => $BatchID,
                "JID" => $d->JID,
                "CID" => $d->CID,
                "Year" => $d->Year,
                "Volume" => "",
                "No" => $d->No,
                "Counts" => $d->RealCounts,
                "DeliveryTime" => date('Y-m-d h:i:s'),
                "DeliveStatus" => $d->DeliveStatus,
                "Note" => "已发",
                "YingFa" => $d->NeedCounts,
                'DaiFa' => $d->NeedCounts - $d->RealCounts
            );
            $this->db_model->insert('deliverydetails', $save);
        }
        return 1;
    }

    public function request_delivery_retail()
    {
        $this->load->model('db_model');
        $BatchID = date('ymd');
        foreach($this->paras->data as $d){
            $this->db_model->where('JID', $d->JID);
            $this->db_model->where('CID', $d->CID);
            $this->db_model->where('Year', $d->Year);
            $this->db_model->where('No', $d->No);
            $this->db_model->where_in('StockTag', array(10,11));
            $smd = $this->db_model->get('stockmanagedetails')->row_array();
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
                    "JID" => $d->JID,
                    "CID" => $d->CID,
                    "Year" => $d->Year,
                    "Volume" => "",
                    "No" => $d->No,
                    "Counts" => $d->Counts,
                    "DeliveryTime" => date('Y-m-d h:i:s'),
                    "DeliveStatus" => $smd['StockTag']
                );
                $this->db_model->insert('deliverydetails', $save);


                $this->db_model->where('id', $smd['id']);
                $this->db_model->update('stockmanagedetails', $smd);

            }
        }
        return 1;
    }

    public function request_delivery_stock()
    {
        $this->load->model('crud_model');
        $this->load->model('JournalStockManage');
        foreach($this->paras->data as $d){
            $this->JournalStockManage->prepare($d->JID, $d->Year, $d->No);
            $this->JournalStockManage->stock_in($d->RealCounts, 1);
        }
        return 1;
    }

    public function request_delivery_append()
    {
        $this->load->model('crud_model');
        $this->load->model('JournalStockManage');
        $BatchID = date('ymd');
        foreach($this->paras->data as $d){
            if ($this->JournalStockManage->prepare($d->JID, $d->Year, $d->No)) {

                $save = array(
                    "batchID" => $BatchID,
                    "JID" => $d->JID,
                    "CID" => $d->CID,
                    "Year" => $d->Year,
                    "Volume" => "",
                    "No" => $d->No,
                    "Counts" => $d->RealCounts,
                    "DeliveryTime" => date('Y-m-d h:i:s'),
                    "DeliveStatus" => $d->DeliveStatus,
                    "Note" => "从库存补发!"
                );
                $this->crud_model->insert('deliverydetails', $save);
                $this->JournalStockManage->stock_out($d->RealCounts, 5, $d->CID, "补发出库");
            }
        }
        return 1;
    }

}
