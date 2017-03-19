<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{

	public function schedule()
	{
		$this->load->model('db_model');
		$this->load->model('message_model');
		$setting = $this->db->get("setting", array('id'=>1))->row_array();
		$year = 2014; //date("Y");
		$month = date("n");

		$this->message_model->send_by_group_name("编辑", "test", true);
		//出版
		if ($setting['PublishEnable']) {
			$preday = $setting['Publish'];
			$day = date('j', strtotime("+{$preday} days"));
			$this->db->from("journalbaseinfo");
			$this->db->where("year", $year);
			$this->db->group_start();
			$this->db->where("PublishMonth", $month);
			$this->db->or_like("PublishMonth", ",{$month},", "both");
			$this->db->or_like("PublishMonth", "{$month},", "after");
			$this->db->or_like("PublishMonth", ",{$month}", "before");
			$this->db->group_end();
			$this->db->group_start();
			$this->db->where("PublishDay", $day);
			$this->db->or_like("PublishDay", "{$day},", "after");
			$this->db->or_like("PublishDay", ",{$day}", "before");
			$this->db->or_like("PublishDay", ",{$day},", "both");
			$this->db->select("id, AID, PublishDay,Name");
			$this->db->group_end();
			$journals = $this->db->sheet();
			$target = array();
			foreach($journals as $j) {
				$target[] = "{$j['Name']}";
			}
			echo implode(';', $target);
			echo "<br>";
		}

		//编辑部交稿、交片
		if ($setting['HandEnable']) {
			$preday = $setting['Hand'];
			$day = date('j', strtotime("+{$preday} days"));
			$this->db->from("journalbaseinfo");
			$this->db->where("year", $year);
			$this->db->group_start();
			$this->db->where("HandMonth", $month);
			$this->db->or_like("HandMonth", ",{$month},", "both");
			$this->db->or_like("HandMonth", "{$month},", "after");
			$this->db->or_like("HandMonth", ",{$month}", "before");
			$this->db->group_end();
			$this->db->group_start();
			$this->db->where("HandDay", $day);
			$this->db->or_like("HandDay", "{$day},", "after");
			$this->db->or_like("HandDay", ",{$day}", "before");
			$this->db->or_like("HandDay", ",{$day},", "both");
			$this->db->select("id, AID, PublishDay,Name");
			$this->db->group_end();
			$journals = $this->db->sheet();
			$target = array();
			foreach($journals as $j) {
				$target[] = "{$j['Name']}";
			}
			echo implode(';', $target);
			echo "<br>";
		}

		//分社发稿、发片
		if ($setting['SubHandEnable']) {
			$preday = $setting['SubHand'];
			$day = date('j', strtotime("+{$preday} days"));
			$this->db->from("journalbaseinfo");
			$this->db->where("year", $year);
			$this->db->group_start();
			$this->db->where("SubHandMonth", $month);
			$this->db->or_like("SubHandMonth", ",{$month},", "both");
			$this->db->or_like("SubHandMonth", "{$month},", "after");
			$this->db->or_like("SubHandMonth", ",{$month}", "before");
			$this->db->group_end();
			$this->db->group_start();
			$this->db->where("SubHandDay", $day);
			$this->db->or_like("SubHandDay", "{$day},", "after");
			$this->db->or_like("SubHandDay", ",{$day}", "before");
			$this->db->or_like("SubHandDay", ",{$day},", "both");
			$this->db->select("id, AID, PublishDay,Name");
			$this->db->group_end();
			$journals = $this->db->sheet();
			$target = array();
			foreach($journals as $j) {
				$target[] = "{$j['Name']}";
			}
			echo implode(';', $target);
			echo "<br>";
		}

		//邮局报数
		if ($setting['PosterOfferEnable']) {
			$preday = $setting['PosterOffer'];
			$day = date('j', strtotime("+{$preday} days"));
			$this->db->from("journalbaseinfo");
			$this->db->where("year", $year);
			$this->db->group_start();
			$this->db->where("PosterOfferMonth", $month);
			$this->db->or_like("PosterOfferMonth", ",{$month},", "both");
			$this->db->or_like("PosterOfferMonth", "{$month},", "after");
			$this->db->or_like("PosterOfferMonth", ",{$month}", "before");
			$this->db->group_end();
			$this->db->group_start();
			$this->db->where("PosterOfferDay", $day);
			$this->db->or_like("PosterOfferDay", "{$day},", "after");
			$this->db->or_like("PosterOfferDay", ",{$day}", "before");
			$this->db->or_like("PosterOfferDay", ",{$day},", "both");
			$this->db->select("id, AID, PublishDay,Name");
			$this->db->group_end();
			$journals = $this->db->sheet();
			$target = array();
			foreach($journals as $j) {
				$target[] = "{$j['Name']}";
			}
			echo implode(';', $target);
			echo "<br>";
		}

		$this->db_model->table('schedule');
		$save = array(
			"status" => "ok",
			"CreateTime" => date('Y-m-d h:i:s')
		);
		$this->db_model->save($save);
	}
}