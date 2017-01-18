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
		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
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
		
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', 14, '', true);
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();
		
		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		
		// Set some content to print
		$html = <<<EOD
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
EOD;

		// Print text using writeHTMLCell()
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		
		// ---------------------------------------------------------
		
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		$pdf->Output('example_001.pdf', 'I');
	}
}
