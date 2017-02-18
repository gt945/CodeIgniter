<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH.'models/Crud_hook.php');

class Crud_before_del extends Crud_hook {


    public function __construct()
	{
		parent::__construct ();
	}

	public function publishnotifydetails_del($oper, $model, &$ids, $old)
    {
        $this->load->model('PublishNotify');
        foreach ($old as $d) {
            $notify = $this->PublishNotify->prepare($d['PNID']);
            if ($notify && $notify['Status'] > 1) {
                return $this->result(false, "已进行审核,无法删除");
            }
            if((int)$d['paperDeduceID'] && (int)$d['paperUseDetailID']) {
                $this->load->model("PaperUseDetail");
                $detail = $this->PaperUseDetail->prepare((int)$d['paperUseDetailID']);
                if ($detail) {
                    $this->load->model("PaperStock");
                    $this->PaperStock->prepare($detail['PaperStyleID']);
                    $this->PaperStock->stock_in($detail['Counts']);

                    $this->PaperUseDetail->delete();
                }
            }
        }
        return $this->result(true);
    }

    public function publishnotify_del($oper, $model, &$ids, $old)
    {
        foreach ($old as $d) {
            if($d['Status'] > 1) {
                if(($k = array_search($d['id'], $ids)) !== false) {
                    return $this->result(false, "已进行审核,无法删除");
                }

            }
        }
        if (count($ids)) {
            $this->db->where_in('PNID', $ids);
            $details = $this->db->get('publishnotifydetails')->result_array ();
            $tmp = array();
            $this->publishnotifydetails_del(null, null, $tmp, $details);
            $this->db->where_in('PNID', $ids);
            $this->db->delete('publishnotifydeliver');
            $this->db->where_in('PNID', $ids);
            $this->db->delete('publishnotifyprice');
        }

        return $this->result(true);
    }
    public function publishnotifydeliver_del($oper, $model, &$ids, $old)
    {
        $this->load->model('PublishNotify');
        foreach($old as $d) {
            $notify = $this->PublishNotify->prepare($d['PNID']);
            if ($notify && $notify['Status'] > 1) {
                return $this->result(false, "已进行审核,无法删除");
            }
        }
        return $this->result(true);
    }
    public function publishnotifyprice_del($oper, $model, &$ids, $old)
    {
        $this->load->model('PublishNotify');
        foreach($old as $d) {
            $notify = $this->PublishNotify->prepare($d['PNID']);
            if ($notify && $notify['Status'] > 1) {
                return $this->result(false, "已进行审核,无法删除");
            }
        }

        return $this->result(true);
    }

    public function reportcounts_del($oper, $model, &$ids, $old)
    {
        $this->load->model('ReportCounts');
        foreach($old as $d) {
            $existreport = $this->ReportCounts->prepare($d['JID'], $d['Year'], $d['No']);
            if ($existreport) {
                $save = array (
                    'ReportBatchID' => "",
                    'ReportStatus'	=> 0
                );

                $this->db->where('Jyear', $d['Year']);
                $this->db->where('JID', $d['JID']);
                $this->db->where('NoStart', $d['No']);
                $this->db->where('NoEnd', $d['No']);
                $this->db->where('ReportBatchID', $d['BatchID']);
                $this->db->update('journalorders', $save);
            }
        }
        return $this->result(true);

    }
    
    public function journalorders_del($oper, $model, &$ids, $old)
    {
		foreach($old as $d) {
			if ($d['ReportStatus'] == 1) {
				return $this->result(false, '已报数,无法删除');
			}
		}
		return $this->result(true);
    }
}
