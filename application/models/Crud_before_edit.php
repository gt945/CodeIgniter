<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH.'models/Crud_hook.php');

class Crud_before_edit extends Crud_hook {


	public function __construct()
	{
		parent::__construct ();
	}

	function array_merge_by_primary($primary, &$data, $old, $field=array())
	{
		if (count($field)) {

			foreach($data as &$d) {
				foreach($field as $f) {
					if (!isset($d[$f])) {
						foreach($old as $o) {
							if($o[$primary] == $d[$primary]) {
								$d[$f] = $o[$f];
								break;
							}
						}
					}

				}
			}
		}
	}
	
	function user_edit($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d = &$data[0];
			$d['create_date'] = date('Y-m-j H:i:s');
		}
		foreach ($data as &$d) {
			if (isset($d['password'])) {
				$d['password'] = md5($d['password']);
			}
		}

		return $this->result(true);
	}
	
	function group_edit($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			
		} else {
		}
	}
	
	
	function journalorders_edit($oper, $model, &$data, $old)
	{

		if ($oper == 'create') {
			$d = &$data[0];
			$NoStart = (int)$d['NoStart'];
			$NoEnd = (int)$d['NoEnd'];
			$OrderCount = (int)$d['OrderCount'];
			$journalbaseinfo = $this->db->get_where('journalbaseinfo', array('id' => $d['JID']))->row_array();
			if (!$journalbaseinfo) {
				return $this->result(false, "不存在该期刊");
			}
			if($NoStart <= 0 || $NoStart > $journalbaseinfo['NofPerYear'] || $NoEnd < $NoStart || $NoEnd > $journalbaseinfo['NofPerYear']) {
				return $this->result(false, "期次错误");
			}
			if ($OrderCount <= 0) {
				return $this->result(false, "订阅数量错误");
			}
			$customer = $this->db->get_where('customers', array('id' => $d['CID']))->row_array();
			if (!$customer) {
				return $this->result(false, "不存在该客户");
			}
			$d['Year'] = $d['Jyear'];
			$this->load->model('JournalStockManage');
			if ($d['OrderType'] == 1) {																/* 收订 - 预留库存 */
				if($customer['CType'] == 2) {														/* 收订 - 预留库存 */
					$d['SaleStyle'] = 2;
					$d['ReportStatus'] = 0;
					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						$data[] = $d;
					}
				} else if($customer['CType'] == 9) {												/* 收订 - 补刊 */
					$d['SaleStyle'] = 9;
					$d['ReportStatus'] = 1;

					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						
						$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
						$stockcount = $this->JournalStockManage->stock_count();
						if ($stockcount < $OrderCount) {											/* 收订 - 补刊 - 库存不够*/
							//TODO 库存不够
							//Do Nothing
						} else {																	/* 收订 - 补刊 - 减库存并记录*/
							$this->JournalStockManage->stock_out($OrderCount, 11, $d['CID']);
							$data[] = $d;
						}
					}

				} else {																			/* 收订 - 一般客户和其他客户 */
					if($customer['CType'] != 1) {													/* 收订 - 一般客户 */
						$d['SaleStyle'] = $customer['CType'];
					} else {
						$d['SaleStyle'] = 1;
					}

					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						
						if ($journalbaseinfo['Classify'] == 1) {									/* 收订 - 其他客户 - 代理类期刊*/

							$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
							$stockcount = $this->JournalStockManage->stock_count();
							if($stockcount === null) {												/* 收订 - 其他客户 - 代理类期刊 - 无库存*/
								$d['ReportStatus'] = 0;
							} else if ($stockcount < $OrderCount) {									/* 收订 - 其他客户 - 代理类期刊 - 库存不够*/
								//TODO 库存不够
								continue;
							} else {																/* 收订 - 其他客户 - 代理类期刊 - 减库存并记录*/
								$d['ReportStatus'] = 1;
								$d['SaleStyle'] = 7;
								$this->JournalStockManage->stock_out($OrderCount, 10, $d['CID']);
							}
						} else {																	/* 收订 - 其他客户 - 非代理类期刊*/
							$this->load->model('ReportCounts');
							$this->ReportCounts->prepare($d['JID'], $d['Jyear'], $i);
							$reportcounts = $this->ReportCounts->report_count();
							if ($reportcounts !== null) {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录*/

								$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
								$stockcount = $this->JournalStockManage->stock_count();
								if($stockcount === null) {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存*/
									$this->db->from('publishnotify');
									$this->db->where('Year', $d['Jyear']);
									$this->db->where('JID', $d['JID']);
									$this->db->where('No', $i);
									$publishnotify = $this->db->row();
									if($publishnotify) {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单*/
										$this->db->from('journalorders');
										$this->db->where('Jyear', $d['Jyear']);
										$this->db->where('JID', $d['JID']);
										$this->db->where('NoStart', $i);
										$this->db->where('NoEnd', $i);
										$this->db->where('SaleStyle', 2);
										$reserved = $this->db->row();
										if ($reserved) {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存*/
											if ($reserved['OrderCount'] < $OrderCount) {			/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 预留库存不够*/
												//TODO 预留库存不够
												continue;
											} else {												/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 减预留库存*/
												$reserved['OrderCount'] -= $OrderCount;
												$save = array(
													'OrderCount' => $reserved['OrderCount']
												);
												$this->db->where('id', $reserved['id']);
												$this->db->update('journalorders', $save);
											}
										} else {													/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 无预留库存*/
											//TODO 无预留库存
											continue;
										}
									} else {														/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 无印制单*/
										$d['ReportStatus'] = 1;
										$this->ReportCounts->report_in($OrderCount);
									}
								} else if ($stockcount < $OrderCount) {								/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 库存不够*/
									//TODO 库存不够
									continue;
								} else {															/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 减库存并记录*/
									$d['ReportStatus'] = 1;
									$d['SaleStyle'] = 7;
									$this->JournalStockManage->stock_out($OrderCount, 10, $d['CID']);
								}
							} else {																/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录*/
								$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
								$stockcount = $this->JournalStockManage->stock_count();
								if($stockcount === null) {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 无库存*/
									$d['ReportStatus'] = 0;
								} else if ($stockcount < $OrderCount) {								/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 库存不够*/
									//TODO 库存不够
									continue;
								} else {															/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 减库存并记录*/
									$d['ReportStatus'] = 1;
									$d['SaleStyle'] = 7;
									$this->JournalStockManage->stock_out($OrderCount, 10, $d['CID']);
								}
							}
						}

						$data[] = $d;
					}
					
					
				}
			} else if ($d['OrderType'] == 2) {														/* 退订*/

				unset($data[0]);
				for ($i = $NoStart; $i <= $NoEnd; $i++) {
					$this->db->from('journalorders');
					$this->db->where('Year', $d['Jyear']);
					$this->db->where('JID', $d['JID']);
					$this->db->where('CID', $d['CID']);
					$this->db->where('NoStart', $i);
					$this->db->where('NoEnd', $i);
					$journalorders = $this->db->sheet();
					$totalcount = 0;
					foreach($journalorders as $order) {												/* 退订 - 计算订单总数*/
						$totalcount += $order['OrderCount'];
					}

					if ($OrderCount > $totalcount) {												/* 退订 - 退订数量大于订单总数*/
						return $this->result(false, "退订数量超过订阅数量");
					} else {																		/* 退订 - 退订数量小于等于订单总数*/
						if ($journalbaseinfo['Classify'] == 1) {									/* 退订 - 退订数量小于等于订单总数 - 代理类期刊*/
							$this->db->from('deliverydetails');
							$this->db->where('Year', $d['Jyear']);
							$this->db->where('JID', $d['JID']);
							$this->db->where('No', $i);
							$this->db->where('CID', $d['CID']);
							$deliverydetails = $this->db->row();
							if ($deliverydetails) {													/* 退订 - 退订数量小于等于订单总数 - 代理类期刊 - 已发货*/
								$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
								$this->JournalStockManage->stock_in($OrderCount, 1);
							} else {																/* 退订 - 退订数量小于等于订单总数 - 代理类期刊 - 未发货*/
								// Do Nothing
							}
						} else {																	/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊*/
							$this->db->from('publishnotify');
							$this->db->where('Year', $d['Jyear']);
							$this->db->where('JID', $d['JID']);
							$this->db->where('No', $i);
							$publishnotify = $this->db->row();
							if ($publishnotify) {													/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊 - 有印制单*/
								$this->db->from('deliverydetails');
								$this->db->where('Year', $d['Jyear']);
								$this->db->where('JID', $d['JID']);
								$this->db->where('No', $i);
								$this->db->where('CID', $d['CID']);
								$deliverydetails = $this->db->row();
								//FIXME
								if ($deliverydetails) {												/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊 - 有印制单 - 已发货*/
									$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
									$this->JournalStockManage->stock_in($OrderCount, 1);
								} else {															/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊 - 有印制单 - 未发货*/
									if($customer['CType'] == 9 || $customer['CType'] == 7) {		/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊 - 有印制单 - 未发货 - 补刊/ */
										$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
										$this->JournalStockManage->stock_in($OrderCount, 1);
									}
								}
							} else {																/* 退订 - 退订数量小于等于订单总数 - 非代理类期刊 - 无印制单*/
								// Do Nothing
							}
						}
					}
					$td = $d;
					$td['OrderCount'] = -$OrderCount;
					$td['NoStart'] = $i;
					$td['NoEnd'] = $i;
					$td['ReportStatus'] = 0;
					$td['SaleStyle'] = 1;
					$data[] = $td;
				}
			}

		} else {
			$field = array(
				'JID', 'NoStart', 'NoEnd', 'OrderCount', 'CostDiscount', 'SaleDiscount', 'TotalPrice', 'SalesTotal', 'CostTotal', 'ReportStatus'
			);

			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			foreach($data as &$d) {
				if ($d['ReportStatus'] == 1) {
					return $this->result(false, "已报数，不可修改");
				}
			}
		}

		foreach($data as &$d) {
			$NoStart = (int)$d['NoStart'];
			$NoEnd = (int)$d['NoEnd'];
			$OrderCount = (int)$d['OrderCount'];
			$CostDiscount = (int)$d['CostDiscount'];
			$SaleDiscount = (int)$d['SaleDiscount'];
			$this->db->where('id', $d['JID']);
			$this->db->from('journalbaseinfo');
			$Price = (float)$this->db->cell('Price');
			if ($NoEnd >= $NoStart) {
				$Count = ($NoEnd - $NoStart + 1) * $OrderCount;
				$d['TotalPrice'] = $Count * $Price;
				$d['SalesTotal'] = $d['TotalPrice'] * $SaleDiscount / 100;
				$d['CostTotal'] = $d['TotalPrice'] * $CostDiscount / 100;
			}
		}
		return $this->result(true);
	}
	
	public function reportcounts_edit ($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d=&$data[0];
			$d['BatchID'] = sprintf("%s%04d",date('Ymj'), $_SESSION['userinfo']['id']);
			$this->load->model('ReportCounts');
			$this->load->model('JournalBaseInfo');
			$journal = $this->JournalBaseInfo->prepare($d['JID']);
			if (!$journal) {
				return $this->result(false, "无此期刊");
			}
			$existreport = $this->ReportCounts->prepare($d['JID'], $d['Year'], $d['No']);
			if ($existreport) {
				return $this->result(false, "该期次已有报数");
			} 
			$save = array (
				'ReportBatchID' => $d['BatchID'],
				'ReportStatus'	=> 1
			);
			
			$this->db->where('Jyear', $d['Year']);
			$this->db->where('JID', $d['JID']);
			$this->db->where('NoStart', $d['No']);
			$this->db->where('NoEnd', $d['No']);
			$this->db->update('journalorders', $save);
			
			$this->load->model('message_model');
			$message = "期刊<<{$journal['Name']}>> {$journal['year']}年第{$d['No']}期的报数已生成，现可以对相应期刊期次进行印制";
			$this->message_model->send_by_group_name('生产', $message);
			$this->message_model->send_by_group_name('管理员', $message);
		} else {
			$field = array(
				'JID', 'No', 'Year'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			foreach($data as &$d) {
				$publishnotify = $this->db->get_where('publishnotify', array('JID' => $d['JID'], 'No' => $d['No']))->row_array();
				if ($publishnotify) {
					return $this->result(false, "已开印，不可修改");
				}
				$d['BatchID'] = sprintf("%s%04d",date('Ymj'), $_SESSION['userinfo']['id']);
				$save = array (
					'ReportBatchID' => $d['BatchID'],
					'ReportStatus'	=> 1
				);
				
				$this->db->where('Jyear', $d['Year']);
				$this->db->where('JID', $d['JID']);
				$this->db->where('NoStart', $d['No']);
				$this->db->where('NoEnd', $d['No']);
				$this->db->update('journalorders', $save);
			}
		}
		return $this->result();
	
	}
	public function publishnotify_before_edit($oper, $model, &$data, $old)
	{
		if ($oper == "create") {
			$d=&$data[0];
			$journal = $this->db->get_where('journalbaseinfo', array('id' => $d['JID']))->row_array();
			if (!$journal) {
				return $this->result(false, "期刊错误");
			} 
			$publishrecords = $this->db->get_where('publishrecords', array('JID' => $d['JID'], 'No' => $d['No']))->row_array();
			if (!$publishrecords) {
				return $this->result(false, "无印制责任卡");
			}
			$publishrecords = $this->db->get_where('publishnotify', array('JID' => $d['JID'], 'No' => $d['No']))->row_array();
			if ($publishrecords) {
				return $this->result(false, "已有重复印制单");
			}
		} else {
			foreach ($old as $o) {
				if ($o['Status'] >= 1) {
					foreach ($data as $k=>$d) {
						if ((int)$d['id'] == (int)$o['id']) {
							return $this->result(false, "已进行审核,无法修改");
						}
					}
				}
			}
		}

		return $this->result();
	}

	public function publishnotifydetails_edit($oper, $model, &$data, $old)
	{
		$this->load->model('PublishNotify');
		if ($oper == 'create') {
			$d = &$data[0];
			if(isset($d['colourCount'])){
				$d['colourCount'] = (int)$d['colourCount'];
			}
			//TODO
			$notify = $this->PublishNotify->prepare($d['PNID']);
			if ($notify) {
				if ($notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
			}
		} else {
			$field = array(
				'PNID', 'KaiShu', 'PublishCount', 'colourCount', 'ZoomPercent', 'Pages'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			foreach($data as &$d) {
				$notify = $this->PublishNotify->prepare($d['PNID']);
				if ($notify && $notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
				$pages = explode('-', $d['Pages']);
				if (count($pages) == 2) {
					$page = (int)$pages[1];
					if ($page > 0) {
						$kai = (int)$d['KaiShu'];
						$count = (int)$d['PublishCount'];
						$color = (int)$d['colourCount'];
						$zoom = (int)$d['ZoomPercent'];
						if ($kai > 0) {
							$paper = round($page * $count / $kai + 0.4999);
							$zoompaper = $color * $zoom;
							$totalpaper = $paper + $zoompaper;
							$d['TotalPaper'] = number_format($totalpaper / 1000, 4);
							$d['PaperCount'] = number_format($paper / 1000, 4);
							$d['ZoomPaperCount'] = number_format($zoompaper / 1000, 4);
						}
					}
				}
			}
		}
		return $this->result(true);
	}

	public function publishnotifydeliver_edit($oper, $model, &$data, $old)
	{
		$this->load->model('PublishNotify');
		if ($oper == 'create') {
			$d = &$data[0];
			if (isset($d['PNID'])){
				$notify = $this->PublishNotify->prepare($d['PNID']);
				if ($notify && $notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
			}
		} else {
			$field = array(
				'PNID'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			foreach($data as $d) {
				$notify = $this->PublishNotify->prepare($d['PNID']);
				if ($notify && $notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
			}
		}

		return $this->result(true);
	}
	public function publishnotifyprice_edit($oper, $model, &$data, $old)
	{
		$this->load->model('PublishNotify');
		if ($oper == 'create') {
			$d = &$data[0];
			if (isset($d['PNID'])){
				$notify = $this->PublishNotify->prepare($d['PNID']);
				if ($notify && $notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
			}
		} else {
			$field = array(
				'PNID'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			foreach($data as $d) {
				$notify = $this->PublishNotify->prepare($d['PNID']);
				if ($notify && $notify['Status'] > 1) {
					return $this->result(false, "已进行审核,无法修改");
				}
			}
		}

		return $this->result(true);
	}

	public function paperusedetail_stock_in($oper, $model, &$data, $old)
	{
		$this->load->model('PaperStock');
		if ($oper == 'create') {
			$d = &$data[0];
			$d['Type'] = 1;
			if (!isset($d['Note'])) {
				$d['Note'] = "手动入库";
			}
			$d['Price'] = number_format($d['Price'], 2);
			if ($d['Price'] < 0.01) {
				return $this->result(false, "价格错误");
			}
			$this->PaperStock->prepare($d['PaperStyleID']);
			$this->PaperStock->stock_in($d['Counts'], $d['Price'], $d['Note']);

		}
		return $this->result(true);
	}
	
	public function paperusedetail_stock_out($oper, $model, &$data, $old)
	{
		$this->load->model('PaperStock');
		if ($oper == 'create') {
			$d = &$data[0];
			$d['Type'] = 0;
			if (!isset($d['JID'])) {
				$d['JID'] = null;
			}
			if (!isset($d['Year'])) {
				$d['Year'] = null;
			}
			if (!isset($d['No'])) {
				$d['No'] = null;
			}
			if (!isset($d['Note'])) {
				$d['Note'] = "手动出库";
			}
			$this->PaperStock->prepare($d['PaperStyleID']);
			$this->PaperStock->stock_out($d['Counts'], null, $d['Note'], $d['JID'], $d['Year'], $d['No']);

		}
		return $this->result(true);
	}

	public function arrivalmanage_edit($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d = &$data[0];
			$d['BatchID'] = date('Ymd');
			if(!isset($d['Note'])){
				$d['Note'] = '';
			}
			$publish_notify = $this->db->get_where('publishnotify', array('JID' => $d['JID'], 'Year' => $d['Year'], 'No' => $d['No']))->result_array();
			if (!count($publish_notify)) {
			//	return $this->result(false, "未印制,无法到货");
			}
			$sql = <<<EOT
	select sum(jo.orderCount) as counts from qkzx_journalorders jo where 
		jo.jid = ? and year = ? and ? between nostart and noend 
		and (
			(jo.isneedDeliver = 1 and jo.saleStyle in (1,5,6,7,8,9) )
			or
			(jo.saleStyle = 2)
		)
EOT;
			$result = $this->db->query($sql, array($d['JID'], $d['Year'], $d['No']));
			$counts = $result->row_array();
			if ($d['Counts'] >= $counts['counts'] ) {
				$this->db->query("set @BatchID ='{$d['BatchID']}'");
				$this->db->query("set @JID ={$d['JID']}");
				$this->db->query("set @AID = {$d['AID']}");
				$this->db->query("set @Year = '{$d['Year']}'");
				$this->db->query("set @Volume = ''");
				$this->db->query("set @No = {$d['No']}");
				$this->db->query("set @Counts ={$d['Counts']}");
				$this->db->query("set @Note ='{$d['Note']}'");
				$this->db->query("call AddArrivalAndDelivery(@BatchID, @JID, @AID, @Year, @Volume, @No, @Counts, @Note)");
				//TODO: 检查返回状态
				unset($data[0]);
			} else if($d['Counts'] > 0) {
				$this->load->model('JournalStockManage');
				$this->JournalStockManage->prepare($d['JID'], $d['Year'], $d['No']);
				$this->JournalStockManage->stock_in($d['Counts'], 1);
			} else {
				return $this->result(false, "到货数量不正确");
			}
		}
		return $this->result(true);
	}

	public function journalfeemaster_edit_type1($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d = &$data[0];
			if (!isset($d['No'])){
				return $this->result(false, "未填写期次");
			}
			$d['Type'] = 1;
			$d['Status'] = 1;
			$d['BatchNo'] = sprintf('%.0f', microtime(true) * 1000);
			$d['CreateTime'] = date('Y-m-d h:i:s');
		}
		return $this->result(true);
	}
	public function journalfeemaster_edit_type2($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d = &$data[0];
			if (!isset($d['No'])){
				return $this->result(false, "未填写期次");
			}
			$d['Type'] = 2;
			$d['Status'] = 1;
			$d['BatchNo'] = sprintf('%.0f', microtime(true) * 1000);
			$d['CreateTime'] = date('Y-m-d h:i:s');
		}
		return $this->result(true);
	}
	public function journalfeemaster_edit_type3($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			$d = &$data[0];
			if (!isset($d['No'])){
				return $this->result(false, "未填写期次");
			}
			$d['Type'] = 3;
			$d['Status'] = 1;
			$d['BatchNo'] = sprintf('%.0f', microtime(true) * 1000);
			$d['CreateTime'] = date('Y-m-d h:i:s');
		}
		return $this->result(true);
	}

	public function publishrecords_edit($oper, $model, &$data, $old)
	{
		if($oper == "create") {
			$d = &$data[0];
			
			$exist = $this->db->get_where('publishrecords', array('JID' => $d['JID'], 'No' => $d['No']))->row_array();
			if ($exist) {
				return $this->result(false, "已存在该期次的印制责任卡");
			}
		} else {
			$field = array(
				'JID', 'No', 'EditOfficeNeedType', 'EditOfficeNeedCount'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
		}
		foreach ($data as $d) {
			$this->load->model('JournalBaseInfo');
			$journal = $this->JournalBaseInfo->prepare($d['JID']);
			if (!$journal) {
				return $this->result(false, "期刊错误");
			}
			if (isset($d['EditOfficeNeedType']) && isset($d['EditOfficeNeedCount'])) {
				$office = $this->db->get_where('editorialoffice', array('id' => $journal['EID']))->row_array();
				if ($office['CID']) {
					$order = $this->db->get_where('journalorders', array('JID' => $d['JID'], 'NoStart' => $d['No'], 'CID' => $office['CID']))->row_array();
					if (!$order) {
						$order = array(
							"CID"	=>	$office['CID'],
							"JID"	=>	$d['JID'],
							"AID"	=>	$_SESSION['userinfo']['id'],
							"Year"	=>	$journal['year'],
							"jyear"	=>	$journal['year'],
							"NoStart"	=>	$d['No'],
							"NoEnd"	=>	$d['No'],
							"CostDiscount"	=>	$journal['CostDiscount'],
							"SaleDiscount"	=>	$journal['SaleDiscount'],
							"SaleStyle"	=>	1,
							"OrderType"	=>	1,
							"IsNeedDeliver"	=>	1,
							"Note"	=>	"由印制责任卡自动生成的订单"
						);
					}
					
					if ($d['EditOfficeNeedType'] == 2) {
						$order['OrderCount'] = $d['EditOfficeNeedCount'];
					} else {
						$order['OrderCount'] = 0;
					}
					$order['TotalPrice'] = $order['OrderCount'] * $journal['Price'];
					$order['SalesTotal'] = $order['TotalPrice'] * $order['CostDiscount'] / 100;
					$order['CostTotal'] = $order['TotalPrice'] * $order['SaleDiscount'] / 100;
					$order['ReportStatus'] = 0;
					if (isset($order['id'])) {
						$this->db->update('journalorders', $order, array('id' => $order['id']));
					} else if ($order['OrderCount'] > 0) {
						$this->db->insert('journalorders', $order);
					}
				}
				
			}
			if($oper == "create") {
				$this->load->model('message_model');
				$message = "期刊<<{$journal['Name']}>> {$journal['year']}年第{$d['No']}期的印制责任卡已做，现可以对相应期刊期次进行报数";
				$this->message_model->send_by_group_name('销售', $message);
				$this->message_model->send_by_group_name('管理员', $message);
			}
		}
		return $this->result(true);
	}

	public function cashdailybook_edit($oper, $model, &$data, $old)
	{
		foreach($data as &$d){
			$field = array(
				'RIDFlag'
			);
			$this->array_merge_by_primary($model->primary, $data, $old, $field);
			$flag = $d['RIDFlag'];
			if ($flag != 1) {
				$d['JID'] = '';
			}else if (!isset($d['JID'])){
				return $this->result(false, '未选择期刊');
			}
			if ($flag != 2) {
				$d['PID'] = '';
			}else if (!isset($d['PID'])){
				return $this->result(false, '未选择印厂');
			}
			if ($flag != 3) {
				$d['EID'] = '';
			}else if (!isset($d['EID'])){
				return $this->result(false, '未选择编辑部');
			}
		}
		return $this->result(true);
	}

}
