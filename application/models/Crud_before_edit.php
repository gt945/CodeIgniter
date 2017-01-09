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
		
	}
	
	
	function order_hook($oper, $model, &$data, $old)
	{

		if ($oper == 'create') {
            $d = &$data[0];
            $d['Year'] = $d['Jyear'];
			$this->load->model('JournalStockManage');
			if ($d['OrderType'] == 1) {														/* 收订 - 预留库存 */
			
				/*get customer info*/
				$customer = $this->db->get_where('customers', array('id' => $d['CID']))->row_array();

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
				
										
                    $journalbaseinfo = $this->db->get_where('journalbaseinfo', array('id' => $d['JID']))->row_array();
					
					unset($data[0]);
					for ($i = $NoStart; $i <= $NoEnd; $i++) {
						$d['NoStart'] = $i;
						$d['NoEnd'] = $i;
						
						if ($journalbaseinfo['Classify'] == 1) {								/* 收订 - 其他客户 - 代理类期刊*/

							$this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
							$stockcount = $this->JournalStockManage->stock_count();
							if($stockcount === null) {                                          /* 收订 - 其他客户 - 代理类期刊 - 无库存*/
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
									$this->db->from('publishnotify');
									$this->db->where('Year', $d['Jyear']);
									$this->db->where('JID', $d['JID']);
									$this->db->where('No', $i);
									$publishnotify = $this->db->row();
									if($publishnotify) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单*/
										$this->db->from('journalorders');
										$this->db->where('Jyear', $d['Jyear']);
										$this->db->where('JID', $d['JID']);
										$this->db->where('NoStart', $i);
										$this->db->where('NoEnd', $i);
										$this->db->where('SaleStyle', 2);
										$reserved = $this->db->row();
										if ($reserved) {										/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存*/
											if ($reserved['OrderCount'] < $d['OrderCount']) {	/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 预留库存不够*/
												//TODO 预留库存不够
												continue;
											} else {											/* 收订 - 其他客户 - 非代理类期刊 - 报数表有记录 - 无库存 - 有印制单 - 有预留库存 - 减预留库存*/
												$reserved['OrderCount'] -= $d['OrderCount'];
												$save = array(
													'OrderCount' => $reserved['OrderCount']
												);
												$this->db->where('id', $reserved['id']);
												$this->db->update('journalorders', $save);
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
			} else if ($d['OrderType'] == 2) {												/* 退订*/
				$NoStart = (int)$d['NoStart'];
				$NoEnd = (int)$d['NoEnd'];
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
					foreach($journalorders as $order) {											/* 退订 - 计算订单总数*/
						$totalcount += $order['OrderCount'];
					}
					
					if ($d['OrderCount'] > $totalcount) {										/* 退订 - 退订数量大于订单总数*/
                        return $this->result(false, "退订数量超过订阅数量");
					} else {																	/* 退订 - 退订数量小于等于订单总数*/
						$this->db->from('deliverydetails');
						$this->db->where('Year', $d['Jyear']);
						$this->db->where('JID', $d['JID']);
						$this->db->where('No', $i);
						$this->db->where('CID', $d['CID']);
						$deliverydetails = $this->db->row();
                        //FIXME
						if ($deliverydetails) {													/* 退订 - 退订数量小于等于订单总数 - 已发货*/
                            // Do Nothing
                        } else {																/* 退订 - 退订数量小于等于订单总数 - 未发货*/
                            $this->JournalStockManage->prepare($d['JID'], $d['Jyear'], $i);
                            $this->JournalStockManage->stock_in($d['OrderCount'], 1);
						}
						$td = $d;
						$td['OrderCount'] = -$d['OrderCount'];
                        $td['NoStart'] = $i;
                        $td['NoEnd'] = $i;

					}

					$data[] = $td;
				}
			}

		} else {
            $field = array(
                'JID', 'NoStart', 'NoEnd', 'OrderCount', 'CostDiscount', 'SaleDiscount', 'TotalPrice', 'SalesTotal', 'CostTotal'
            );

            $this->array_merge_by_primary($model->primary, $data, $old, $field);
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
				$d['SalesTotal'] = $d['TotalPrice']  * $SaleDiscount / 100;
				$d['CostTotal'] = $d['TotalPrice']  * $CostDiscount / 100;
			}
		}
        return $this->result(true);
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
				
				$this->db->where('Jyear', $data['Year']);
				$this->db->where('JID', $data['JID']);
				$this->db->where('NoStart', $data['No']);
				$this->db->where('NoEnd', $data['No']);
				$this->db->update('journalorders', $save);
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

    public function paperusedetail_stock_in($oper, $model, &$data, $old)
    {
        $this->load->model('PaperStock');
        if ($oper == 'create') {
            $d = &$data[0];
            $d['Type'] = 1;
            $this->PaperStock->prepare($d['PaperStyleID']);
            $this->PaperStock->stock_in($d['Counts']);

        }
        return $this->result(true);
    }
    public function paperusedetail_stock_out($oper, $model, &$data, $old)
    {

        $this->load->model('PaperStock');
        if ($oper == 'create') {
            $d = &$data[0];
            $d['Type'] = 0;
            $this->PaperStock->prepare($d['PaperStyleID']);
            $this->PaperStock->stock_out($d['Counts']);

        }
        return $this->result(true);
    }

    public function arrivalmanage_edit($oper, $model, &$data, $old)
    {
        if ($oper == 'create') {
            $d = &$data[0];
            $sql = "select sum(jo.orderCount) as counts from qkzx_journalorders jo where jo.jid = ? and year = ? and ? between nostart and noend and ( (jo.isneedDeliver = 1 and jo.saleStyle in (1,5,6,7,8,9) ) or (jo.saleStyle = 2))";
            $result = $this->db->query($sql, array($d['JID'], $d['Year'], $d['No']));
            $counts = $result->row_array();
            if ($d['Counts'] >= $counts['counts']) {
                $this->db->query("set @BatchID ='{$d['BatchID']}'");
                $this->db->query("set @JID ={$d['JID']}");
                $this->db->query("set @AID = 11");
                $this->db->query("set @Year = '{$d['Year']}'");
                $this->db->query("set @Volume = ''");
                $this->db->query("set @No = {$d['No']}");
                $this->db->query("set @Counts ={$d['Counts']}");
                $this->db->query("set @Note ='{$d['Note']}'");
                $this->db->query("call AddArrivalAndDelivery(@BatchID, @JID, @AID, @Year, @Volume, @No, @Counts, @Note)");
                //TODO: 检查返回状态
            } else {
                return $this->result(false, "到货数量小于订单数量");
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
            $d['BatchNo'] = sprintf('%.0f', microtime(true)  * 1000);
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
            $d['BatchNo'] = sprintf('%.0f', microtime(true)  * 1000);
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
            $d['BatchNo'] = sprintf('%.0f', microtime(true)  * 1000);
            $d['CreateTime'] = date('Y-m-d h:i:s');
        }
        return $this->result(true);
    }

    /**
     * @param $oper
     * @param $model
     * @param $data
     * @param $old
     *
     * 印制责任卡
     *
     * 检查编辑部要刊数,并自动生成订单
     */
    public function publishrecords_edit($oper, $model, &$data, $old)
    {
//        if ($oper == 'create') {
//            $d = &$data[0];
//            if (isset($d['EditOfficeNeedCount']) && isset($d['JID'])) {         //获取期刊信息
//                $j_info = $this->db->get_where('journalbaseinfo', array('id' => $d['JID']))->row_array();
//                if ($j_info) {                                                  //获取编辑部信息
//                    $eo_info = $this->db->get_where('editorialoffice', array('id' => $j_info['EID']))->row_array();
//                    if ($eo_info) {
//                        if(!$eo_info['CID']) {                                  //没有编辑部对应的客户
//                            $save_customer = array(
//                                'name' => $eo_info['Name'],
//                                'ctype' => 5
//                            );
//                            $this->db->insert('customers', $save_customer);
//                            $eo_info['CID'] = $this->db->insert_id();
//                            $this->db->update('editorialoffice', array('CID' => $eo_info['CID']), array('id' => $eo_info['id']));
//                        }
//                        $save_order = array(
//                            'CID' => $eo_info['CID'],
//                            'JID' => $d['JID'],
//                            'EID' => $j_info['EID'],
//                            'Year' => $j_info['year'],
//                            'Jyear' => $j_info['year'],
//                            'NoStart' => 1,
//                            'NoEnd' => 1,
//                            'OrderCount' => $d['EditOfficeNeedCount']
//                        );
////                        $this->db->insert('journalorders', $save_order);
//                    }
//                }
//            }
//        } else {
//
//        }
        return $this->result(true);
    }
}
