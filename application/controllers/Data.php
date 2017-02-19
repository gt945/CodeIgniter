<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model ( 'auth_model' );
		
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
// 		ini_set('memory_limit','512M');
		
		$this->load->model('grid_model');
		$table = $this->input->post_get("key");
		$paras_json = $this->input->post_get("paras");
		$paras = json_decode($paras_json);
		
		$setting = $paras->setting;
		$paras->page = 1;
		$paras->size = 500;
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
			die();
		}
		$data = $ret[0]->cells;
//		print_r($data);
		$JID = $data['JID']->value;
		$Year = $data['Year']->value;
		$No = $data['No']->value;
		$total = 0;
		if ($JID && $Year && $No) {
			$sql = <<<EOF
select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle = 5 and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) a
union
select '邮局外埠' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle = 4 and jyear ={$Year}  and nostart <= {$No} and noend >={$No}) b
union
select '邮局本市' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount  from `qkzx_journalorders`  where JID = {$JID} and saleStyle = 3 and jyear ={$Year} and nostart <= {$No} and noend >= {$No}) c
union
SELECT '邮局本市东' AS orderUnit, orderCount FROM (SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} AND saleStyle =20 AND Jyear ={$Year}  AND nostart = {$No}) e
union
SELECT '邮局本市西' AS orderUnit, orderCount FROM(SELECT IfNULL(SUM(orderCount),0) AS orderCount FROM `qkzx_journalorders` WHERE JID = {$JID} AND saleStyle = 21 AND Jyear ={$Year} AND nostart ={$No} ) f
union
select '本社' as orderUnit, orderCount from (select IfNULL(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = {$JID} and saleStyle in(1,2,6,8) and jyear = {$Year} and nostart <= {$No} and noend >= {$No}) d

EOF;
//		$sql = "select '编辑部' as orderUnit, orderCount from (select ifnull(sum(orderCount),0) as orderCount from `qkzx_journalorders` where JID = ? and saleStyle = 5 and jyear =? and nostart <= ? and noend >=?) a";
			$counts = $this->db->query($sql)->result();
			foreach($counts as $c) {
				$total += $c->orderCount;
			}

		} else {
			$counts = null;
		}

//		print_r($counts);

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
		$sheet->setCellValue("K2", date('Y-m-d'));	//打印日期
		$sheet->setCellValue("B3", "{$data['JID']->caption}\n\t({$data['Year']->value}年第{$data['No']->value}期)");  //刊名
		$sheet->setCellValue("I3", "{$total} 册");  //印数
		$sheet->setCellValue("I4", "{$data['Price']->value} 元");   //定价
		$sheet->setCellValue("A6", $data['KaiId']->caption);  //开本
		$sheet->setCellValue("B6", $data['SizeId']->caption);	//开本尺寸
		$sheet->setCellValue("C6", $data['DingKou']->value);	//订口
		$sheet->setCellValue("D6", $data['QieKou']->value);	//切口
		$sheet->setCellValue("E6", $data['TianTou']->value);	//天头
		$sheet->setCellValue("F6", $data['DiJiao']->value);   //地脚
		$sheet->setCellValue("G6", $data['FanShen']->value);	//翻身
		$sheet->setCellValue("I5", $data['BindingMethod']->value);   //装订方法

		$i = 9;
		foreach($details as $detail) {
			$d = $detail->cells;
			$sheet->setCellValue("A{$i}", $d['PublishContent']->caption);   //印刷内容
			$sheet->setCellValue("B{$i}", $d['Pages']->value);	//页码
			$sheet->setCellValue("C{$i}", "");		//页数
			$sheet->setCellValue("D{$i}", $d['PublishCount']->value);	   //印数
			$sheet->setCellValue("E{$i}", $d['paperDeduceID']->caption);   //纸名
			$sheet->setCellValue("G{$i}", $d['SizeId']->value); //规格
			$sheet->setCellValue("H{$i}", $d['KaiShu']->value);		//开数
			$sheet->setCellValue("I{$i}", $d['PaperCount']->value);		//应用纸数
			$sheet->setCellValue("J{$i}", $d['ZoomPercent']->value);		//加放率
			$sheet->setCellValue("K{$i}", $d['ZoomPaperCount']->value);		//加放纸数
			$sheet->setCellValue("L{$i}", $d['TotalPaper']->value);		//共计用纸数
			$i++;
		}

		$sheet->setCellValue("B15", $data['coverInk']->value);	   //封面墨色
		$sheet->setCellValue("I15", $data['textInk']->value);	   //正文墨色
//		$sheet->setCellValue("B16", $data['BindingOrder']->value);	   //装订顺序
//		$sheet->setCellValue("A17", $data['Note']->value);	   //备注
		$sheet->setCellValue("B16", wordwrap($data['BindingOrder']->value, 125));	   //装订顺序
		$sheet->setCellValue("A17", wordwrap($data['Note']->value, 125));	   //备注
		if (is_array($counts) && count($counts) == 6) {
			$sheet->setCellValue("K17", $counts[5]->orderCount);	  //本社
			$sheet->setCellValue("K18", $counts[0]->orderCount);	  //编辑部
			$sheet->setCellValue("K19", $counts[2]->orderCount);	  //邮局本市
			$sheet->setCellValue("K20", $counts[3]->orderCount);	  //邮局本市东
			$sheet->setCellValue("K21", $counts[4]->orderCount);	  //邮局本市西
			$sheet->setCellValue("K22", $counts[1]->orderCount);	  //邮局外埠
		}
		$sheet->setCellValue("K23", $total);	  //合计
		$sheet->setCellValue("K24", $data['AID']->caption);	  //开单


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		header('Content-Type: application/pdf'); //mime type
//		header('Content-Disposition: attachment;filename="1.pdf"'); //tell browser what's the file name
//		header('Cache-Control: max-age=0'); //no cache
////		$objWriter->SetFont('droidsansfallback');
////		$objWriter->setCellPaddings(10,10,10,10);
		$objWriter->save('php://output');

	}
	public function delivers($ID = 0)
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
		$sheet->setCellValue("I2", substr($data['CreateTime'], 0, 10));
		foreach($ret as $r) {
			$d = $r->cells;
			$sheet->mergeCells("A{$i}:F{$i}");
			$sheet->setCellValue("A{$i}", $d['CID']->caption);
			$sheet->getStyle("A{$i}")->getFont()->setSize(9);
			$sheet->setCellValue("G{$i}", $d['Year']->value);
			$sheet->setCellValue("H{$i}", $d['No']->value);
			$sheet->setCellValue("I{$i}", $d['Counts']->value);
			$sheet->getRowDimension($i)->setRowHeight(18);
			$total += $d['Counts']->value;
			$i++;
		}
		
		$sheet->mergeCells("A{$i}:H{$i}");
		$sheet->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->setCellValue("A{$i}", "小计");
		$sheet->setCellValue("I{$i}", $total);
		$sheet->getRowDimension($i)->setRowHeight(18);
		$sheet->getStyle("A4:I{$i}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
		header('Content-Type: application/pdf');
		$objWriter->save('php://output');
		
	}

}
