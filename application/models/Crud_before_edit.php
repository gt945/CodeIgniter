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
		if (isset($data['password'])) {
			$data['password'] = md5($data['password']);
		}
		if ($oper == 'create') {
			$data['create_date'] = date('Y-m-j H:i:s');
		}
        return $this->result();
	}
	
	function group_edit($oper, $model, &$data, $old)
	{
		
	}
	
	
	function order_hook($oper, $model, &$data, $old)
	{

		if ($oper == 'create') {
			$this->load->model('JournalStockManage');
			if ($data['OrderType'] == 1) {														/* 收订 - 预留库存 */
			
				$d = $data[0];
				/*get customer info*/
				$this->db2->from('customers');
				$this->db2->where('id', $d['CID']);
				$customer = $this->db2->row();

				if($customer['CType'] == 2) {													/* 收订 - 预留库存 */
					$d['SaleStyle'] = 2;
					$d['ReportStatus'] = 0;
					$NoStart = (int)$d['NoStart'];
					$NoEnd = (int)$d['NoEnd'];

					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						$data[] = $d;
					}
				} else if($customer['CType'] == 9) {											/* 收订 - 补刊 */
					$d['SaleStyle'] = 9;
					$d['ReportStatus'] = 1;
					$NoStart = (int)$d['NoStart'];
					$NoEnd = (int)$d['NoEnd'];

					
					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						
						$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
						$stockcount = $this->JournalStockManage->stock_count();
						if ($stockcount < $d['OrderCount']) {									/* 收订 - 补刊 - 库存不够*/
							//TODO 库存不够
							//Do Nothing
						} else {																/* 收订 - 补刊 - 减库存并记录*/
							$this->JournalStockManage->stock_out($d['OrderCount'], 11, $d['CID']);
						}
						
						$data[] = $d;
					}

				} else {																		/* 收订 - 一般客户和其他客户 */
                    $NoStart = (int)$d['NoStart'];
                    $NoEnd = (int)$d['NoEnd'];
					if($customer['CType'] != 1) {												/* 收订 - 一般客户 */
						$d['SaleStyle'] = $customer['CType'];
					}
				
										
					$this->db2->from('journalbaseinfo');
					$this->db2->where('id', $data['JID']);
					$journalbaseinfo = $this->db2->row();
					
					
					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						
						if ($journalbaseinfo['Classify'] == 1) {								/* 收订 - 其他客户 - 代理类期刊*/

							$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
							$stockcount = $this->JournalStockManage->stock_count();
							if($stockcount === null) {
								$d['ReportStatus'] = 0;
							} else if ($stockcount < $d['OrderCount']) {						/* 收订 - 其他客户 - 代理类期刊 - 库存不够*/
								//TODO 库存不够
								continue;
							} else {															/* 收订 - 其他客户 - 代理类期刊 - 减库存并记录*/
								$d['ReportStatus']  = 1;
								$d['SaleStyle'] = 7;
								$this->JournalStockManage->stock_out($d['OrderCount'], 10, $d['CID']);
							}
						} else {																/* 收订 - 其他客户 - 非代理类期刊*/
							$this->load->model('ReportCounts');
							$this->ReportCounts->prepare($d['JID'], $d['Jyear'], $i);
							$reportcounts = $this->ReportCounts->report_count();
							if ($reportcounts !== null) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录*/

								$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
								$stockcount = $this->JournalStockManage->stock_count();
								if($stockcount === null) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存*/
									$this->db2->from('publishnotify');
									$this->db2->where('Year', $d['Jyear']);
									$this->db2->where('JID', $d['JID']);
									$this->db2->where('No', $i);
									$publishnotify = $this->db2->row();
									if($publishnotify) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单*/
										$this->db2->from('journalorders');
										$this->db2->where('Jyear', $d['Jyear']);
										$this->db2->where('JID', $d['JID']);
										$this->db2->where('NoStart', $i);
										$this->db2->where('NoEnd', $i);
										$this->db2->where('SaleStyle', 2);
										$reserved = $this->db2->row();
										if ($reserved) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存*/
											if ($reserved['OrderCount'] < $d['OrderCount']) {	/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 预留库存不够*/
												//TODO 预留库存不够
												continue;
											} else {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 减预留库存*/
												$reserved['OrderCount'] -= $d['OrderCount'];
												$save = array(
													'OrderCount' => $reserved['OrderCount']
												);
												$this->db2->where('id', $reserved['id']);
												$this->db2->update('journalorders', $save);
											}
										} else {												/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 无预留库存*/
											//TODO 无预留库存
											continue;
										}
									} else {													/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 无印制单*/
										$d['ReportStatus'] = 1;
										$this->ReportCounts->report_in($d['OrderCount']);
									}
								} else if ($stockcount < $d['OrderCount']) {					/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 库存不够*/
									//TODO 库存不够
									continue;
								} else {														/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 减库存并记录*/
									$d['ReportStatus']  = 1;
									$d['SaleStyle'] = 7;
									$this->JournalStockManage->stock_out($d['OrderCount'], 10, $d['CID']);
								}
							} else {															/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录*/

								$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
								$stockcount = $this->JournalStockManage->stock_count();
								if($stockcount === null) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 无库存*/
									$d['ReportStatus']  = 0;
								} else if ($stockcount < $d['OrderCount']) {					/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 库存不够*/
									//TODO 库存不够
									continue;
								} else {														/* 收订 - 其他客户 - 非代理类期刊 - 报数表无记录 - 减库存并记录*/
									$d['ReportStatus']  = 1;
									$d['SaleStyle'] = 7;
									$this->JournalStockManage->stock_out($d['OrderCount'], 10, $d['CID']);
								}
							}
						}

						$data[] = $d;
					}
					
					
				}
			} else if ($data['OrderType'] == 2) {												/* 退订*/
                $d = $data[0];
				$NoStart = (int)$d['NoStart'];
				$NoEnd = (int)$d['NoEnd'];
				unset($data[0]);
				for ($i = $NoStart; $i <= $NoEnd; $i++) {
					$this->db2->from('journalorders');
					$this->db2->where('Year', $d['Jyear']);
					$this->db2->where('JID', $d['JID']);
					$this->db2->where('CID', $d['CID']);
					$this->db2->where('NoStart', $i);
					$this->db2->where('NoEnd', $i);
					$journalorders = $this->db2->sheet();
					$totalcount = 0;
					foreach($journalorders as $order) {											/* 退订 - 计算订单总数*/
						$totalcount += $order['OrderCount'];
					}
					
					if ($d['OrderCount'] > $totalcount) {										/* 退订 - 退订数量大于订单总数*/
						//TODO 数据有误
						continue;
					} else {																	/* 退订 - 退订数量小于等于订单总数*/
						$this->db2->from('deliverydetails');
						$this->db2->where('Year', $d['Jyear']);
						$this->db2->where('JID', $d['JID']);
						$this->db2->where('No', $i);
						$this->db2->where('CID', $d['CID']);
						$deliverydetails = $this->db2->row();
						if ($deliverydetails) {													/* 退订 - 退订数量小于等于订单总数 - 已发货*/
							$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
							$this->JournalStockManage->stock_in($d['OrderCount'], 1);
						} else {																/* 退订 - 退订数量小于等于订单总数 - 未发货*/
							// Do Nothing
						}
						$d['OrderCount'] = -$d['OrderCount'];
					}
					
					$data[] = $d;
				}
			}

		}



		$field = array(
			'JID', 'NoStart', 'NoEnd', 'OrderCount', 'CostDiscount', 'SaleDiscount', 'TotalPrice', 'SalesTotal', 'CostTotal'
		);

		$this->array_merge_by_primary($model->primary, $data, $old, $field);
		foreach($data as &$d) {
			$NoStart = (int)$d['NoStart'];
			$NoEnd = (int)$d['NoEnd'];
			$OrderCount = (int)$d['OrderCount'];
			$CostDiscount = (int)$d['CostDiscount'];
			$SaleDiscount = (int)$d['SaleDiscount'];
			$this->db2->where('id', $d['JID']);
			$this->db2->from('journalbaseinfo');
			$Price = $this->db2->cell('Price');
			if ($NoEnd >= $NoStart) {
				$Count = ($NoEnd - $NoStart +1) * $d['OrderCount'];
				$d['TotalPrice'] = $Count * $Price;
				$d['SalesTotal'] = $d['TotalPrice']  * $d['SaleDiscount'] / 100;
				$d['CostTotal'] = $d['TotalPrice']  * $d['CostDiscount'] / 100;
			}
		}
        return $this->result();
	}
	
	public function report_hook ($oper, $model, &$data, $old)
	{
		if ($oper == 'create') {
			//TODO 'UID'
			$data['BatchID'] = date('Ymj').'111';
			$this->load->model('ReportCounts');
			$existreport = $this->ReportCounts->prepare($data['JID'], $data['Year'], $data['No']);
			if ($existreport) {
				//TODO 已有报数
			} else {
				$save = array (
					'ReportBatchID' => $data['BatchID'],
					'ReportStatus'	=> 1
				);
				
				$this->db2->where('Jyear', $data['Year']);
				$this->db2->where('JID', $data['JID']);
				$this->db2->where('NoStart', $data['No']);
				$this->db2->where('NoEnd', $data['No']);
				$this->db2->update('journalorders', $save);
			}
		}
        return $this->result();
	
	}
	public function publishnotify_before_edit($oper, $model, &$data, $old)
    {
        foreach ($old as $o) {
            if ($o['Status'] > 1) {
                foreach ($data as $k=>$d) {
                    if ((int)$d['id'] == (int)$o['id']) {
                        return $this->result(false, "已进行审核,无法修改");
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
            $notify = $this->PublishNotify->prepare($d['PNID']);
            if ($notify) {
                if ($notify['Status'] > 1) {
                    return $this->result(false, "已进行审核,无法修改");
                }
                $this->load->model('JournalBaseInfo');
                $journal = $this->JournalBaseInfo->prepare($notify['JID']);
                if($journal && $journal['providePaper']  == 0) {
                    $this->load->model('PaperStock');
                    $this->PaperStock->prepare($d['paperDeduceID']);
                    $this->PaperStock->stock_out($d['TotalPaper']);
                    $this->load->model('PaperUseDetail');
                    $d['paperUseDetailID'] = $this->PaperUseDetail->create($d, $journal, $notify);
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

}
