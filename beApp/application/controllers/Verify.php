<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Verify extends CI_Controller {

	 public function __construct() {
        parent::__construct();
        // Default load class
        // Response heade content type set from text/HTML to application/json
  
  
        // Load modal class
        $this->load->model('m_verify');
         // Load validation class
        $this->load->library('validation');
 
    }

    function process($code){

         $response=$this->m_verify->process($code);
        $this->load->view('v_verify_process', $response);

    }


}
