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
		}
		if (!isset($this->paras->relate->Year)){
			$this->reply(501, "未选择年份");
		}
		if (!isset($this->paras->relate->No)){
			$this->reply(501, "未选择期次");
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
        $this->load->library( 'xui_utils' );
        $ret = new stdClass ();
        $grid_info = $this->grid_model->grid_info("inline");
        $ret->gridName = $this->grid_model->name;
        $ret->gridPrimary = $this->grid_model->primary;

        $ret->gridHeaders = $grid_info->headers;
        $ret->gridSetting = $grid_info->setting;
        return $ret;
    }

}
