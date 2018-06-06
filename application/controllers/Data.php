<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	
	public function index()
	{
		if (! $this->auth_model->check( true, uri_string() )) {
			die ();
		}
	}
	
	public function export()
	{
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','512M');
		
		$this->load->model('grid_model');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		
		$setting = $paras->setting;
		$paras->page = 1;
		$paras->size = 100;
		$fields = array();
		$sheet = array();
		
		$ret = $this->grid_model->table($table);
		if ($ret) {
			if ($this->grid_model->prepare()) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				
				$row = array();
				if ($paras->key) {
					$row[] = $this->grid_model->crud_field[$this->grid_model->primary]['caption'];
				}
				$colNumber = 0;
				foreach($setting as $s){
					if (isset($this->grid_model->crud_field[$s[0]]) && $this->grid_model->crud_field[$s[0]]['_role_r']) {
						
						$fields[$s[0]] = $s[1]?1:0;
						if ($s[1]) {
							$row[] = $this->grid_model->crud_field[$s[0]]['caption'];
							switch($this->grid_model->crud_field[$s[0]]['type']) {
								case Crud_model::TYPE_NUMBER:
									break;
								default:
									$colString = PHPExcel_Cell::stringFromColumnIndex($colNumber);
									$this->excel->getActiveSheet()->getStyle("{$colString}:{$colString}")
										->getNumberFormat()
										->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
									break;
							}
							$colNumber++;
						}
					}
				}
				$sheet[] = $row;
				$this->excel->getActiveSheet()->fromArray($sheet, NULL, "A1");
				$line = 2;
				$count = 0;
				$total = 0;
				
				while ($count == 0 || $count < $total) {
					unset($sheet);
					$sheet = array();
					
					
					$data = $this->grid_model->wrapper_sheet($paras);
					$this->grid_model->pop_cache();
					if (!count($data->data)) {
						break;
					}
					$total = $data->count;
					$paras->page++;
					foreach($data->data as $d) {
						unset($row);
						$row = array();
						if ($paras->key) {
							$row[] = $d[$this->grid_model->primary];
						}
						foreach($fields as $k=>$f) {
							if ($f) {
								if ($paras->raw) {
									$row[] = $d[$k];
								} elseif (isset($this->grid_model->crud_field[$k]['_caption'])) {
									$this->grid_model->wrapper_caption($this->grid_model->crud_field[$k], $d);
									$row[] = $d[$this->grid_model->crud_field[$k]['_caption']];
								} else {
									$row[] = $d[$k];
								}
							}
						}
						$sheet[] = $row;
					}

					$this->excel->getActiveSheet()->fromArray($sheet, NULL, "A{$line}");
					$count += count($data->data);
					$line += count($data->data);
				}
				
				
				
				
				$filename=date('Ymd').'.xls'; //save our workbook as this file name
				header('Content-Type: application/vnd.ms-excel'); //mime type
				header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
				header('Cache-Control: max-age=0'); //no cache

				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				$objWriter->save('php://output');
			} else {
				$error = "内部错误";
			}
		} else {
			$error = "数据表错误";
		}
	}
	
	public function upload()
	{
	
	}
	
	public function import()
	{
		
	}
	public function pdf()
	{
		$this->load->library('Pdf');
		$pdf = new Pdf('P', 'pt', 'A4', true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle('TCPDF Example 001');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
		
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setFooterData(array(0,64,0), array(0,64,128));
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		

		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('droidsansfallback', '', 14, '', true);
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();
		
		// set text shadow effect
//		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		
		// Set some content to print
		$html = <<<EOD

EOD;
		// Print text using writeHTMLCell()
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		$pdf->setCellPaddings(10,10,10,10);
		header('Content-Type: application/pdf'); //mime type
		header('Content-Disposition: attachment;filename="1.pdf"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		// ---------------------------------------------------------
		
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.

		$pdf->Output('php://output', 'I');

	}


	/**
	 *  打印印制单
	 */
	public function publish_notify($PNID)
	{
		ini_set('max_execution_time', 0);
		$this->load->library('excel');
		$this->load->model('grid_model', 'publishnotify');

		$this->publishnotify->table('publishnotify');
		$this->publishnotify->prepare(true, false);

		$paras = new stdClass();
		$paras->page = 1;
		$paras->size = 1;
		$paras->search = true;
		$paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $PNID,
					"op" => "eq",
					"field" => "id"
				)
			)
		);


		$ret = $this->publishnotify->wrapper_sheet($paras);

		$ret = $this->publishnotify->sheet_to_grid($ret->data, false, true);
		if (!isset($ret[0])) {
			die("无此记录");
		}
		$data = $ret[0]->cells;
//		print_r($data);
		$JID = $data['JID']->value;
		$Year = $data['Year']->value;
		$No = $data['No']->value;
		$total = 0;
		$deliver = null;
		if ($JID && $Year && $No) {
			$delivers = $this->db->get_where('publishnotifydeliver', array('PNID' => $PNID))->result_array();
			$deliver = array_column($delivers, 'DeliverCount',  'DeliverTarget');
			
			foreach($deliver as $c) {
				$total += $c;
			}

		}

		$this->load->model('grid_model', 'publishnotifydetails');

		$this->publishnotifydetails->table('publishnotifydetails');
		$this->publishnotifydetails->prepare(true, false);

		$paras->page = 1;
		$paras->size = 10;
		$paras->search = true;
		$paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $PNID,
					"op" => "eq",
					"field" => "PNID"
				)
			)
		);



		$ret = $this->publishnotifydetails->wrapper_sheet($paras);
		$details = $this->publishnotifydetails->sheet_to_grid($ret->data, false, true);

		$objPHPExcel = PHPExcel_IOFactory::load(BASEPATH.'../assets/reports/publishnotify.xls');
		$sheet = $objPHPExcel->getActiveSheet();
		
		$sheet->setCellValue("B2", $data['PID']->caption);	//承印厂
		$sheet->setCellValue("J2", date('Y-m-d'));	//打印日期
		$sheet->setCellValue("B3", "{$data['JID']->caption}\n\t({$data['Year']->value}年第{$data['No']->value}期)");	//刊名
		$sheet->setCellValue("I3", "{$data['PublishCounts']->value} 册");	//印数
//		$sheet->setCellValue("I3", "{$total} 册");							//印数
		$sheet->setCellValue("B4", $data['BindupTime']->value);				//印装完成日期
		$sheet->setCellValue("I4", "{$data['Price']->value} 元");			//定价
		$sheet->setCellValue("A6", $data['KaiId']->caption);				//开本
		$sheet->setCellValue("B6", $data['SizeId']->caption);				//开本尺寸
		$sheet->setCellValue("C6", $data['DingKou']->value);				//订口
		$sheet->setCellValue("D6", $data['QieKou']->value);					//切口
		$sheet->setCellValue("E6", $data['TianTou']->value);				//天头
		$sheet->setCellValue("F6", $data['DiJiao']->value);					//地脚
		$sheet->setCellValue("G6", $data['FanShen']->value);				//翻身
		$sheet->setCellValue("I5", $data['BindingMethod']->value);			//装订方法

		$i = 9;
		foreach($details as $detail) {
			$d = $detail->cells;
			$sheet->setCellValue("A{$i}", $d['PublishContent']->caption);		//印刷内容
			$sheet->setCellValue("B{$i}", $d['Pages']->value);					//页码
//			$sheet->setCellValue("C{$i}", "");									//页数
			$sheet->setCellValue("C{$i}", $d['paperDeduceID']->caption); 		//纸名
//			$sheet->setCellValue("F{$i}", $d['SizeId']->value);					//规格
			$sheet->setCellValue("F{$i}", $d['Size']->value);					//规格
			$sheet->setCellValue("G{$i}", $d['KaiShu']->caption);					//开数
			$sheet->setCellValue("H{$i}", $d['PublishCount']->value);			//印数
			$sheet->setCellValue("I{$i}", ' '.number_format($d['PaperCount']->value, 3));				//应用纸数
//			$sheet->setCellValue("J{$i}", $d['ZoomPercent']->value);			//加放率
			$sheet->setCellValue("J{$i}", ' '.number_format($d['ZoomPaperCount']->value, 3));			//加放纸数
			$sheet->setCellValue("K{$i}", ' '.number_format($d['TotalPaper']->value, 3));				//共计用纸数
			$i++;
		}

		$sheet->setCellValue("B15", $data['CoverInk']->caption . " " . $data['CoverType']->caption);	//封面墨色 + 封面处理方式
		$sheet->setCellValue("H15", $data['TextInk']->caption);			//正文墨色
//		$sheet->setCellValue("B16", $data['BindingOrder']->value);		//装订顺序
//		$sheet->setCellValue("A17", $data['Note']->value);	   //备注
		$sheet->setCellValue("B16", wordwrap($data['BindingOrder']->value, 125));	   //装订顺序
		$sheet->setCellValue("A17", wordwrap($data['Note']->value, 125));	   //备注
		if (is_array($deliver)) {
			if (!isset($deliver['本社'])) {
				$deliver['本社'] = '';
			}
			if (!isset($deliver['编辑部'])) {
				$deliver['编辑部'] = '';
			}
			if (!isset($deliver['邮局本市'])) {
				$deliver['邮局本市'] = '';
			}
			if (!isset($deliver['邮局本市东'])) {
				$deliver['邮局本市东'] = '';
			}
			if (!isset($deliver['邮局本市西'])) {
				$deliver['邮局本市西'] = '';
			}
			if (!isset($deliver['邮局外埠'])) {
				$deliver['邮局外埠'] = '';
			}
			$sheet->setCellValue("J17", $deliver['本社']);
			$sheet->setCellValue("J18", $deliver['编辑部']);
			$sheet->setCellValue("J19", $deliver['邮局本市']);
			$sheet->setCellValue("J20", $deliver['邮局本市东']);
			$sheet->setCellValue("J21", $deliver['邮局本市西']);
			$sheet->setCellValue("J22", $deliver['邮局外埠']);
		}
		$sheet->setCellValue("J23", $total);	  //合计
		$sheet->setCellValue("J24", $data['AID']->caption);	  //开单

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		header('Content-Type: application/pdf'); //mime type
		header('Content-Disposition: attachment;filename="YZD'.$PNID.'.pdf"');
//		header('Cache-Control: max-age=0'); //no cache
////		$objWriter->SetFont('droidsansfallback');
////		$objWriter->setCellPaddings(10,10,10,10);
		$objWriter->save('php://output');

	}

	/**
	 * 打印到货分发表
	 */
	public function delivers($ID/*$JID = 0, $Year = 0, $No = 0*/)
	{
		ini_set('max_execution_time', 0);
		$this->load->library('excel');

		$this->load->model('grid_model');
		$ret = $this->db->get_where('arrivalmanage', array('id' => (int) $ID))->result_array();
		
		if (!isset($ret[0])) {
			die();
		}
		$data = $ret[0];
		$JID = $data['JID'];
		$Year = $data['Year'];
		$No = $data['No'];
		$stock = $this->db->get_where('StockView', array('JID' => $JID, 'Year' => $Year, 'No' => $No))->row_array();
//		print_r($stock);
		$this->grid_model->table('DeliveryHistoryView');
		$this->grid_model->prepare(true, false);
		$paras = new stdClass();
		$paras->search = true;
		$paras->filters = (object) array(
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
		$ret = $this->grid_model->wrapper_sheet($paras);
		$ret = $this->grid_model->sheet_to_grid($ret->data, false, true);
		
//		print_r($data);
		$objPHPExcel = PHPExcel_IOFactory::load(BASEPATH.'../assets/reports/delivers.xls');
		$sheet = $objPHPExcel->getActiveSheet();
		
		$i = 4;
		$total = 0;
		if (isset($ret[0])) {
			$d = $ret[0]->cells;
			$sheet->setCellValue("B2", $d['JID']->caption);
		}
		if ($stock) {
			$sheet->setCellValue("G2", $stock['Counts']);
		}
		$sheet->setCellValue("I2", substr($data['CreateTime'], 0, 10));
		foreach($ret as $r) {
			$d = $r->cells;
			$sheet->mergeCells("A{$i}:F{$i}");
			$sheet->setCellValue("A{$i}", $d['CID']->caption);
			$sheet->getStyle("A{$i}")->getFont()->setSize(12);
			$sheet->setCellValue("G{$i}", $d['Year']->value);
			$sheet->setCellValue("H{$i}", $d['No']->value);
			$sheet->setCellValue("I{$i}", $d['Counts']->value);
			$sheet->getRowDimension($i)->setRowHeight(25);
			$total += $d['Counts']->value;
			$i++;
		}
		
		$sheet->mergeCells("A{$i}:H{$i}");
		$sheet->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->setCellValue("A{$i}", "小计");
		$sheet->setCellValue("I{$i}", $total);
		$sheet->getRowDimension($i)->setRowHeight(35);
		$sheet->getStyle("A4:I{$i}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment;filename="FH'.$ID.'.pdf"');
		$objWriter->save('php://output');
		
	}
	
	/**
	 * 打印客户取刊单
	 */
	function delivery_bill()
	{
		ini_set('max_execution_time', 0);
		$CID = (int)$this->input->post_get("CID");
		$BatchIDs = explode(',', $this->input->post_get("BatchIDs"));
		foreach($BatchIDs as &$bid) {
			$bid = (int)$bid;
		}
		$BatchIDs = implode(',', $BatchIDs);
		$this->load->library('excel');

		$this->load->model('grid_model');
		
		$ret = $this->db->get_where('customers', array('id' => (int) $CID))->result_array();
		
		if (!isset($ret[0])) {
			die();
		}
		$customer = $ret[0];
//		print_r($customer);
		
//		$this->grid_model->table('DeliveryHistoryView');
		$this->grid_model->table('deliverydetails');
		$this->grid_model->prepare(true, false);
		$paras = new stdClass();
		$paras->search = true;
		$paras->filters = (object) array(
			"groupOp" => "AND",
			"rules" => array(
				(object) array(
					"data" => $CID,
					"op" => "eq",
					"field" => "CID"
				),
				(object) array(
					"data" => $BatchIDs,
					"op" => "in",
					"field" => "BatchID"
				)
			)
		);
		$ret = $this->grid_model->wrapper_sheet($paras);
		$ret = $this->grid_model->sheet_to_grid($ret->data, false, true);
//		echo "<pre>";
//		print_r($ret);
//		echo "</pre>";
		$objPHPExcel = PHPExcel_IOFactory::load(BASEPATH.'../assets/reports/deliverybill.xls');
		$sheet = $objPHPExcel->getActiveSheet();
		
//		if (isset($ret[0])) {
//			$d = $ret[0]->cells;
//			$sheet->setCellValue("B2", $d['JID']->caption);
//		}
		$sheet->setCellValue("A1", $customer['ReceivePost']);
		$sheet->setCellValue("A2", $customer['ReceiveAddress']);
		$sheet->setCellValue("A3", $customer['Receiver']);
		$sheet->setCellValue("A4", "{$customer['ContactMobile']} / {$customer['ContactPhone']}");
		
		$sheet->setCellValue("B9", $customer['Name']);
		$sheet->setCellValue("F9", date('Y-m-d'));
		
		$i = 11;
		$total = 0;
		$total2 = 0;
		foreach($ret as $r) {
			$d = $r->cells;
			$sheet->setCellValue("A{$i}", $i - 10);
			$sheet->setCellValue("B{$i}", $d['BatchID']->value);
			$sheet->setCellValue("C{$i}", $d['JID']->caption);
//			$sheet->mergeCells("A{$i}:F{$i}");
//			$sheet->setCellValue("A{$i}", $d['CID']->caption);
//			$sheet->getStyle("A{$i}")->getFont()->setSize(9);
			$sheet->setCellValue("D{$i}", $d['Year']->value);
			$sheet->setCellValue("E{$i}", $d['No']->value);
			$sheet->setCellValue("F{$i}", $d['Counts']->value);
			$sheet->setCellValue("G{$i}", $d['daiFa']->value);
			$sheet->getRowDimension($i)->setRowHeight(30);
			$total += $d['Counts']->value;
			$total2 += $d['daiFa']->value;
			$this->db->update('deliverydetails', array('isPrint'=>1), array('id'=>$r->id));
			$i++;
		}
		
		$sheet->mergeCells("A{$i}:E{$i}");
//		$sheet->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->setCellValue("A{$i}", "合计");
		$sheet->setCellValue("F{$i}", $total);
//		$sheet->setCellValue("G{$i}", $total2);
		$sheet->getRowDimension($i)->setRowHeight(40);
		$sheet->getStyle("A11:G{$i}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment;filename="DB'.$CID.'.pdf"');
		$objWriter->save('php://output');
	}
	
	function a()
	{
		ini_set('max_execution_time', 0);
		$this->load->library('excel');
		$this->load->model('grid_model');
		
		$this->grid_model->table('publishnotify', array(
		"JID", "No", "Price", "PublishCounts"
		));
		$this->grid_model->prepare(true, false);
		
		$sql = <<<EOF
	select
		a.*,
		c.Name as JName2,
		GROUP_CONCAT(b.DeliverTarget) as DeliverTarget, 
		GROUP_CONCAT(b.DeliverCount) as DeliverCount
	from
		`qkzx_publishnotifydeliver` b, 
		`qkzx_publishnotify` a left join `qkzx_journalbaseinfo` c on a.JID = c.id
		
	where
		a.id=b.PNID
	group by
		a.id;
EOF;
//		$this->db->reset();
		$this->db->select("aa.JID");
		$this->db->select("aa.NoStart");
		$this->db->select("SUM(aa.OrderCount) as Counts");
		$this->db->from("journalorders aa");
		$this->db->where("aa.SaleStyle", 8);
		$this->db->group_by("aa.JID");
		$this->db->group_by("aa.NoStart");
		
		$this->grid_model->from("publishnotifydeliver b");
		$this->grid_model->select("a2.NofPerYear as NofPerYear");
		$this->grid_model->select("GROUP_CONCAT(b.DeliverTarget) as DeliverTarget");
		$this->grid_model->select("GROUP_CONCAT(b.DeliverCount) as DeliverCount");
		$this->grid_model->select("c.Counts AS StockCounts");
		$this->grid_model->select("d.Counts AS GiftCounts");
		$this->grid_model->join("journalstockmanage c", "a.JID=c.JID and a.No=c.No", 'LEFT');
		$this->grid_model->join("(" . $this->db->get_compiled_select() . ") d", "a.JID=d.JID and a.No=d.NoStart", 'LEFT');
		$this->grid_model->where("a.id=b.PNID");
		$this->grid_model->group_by("a.id");
		$this->grid_model->order_by("a.JID,a.No", "ASC");
//		$paras = new stdClass();
//		$paras->page = -1;
//		$paras->size = -1;
//		$paras->search = false;
//		$ret = $this->grid_model->wrapper_sheet($paras, true);
//		$publishnotifys = $ret->data;
		$publishnotifys = $this->grid_model->sheet();
//		print_r($this->grid_model->db3->queries);
//		die();
//		$publishnotifys = $this->publishnotify->sheet_to_grid($ret->data, false, true);
		
		
		
//		$this->db->from("publishnotifydeliver a");
//		$this->db->from("publishnotify b");
//		$this->db->from("journalbaseinfo c");
//		$this->db->select("b.*");
//		$this->db->select("GROUP_CONCAT(a.DeliverTarget) as DeliverTarget");
//		$this->db->select("GROUP_CONCAT(a.DeliverCount) as DeliverCount");
//		$this->db->select("c.Name as JName2");
//		
//		$this->db->where("b.id=a.PNID");
//		$this->db->where("b.JID=c.id");
//		$this->db->group_by("b.id");
//		$this->db->order_by("b.JID,b.No", 'ASC');
//		$data = $this->db->sheet();
	
//		print_r($publishnotifys);
		$objPHPExcel = PHPExcel_IOFactory::load(BASEPATH.'../assets/reports/report1.xls');
		$sheet = $objPHPExcel->getActiveSheet();
		$i = 3;
		//序号	刊名	刊期	期数	单价/元	总印数	造货码洋	编辑部	编辑部码洋	邮局本市	邮局外埠	邮局小计	送社	自办销售	赠阅	库存	损耗	发货数量	发货码洋	销售数量	销售码洋	销售实洋
		//A		B		C		D		E		F		G			H		I			J			K			L			M		N			O		P		Q		R			S			T			U			V
		foreach($publishnotifys as $k=>$d) {
//			$sheet->setCellValue("A{$i}", "");
			$sheet->setCellValue("B{$i}", $d['r2']);
			$sheet->setCellValue("C{$i}", $d['NofPerYear']);
			$sheet->setCellValue("D{$i}", $d['No']);
			$sheet->setCellValue("E{$i}", $d['Price']);
			$sheet->setCellValue("F{$i}", $d['PublishCounts']);
			$sheet->setCellValue("G{$i}", "=E{$i}*F{$i}");

			$DeliverTarget = explode(',', $d['DeliverTarget']);
			$DeliverCount = explode(',', $d['DeliverCount']);
			$deliver = array();
			foreach($DeliverTarget as $m=>$n) {
				$deliver[$n] = $DeliverCount[$m];
			}
////			print_r($deliver);
			if (!isset($deliver['本社'])) {
				$deliver['本社'] = 0;
			}
			if (!isset($deliver['编辑部'])) {
				$deliver['编辑部'] = 0;
			}
			if (!isset($deliver['邮局本市'])) {
				$deliver['邮局本市'] = 0;
			}
			if (!isset($deliver['邮局本市东'])) {
				$deliver['邮局本市东'] = 0;
			}
			if (!isset($deliver['邮局本市西'])) {
				$deliver['邮局本市西'] = 0;
			}
			if (!isset($deliver['邮局外埠'])) {
				$deliver['邮局外埠'] = 0;
			}
			if (!$d['GiftCounts']){
				$d['GiftCounts'] = 0;
			}
			if (!$d['StockCounts']) {
				$d['StockCounts'] = 0;
			}
			$sheet->setCellValue("H{$i}", $deliver['编辑部']);
			$sheet->setCellValue("I{$i}", "=E{$i}*H{$i}");
			$sheet->setCellValue("J{$i}", $deliver['邮局本市'] + $deliver['邮局本市东'] + $deliver['邮局本市西']);
			$sheet->setCellValue("K{$i}", $deliver['邮局外埠']);
			$sheet->setCellValue("L{$i}", $deliver['邮局本市'] + $deliver['邮局本市东'] + $deliver['邮局本市西'] + $deliver['邮局外埠']);
			$sheet->setCellValue("M{$i}", "=F{$i}-H{$i}-L{$i}");
			$sheet->setCellValue("N{$i}", 0);
			$sheet->setCellValue("O{$i}", $d['GiftCounts']);
			$sheet->setCellValue("P{$i}", $d['StockCounts']);
			$sheet->setCellValue("Q{$i}", 0);
			$sheet->setCellValue("R{$i}", "=H{$i}+L{$i}+N{$i}+O{$i}+Q{$i}");
			$sheet->setCellValue("S{$i}", "=R{$i}*E{$i}");
			$sheet->setCellValue("T{$i}", "=L{$i}+N{$i}+O{$i}+Q{$i}");
			$sheet->setCellValue("U{$i}", "=T{$i}*E{$i}");
			$sheet->setCellValue("V{$i}", "=(L{$i}-10)*0.62*E{$i}+N{$i}*0.71*E{$i}");
			$i++;
		}
		$tail = $i - 1;
		$sheet->setCellValue("B{$i}", "合计");
		$sheet->setCellValue("G{$i}", "=SUM(G3:G{$tail})");
		$sheet->setCellValue("R{$i}", "=SUM(R3:R{$tail})");
		$sheet->setCellValue("S{$i}", "=SUM(S3:S{$tail})");
		$sheet->setCellValue("T{$i}", "=SUM(T3:T{$tail})");
		$sheet->setCellValue("U{$i}", "=SUM(U3:U{$tail})");
		$sheet->setCellValue("V{$i}", "=SUM(V3:V{$tail})");
		$sheet->getStyle("A3:V{$i}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		$filename = "年过程类期刊统计表.xls";
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	private function sales_stats_jid_no($JID, $No)
	{
		$JID = (int)$JID;
		$No = (int)$No;
		$sales = array(
			'JID' => $JID,
			'No' => $No,
			'Price' => 0,
			'PublishCounts' => 0,
			'EditOrderCount' => 0,
			'PostInCity' => 0,
			'PostOutCity' => 0,
			'ToPress' => 0,
			'Gift' => 0
		);
		
		//
		$this->db->select('id,Name,NofPerYear,Price,SaleDiscount');
		$result1 = $this->db->get_where('journalbaseinfo', array('id'=> $JID))->row_array();
		if ($result1) {
			$sales['NoPerYear'] = $result1['NofPerYear'];								//刊期
			$sales['Price'] = $result1['Price'];										//单价
		}
		
		//总印数
		$this->db->select('id,PublishCounts');
		$result2 = $this->db->get_where('publishnotify', array('JID'=> $JID, 'No'=> $No))->row_array();
		if ($result2) {
			$sales['PublishCounts'] = $result2['PublishCounts'];						//总印数
		}
		$sales['TotalCost'] = $sales['PublishCounts'] * $sales['Price'];				//造货码洋
		
		if ($result2 && 0) {															//有印制单,说明是全过程类期刊
			$delivers = $this->db->get_where('publishnotifydeliver', array('PNID' => $result2['id']))->result_array();
			$deliver = array_column($delivers, 'DeliverCount',  'DeliverTarget');
			if (!isset($deliver['本社'])) {
				$deliver['本社'] = 0;
			}
			if (!isset($deliver['编辑部'])) {
				$deliver['编辑部'] = 0;
			}
			if (!isset($deliver['邮局本市'])) {
				$deliver['邮局本市'] = 0;
			}
			if (!isset($deliver['邮局本市东'])) {
				$deliver['邮局本市东'] = 0;
			}
			if (!isset($deliver['邮局本市西'])) {
				$deliver['邮局本市西'] = 0;
			}
			if (!isset($deliver['邮局外埠'])) {
				$deliver['邮局外埠'] = 0;
			}
//			$sheet->setCellValue("H{$i}", $deliver['编辑部']);							//编辑部
//			$sheet->setCellValue("I{$i}", "=E{$i}*H{$i}");								//编辑部码洋
//			$sheet->setCellValue("J{$i}", $deliver['邮局本市'] 							//邮局本市
//								+ $deliver['邮局本市东'] + $deliver['邮局本市西'] );
//			$sheet->setCellValue("K{$i}", $deliver['邮局外埠']);						//邮局外埠
//			$sheet->setCellValue("L{$i}", "=J{$i}+K{$i}");								//邮局小计
//			$sheet->setCellValue("M{$i}", $deliver['本社']);							//送社
		} else {
			//编辑部订单数
			$this->db->select('ifnull(sum(orderCount), 0) AS EditOrderCount');
			$this->db->where(array('saleStyle' => 5, 'JID' => $JID, 'NoStart' => $No));
			$result3 = $this->db->get('journalorders')->row_array();
			if ($result3){
				$sales['EditOrderCount'] = $result3['EditOrderCount'];					//编辑部
			}
			$sales['EditOrderCost'] = $sales['EditOrderCount'] * $sales['Price'];		//编辑部码洋
			//邮局本市
			$this->db->select('ifnull(sum(orderCount), 0) AS PostInCity');
			$this->db->where(array('JID' => $JID, 'NoStart' => $No));
			$this->db->where_in('saleStyle', array(20,21));
			$result4 = $this->db->get('journalorders')->row_array();
			if ($result4){
				$sales['PostInCity'] = $result4['PostInCity'];							//邮局本市
			}
			
			//邮局外埠
			$this->db->select('ifnull(sum(orderCount), 0) AS PostOutCity');
			$this->db->where(array('saleStyle' => 4, 'JID' => $JID, 'NoStart' => $No));
			$result5 = $this->db->get('journalorders')->row_array();
			if ($result5){
				$sales['PostOutCity'] = $result5['PostOutCity'];						//邮局外埠
			}
			$sales['PostTotal'] = $sales['PostInCity'] * $sales['PostOutCity'];			//邮局小计
			//送社
			$this->db->select('ifnull(sum(orderCount), 0) AS ToPress');
			$this->db->where(array('JID' => $JID, 'NoStart' => $No));
			$this->db->where_in('saleStyle', array(1,2,6,8));
			$this->db->where('IsNeedDeliver', 1);
			$result6 = $this->db->get('journalorders')->row_array();
			if ($result6) {
				$sales['ToPress'] = $result6['ToPress'];								//送社
			}
		}
		
		
		//自办销售
		$this->db->select('ifnull(sum(jo.orderCount), 0) AS SelfSale');
		$this->db->from('journalorders jo');
		$this->db->from('customers c');
		$this->db->where(array('jo.JID' => $JID, 'jo.NoStart' => $No));
		$this->db->where('jo.cid = c.id');
		$this->db->group_start();
		$this->db->group_start();
		$this->db->where(array('c.IsDelivery' => 1, 'c.isAlone' => 0, 'c.Discount<' => 100));
		$this->db->where_not_in('jo.SaleStyle', array(2,5));
		$this->db->group_end();
		$this->db->or_where('jo.SaleStyle', 7);
		$this->db->group_end();
		$result7 = $this->db->get()->row_array();
		if ($result7) {
			$sales['SelfSale'] = $result7['SelfSale'];									//自办销售
		}
		
		//赠刊
		$this->db->select('ifnull(sum(orderCount), 0) AS Gift');
		$this->db->where(array('saleStyle' => 8, 'JID' => $JID, 'NoStart' => $No));
		$result8 = $this->db->get('journalorders')->row_array();
		if ($result8) {
			$sales['Gift'] = $result8['Gift'];											//赠阅
		}
		
		//库存
		$this->db->select('Counts AS StockCount');
		$this->db->where(array('JID' => $JID, 'No' => $No));
		$result9 = $this->db->get('journalstockmanage')->row_array();
		if ($result9) {
			$sales['StockCount'] = $result9['StockCount'];								//库存
		}
		//FIXEME
		$sales['Waste'] = 0;															//损耗 
		
		$sales['DeliverCount'] = $sales['EditOrderCount'] + $sales['PostTotal'] + $sales['SelfSale'] + $sales['Gift'];
																						//发货数量
		$sales['DeliverCountCost'] = $sales['DeliverCount'] * $sales['Price'];			//发货码洋
		$sales['SaleCount'] = $sales['PostTotal'] - 10 + $sales['SelfSale'];			//销售数量
		$sales['SaleCountCost'] = $sales['SaleCount'] * $sales['Price'];				//销售码洋
		$result1['SaleDiscount'] = ((int)$result1['SaleDiscount'] )? $result1['SaleDiscount'] / 100 : 1;
		$sales['SaleDiscount'] = $result1['SaleDiscount'];								//销售折扣
		$sales['RealCost'] = $sales['SaleCountCost'] * $sales['SaleDiscount'];			//销售实洋
		return $sales;
	}
	private function sales_stats_excel($data)
	{
		ini_set('max_execution_time', 0);
		$this->load->library('excel');
		$objPHPExcel = PHPExcel_IOFactory::load(BASEPATH.'../assets/reports/SaleStats.xls');
		$sheet = $objPHPExcel->getActiveSheet();
		$i = 2;
//		print_r($data);
		foreach($data as $d) {
			$JID = (int)$d->JID;
			$No = (int)$d->No;
			$this->db->select('id,Name,NofPerYear,Price,SaleDiscount');
			$result1 = $this->db->get_where('journalbaseinfo', array('id'=> $JID))->row_array();
			if ($result1) {
				$sheet->setCellValue("A{$i}", $i - 1);										//序号
				$sheet->setCellValue("B{$i}", $result1['Name']);							//刊名
				$sheet->setCellValue("C{$i}", $result1['NofPerYear']);						//刊期
				$sheet->setCellValue("D{$i}", $No);											//期数
				$sheet->setCellValue("E{$i}", $result1['Price']);							//单价
			}
			//总印数
			$this->db->select('id,PublishCounts');
			$result2 = $this->db->get_where('publishnotify', array('JID'=> $JID, 'No'=> $No))->row_array();
			if ($result2) {
				$sheet->setCellValue("F{$i}", $result2['PublishCounts']);					//总印数
			}
			$sheet->setCellValue("G{$i}", "=E{$i}*F{$i}");									//造货码洋
			
			if ($result2 && 0) {															//有印制单,说明是全过程类期刊
				$delivers = $this->db->get_where('publishnotifydeliver', array('PNID' => $result2['id']))->result_array();
				$deliver = array_column($delivers, 'DeliverCount',  'DeliverTarget');
				if (!isset($deliver['本社'])) {
					$deliver['本社'] = 0;
				}
				if (!isset($deliver['编辑部'])) {
					$deliver['编辑部'] = 0;
				}
				if (!isset($deliver['邮局本市'])) {
					$deliver['邮局本市'] = 0;
				}
				if (!isset($deliver['邮局本市东'])) {
					$deliver['邮局本市东'] = 0;
				}
				if (!isset($deliver['邮局本市西'])) {
					$deliver['邮局本市西'] = 0;
				}
				if (!isset($deliver['邮局外埠'])) {
					$deliver['邮局外埠'] = 0;
				}
				$sheet->setCellValue("H{$i}", $deliver['编辑部']);							//编辑部
				$sheet->setCellValue("I{$i}", "=E{$i}*H{$i}");								//编辑部码洋
				$sheet->setCellValue("J{$i}", $deliver['邮局本市'] 							//邮局本市
									+ $deliver['邮局本市东'] + $deliver['邮局本市西'] );
				$sheet->setCellValue("K{$i}", $deliver['邮局外埠']);						//邮局外埠
				$sheet->setCellValue("L{$i}", "=J{$i}+K{$i}");								//邮局小计
				$sheet->setCellValue("M{$i}", $deliver['本社']);							//送社
			} else {
				//编辑部订单数
				$this->db->select('ifnull(sum(orderCount), 0) AS EditOrderCount');
				$this->db->where(array('saleStyle' => 5, 'JID' => $JID, 'NoStart' => $No));
				$result3 = $this->db->get('journalorders')->row_array();
				if ($result3){
					$sheet->setCellValue("H{$i}", $result3['EditOrderCount']);				//编辑部
				}
				$sheet->setCellValue("I{$i}", "=E{$i}*H{$i}");								//编辑部码洋
				//邮局本市
				$this->db->select('ifnull(sum(orderCount), 0) AS PostInCity');
				$this->db->where(array('JID' => $JID, 'NoStart' => $No));
				$this->db->where_in('saleStyle', array(20,21));
				$result4 = $this->db->get('journalorders')->row_array();
				if ($result4){
					$sheet->setCellValue("J{$i}", $result4['PostInCity']);					//邮局本市
				}
				
				//邮局外埠
				$this->db->select('ifnull(sum(orderCount), 0) AS PostOutCity');
				$this->db->where(array('saleStyle' => 4, 'JID' => $JID, 'NoStart' => $No));
				$result5 = $this->db->get('journalorders')->row_array();
				if ($result5){
					$sheet->setCellValue("K{$i}", $result5['PostOutCity']);					//邮局外埠
				}
				$sheet->setCellValue("L{$i}", "=J{$i}+K{$i}");								//邮局小计
				//送社
				$this->db->select('ifnull(sum(orderCount), 0) AS ToPress');
				$this->db->where(array('JID' => $JID, 'NoStart' => $No));
				$this->db->where_in('saleStyle', array(1,2,6,8));
				$this->db->where('IsNeedDeliver', 1);
				$result6 = $this->db->get('journalorders')->row_array();
				if ($result6) {
					$sheet->setCellValue("M{$i}", $result6['ToPress']);						//送社
				}
			}
			
			
			//自办销售
			$this->db->select('ifnull(sum(jo.orderCount), 0) AS SelfSale');
			$this->db->from('journalorders jo');
			$this->db->from('customers c');
			$this->db->where(array('jo.JID' => $JID, 'jo.NoStart' => $No));
			$this->db->where('jo.cid = c.id');
			$this->db->group_start();
			$this->db->group_start();
			$this->db->where(array('c.IsDelivery' => 1, 'c.isAlone' => 0, 'c.Discount<' => 100));
			$this->db->where_not_in('jo.SaleStyle', array(2,5));
			$this->db->group_end();
			$this->db->or_where('jo.SaleStyle', 7);
			$this->db->group_end();
			$result7 = $this->db->get()->row_array();
			if ($result7) {
				$sheet->setCellValue("N{$i}", $result7['SelfSale']);						//自办销售
			}
			
			//赠刊
			$this->db->select('ifnull(sum(orderCount), 0) AS Gift');
			$this->db->where(array('saleStyle' => 8, 'JID' => $JID, 'NoStart' => $No));
			$result8 = $this->db->get('journalorders')->row_array();
			if ($result8) {
				$sheet->setCellValue("O{$i}", $result8['Gift']);							//赠阅
			}
			
			//库存
			$this->db->select('Counts AS StockCount');
			$this->db->where(array('JID' => $JID, 'No' => $No));
			$result9 = $this->db->get('journalstockmanage')->row_array();
			if ($result9) {
				$sheet->setCellValue("P{$i}", $result9['StockCount']);						//库存
			}
			$sheet->setCellValue("Q{$i}", "=IF(ISNUMBER(F{$i}),F{$i}-R{$i}-P{$i},0)");				//损耗
			
			$sheet->setCellValue("R{$i}", "=H{$i}+L{$i}+N{$i}+O{$i}");						//发货数量
			$sheet->setCellValue("S{$i}", "=E{$i}*R{$i}");									//发货码洋
			$sheet->setCellValue("T{$i}", "=(L{$i}-10)+N{$i}");								//销售数量
			$sheet->setCellValue("U{$i}", "=T{$i}*E{$i}");									//销售码洋
			$result1['SaleDiscount'] = ((int)$result1['SaleDiscount'] )? $result1['SaleDiscount'] / 100 : 1;
			$sheet->setCellValue("V{$i}", "{$result1['SaleDiscount']}");					//销售折扣
			$sheet->setCellValue("W{$i}", "=U{$i}*V{$i}");									//销售实洋
//			break;
			$i++;
		}
		$i--;
		$sheet->getStyle("A2:W{$i}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		$filename = "销售月报表.xls";
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	/**
	 * 销售月报表
	 */
	public function sales_stats()
	{
		$data = json_decode($this->input->post_get("data"));
		$this->sales_stats_excel($data);
	}
	
	public function sales_all_stats()
	{
		$this->load->model('grid_model');
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		$paras->page = 1;
		$paras->size = 500;
		$paras->sidx = 'No';
		$paras->sord = 'asc';
		$export = array();
		$ret = $this->grid_model->table('DeliveryTotalForSaleStat');
		if ($ret) {
			if ($this->grid_model->prepare(false)) {
				$count = 0;
				$total = 0;
				while ($count == 0 || $count < $total) {
					$data = $this->grid_model->wrapper_sheet($paras);
					$this->grid_model->pop_cache();
					if (!count($data->data)) {
						break;
					}
					foreach($data->data as $d) {
						array_push($export, (object) $d);
					}
//					$export = array_merge($export, $data->data);
					$total = $data->count;
					$paras->page++;
					$count += count($data->data);
				}
				$this->sales_stats_excel($export);
			}
		}

	/*
		$ret = $this->grid_model->table($table);
		if ($ret) {
			if ($this->grid_model->prepare()) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				
				$row = array();
				if ($paras->key) {
					$row[] = $this->grid_model->crud_field[$this->grid_model->primary]['caption'];
				}

				foreach($setting as $s){
					if (isset($this->grid_model->crud_field[$s[0]]) && $this->grid_model->crud_field[$s[0]]['_role_r']) {
						
						$fields[$s[0]] = $s[1]?1:0;
						if ($s[1]) {
							$row[] = $this->grid_model->crud_field[$s[0]]['caption'];
						}
					}
				}
				$sheet[] = $row;
				$this->excel->getActiveSheet()->fromArray($sheet, NULL, "A1");
				$line = 2;
				$count = 0;
				$total = 0;
				
				while ($count == 0 || $count < $total) {
					unset($sheet);
					$sheet = array();
					
					
					$data = $this->grid_model->wrapper_sheet($paras);
					$this->grid_model->pop_cache();
					if (!count($data->data)) {
						break;
					}
					$total = $data->count;
					$paras->page++;
					foreach($data->data as $d) {
						unset($row);
						$row = array();
						if ($paras->key) {
							$row[] = $d[$this->grid_model->primary];
						}
						foreach($fields as $k=>$f) {
							if ($f) {
								if ($paras->raw) {
									$row[] = $d[$k];
								} elseif (isset($this->grid_model->crud_field[$k]['_caption'])) {
									$this->grid_model->wrapper_caption($this->grid_model->crud_field[$k], $d);
									$row[] = $d[$this->grid_model->crud_field[$k]['_caption']];
								} else {
									$row[] = $d[$k];
								}
							}
						}
						$sheet[] = $row;
					}

					$this->excel->getActiveSheet()->fromArray($sheet, NULL, "A{$line}");
					$count += count($data->data);
					$line += count($data->data);
				}
	 */
	
	
	}
}
