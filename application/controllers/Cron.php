<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{

    public function schedule()
    {
        $this->load->model('db_model');
        $this->db_model->table('schedule');
        $save = array(
            "status" => "ok",
            "CreateTime" => date('Y-m-d h:i:s')
        );
        $this->db_model->save($save);
    }
}