<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: tao
 * Date: 16-11-27
 * Time: 下午9:14
 */
class MY_Controller extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model ( 'auth_model' );
        if (! $this->auth_model->check( false )) {
            $this->reply(401, "请重新登录");
        }
    }

    public function reply($code = 200, $msg = "Success", $data=null)
    {
        $response = new stdClass();
        $response->code = $code;
        $response->msg = $msg;
        if ($data) {
            $response->data = $data;
        }
        echo json_encode($response);
        die();
    }
}