<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

      public function __construct() {
            parent::__construct();
            // Default load class
            // Response heade content type set from text/HTML to application/json
            header('Content-type: application/json');
            date_default_timezone_set("GMT");
            // Load modal class
            $this->load->model('m_api');
            // Load validation class
            $this->load->library('validation');

      }

      public function index() {
            // Intial testing function for controller
      echo json_encode("API controller is working fine");

      }

       function email_configration() {
        $email = "support@s166-62-92-171.secureserver.net";
        $this->load->library('email');
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'localhost';
        $config['smtp_port'] = '25';
        $config['smtp_timeout'] = '7';
        $config['smtp_user'] = $email;
        $config['smtp_pass'] = 'tL1d1@4t';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html
        $config['validation'] = TRUE; // bool whether to validate email or not      
        $this->email->initialize($config);
        $this->email->from($email, 'Be');
    }

    function send_signup_email($email_id,$data){
       
        $this->email_configration();
        $this->email->to($email_id);
        $this->email->subject("Verify Your BE Account");
        $this->email->message($this->load->view('v_signup_email', $data,true));
        $this->email->send();
    }

    function test_email(){
        $code="";
        
        echo "success";
    }

     function signup(){
        $params=$_POST;
        // Validation of required fields
        $params=$this->validation->required_fields('email_id,password','',$params);
        // Modal class call
        $response=$this->m_api->signup($params);
        if($response['statuscode']==201){

            $mail['link']=base_url('index.php/verify/process/'.$response['verify_code']);
            $this->send_signup_email("amar.appvolution@gmail.com",$data);
            unset($response['verify_code']);
        }

        echo json_encode($response);
    }

  function login(){
    $params=$_POST;
        // Validation of required fields
    $params=$this->validation->required_fields('email_id,password','',$params);
        // Modal class call
        $response=$this->m_api->login($params);
        echo json_encode($response);
  }

    function expertise_list(){
        $params=$_POST;
        // Modal class call
        $response=$this->m_api->expertise_list();
        echo json_encode($response);
    }

    function update_profile(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id','display_name,motto,birthday,gender,expertise_name,location_name,gender',$params);
    
          $params['avatar']=$this->validation->file_upload('avatar','avatar');
             $response=$this->m_api->update_profile($params);
           echo json_encode($response);
    }

    function goal_category(){
         $params=$_POST;
        // Modal class call
        $response=$this->m_api->goal_category();
        echo json_encode($response);
    }

    function create_goal(){
         $params=$_POST;
          $params=$this->validation->required_fields('user_id,goal_name,finish_line,goal_category_id','',$params);
          $params['goal_pic']=$this->validation->file_upload('goal_pic','goal_pic');
          $response=$this->m_api->create_goal($params);
          echo json_encode($response); 
    }

    function my_goals(){
            $params=$_GET;
            $params=$this->validation->required_fields('user_id','',$params);
            $response=$this->m_api->my_goals($params);
             echo json_encode($response);
    }

    function edit_goal(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id,user_id,goal_name,finish_line,goal_category_id','',$params);
          $params['goal_pic']=$this->validation->file_upload('goal_pic','goal_pic');
          $response=$this->m_api->create_goal($params);
          echo json_encode($response); 
    }

    function delete_goal(){
          $params=$_GET;
          $params=$this->validation->required_fields('goal_id,user_id','',$params);
          $response=$this->m_api->delete_goal($params);
          echo json_encode($response);
    }

    function get_goal_steps(){
          $params=$_GET;
          $params=$this->validation->required_fields('goal_id,user_id','',$params);
          $response=$this->m_api->get_goal_steps($params);
          echo json_encode($response);
    }
    function add_goal_step(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id,title,stride_id','',$params);
          $response=$this->m_api->add_goal_step($params);
          echo json_encode($response);
    }

    function edit_goal_step(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_step_id,user_id,title','',$params);
          $response=$this->m_api->edit_goal_step($params);
          echo json_encode($response);
    }

    function delete_goal_step(){
           $params=$_GET;
          $params=$this->validation->required_fields('goal_step_id,user_id','',$params);
          $response=$this->m_api->delete_goal_step($params);
          echo json_encode($response);

    }

    function add_goal_stride(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id,user_id,title,finish_line','',$params);
          $response=$this->m_api->add_goal_stride($params);
          echo json_encode($response);
    }

    function edit_goal_stride(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_stride_id,user_id,title,finish_line','',$params);
          $response=$this->m_api->edit_goal_stride($params);
          echo json_encode($response);
    }

    function delete_goal_stride(){
           $params=$_GET;
          $params=$this->validation->required_fields('goal_stride_id,user_id','',$params);
          $response=$this->m_api->delete_goal_stride($params);
          echo json_encode($response);

    }

    function search_users(){
          $params=$_GET;
          $params=$this->validation->required_fields('user_id,limit,offset','keyword',$params);
         $response=$this->m_api->search_users($params);
          echo json_encode($response);
    }

    function add_goal_team(){
         $params=$_GET;
          $params=$this->validation->required_fields('user_id,team_memeber,goal_id','',$params);
          $response=$this->m_api->add_goal_team($params);
          echo json_encode($response);
    }

    function get_goal_team(){
          $params=$_GET;
          $params=$this->validation->required_fields('goal_id','',$params);
          $response=$this->m_api->get_goal_team($params);
          echo json_encode($response);
    }

    function get_stride_data(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id,user_id','',$params);
          $response=$this->m_api->get_stride_data($params);
          echo json_encode($response);
    }

    function get_stride_step(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_stride_id,user_id','',$params);
          $response=$this->m_api->get_stride_step($params);
          echo json_encode($response);
    }

    function goal_like(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id,user_id,is_like','',$params);
          $response=$this->m_api->goal_like($params);
          echo json_encode($response);
    }

    function user_profile(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->user_profile($params);
          echo json_encode($response);
    }

    function user_list(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id','limit,offset',$params);
          $response=$this->m_api->user_list($params);
          echo json_encode($response);
    }

    function user_list_with_contact(){
          $params=$_POST;
          $params=$this->validation->required_fields('email_list','',$params);
          $response=$this->m_api->user_list_with_contact($params);
          echo json_encode($response);
    }

    function goal_detail(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id','',$params);
          $response=$this->m_api->goal_detail($params);
          echo json_encode($response);
    }

    function invite_user(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_id,invite_user_id_list,owner_id','',$params);
          $response=$this->m_api->invite_user($params);
          echo json_encode($response);
    }

    function invitation_list(){
          $params=$_REQUEST;;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->invitation_list($params);
          echo json_encode($response);
    }

    function accept_invitation(){
          $params=$_REQUEST;;
          $params=$this->validation->required_fields('goal_invitation_id,user_id','',$params);
          $response=$this->m_api->accept_invitation($params);
          echo json_encode($response);
    }

    function reject_invitation(){
          $params=$_REQUEST;;
          $params=$this->validation->required_fields('goal_invitation_id,user_id','',$params);
          $response=$this->m_api->reject_invitation($params);
          echo json_encode($response);
    }

    function send_friend_request(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id,to_user_id','',$params);
          $response=$this->m_api->send_friend_request($params);
          echo json_encode($response);
    }

    function acc_dec_teammate_req(){
          $params=$_POST;
          $params=$this->validation->required_fields('goal_team_id,status','',$params);
          $response=$this->m_api->acc_dec_teammate_req($params);
          echo json_encode($response);
    }

    function get_teammate_req_list(){
          $params=$_POST;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->get_teammate_req_list($params);
          echo json_encode($response);
    }

    function acc_dec_friend_req(){
          $params=$_POST;
          $params=$this->validation->required_fields('friend_id,status','',$params);
          $response=$this->m_api->acc_dec_friend_req($params);
          echo json_encode($response);
    }

    function friend_list(){
          $params=$_REQUEST;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->friend_list($params);
          echo json_encode($response);
    }

    function notification(){
          $params=$_REQUEST;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->notification($params);
          echo json_encode($response);
    }

    function userDetail(){
          $params=$_REQUEST;
          $params=$this->validation->required_fields('user_id','',$params);
          $response=$this->m_api->userDetail($params);
          echo json_encode($response); 
    }



  function home_feed(){
          $params=$_REQUEST;
          $params=$this->validation->required_fields('user_id,limit,offset','',$params);
          $response=$this->m_api->home_feed($params);
          echo json_encode($response);
  }

  function add_inspiration(){
          $params=$_REQUEST;
          $params=$this->validation->required_fields('user_id,goal_name','',$params);
          $params['goal_pic']=$this->validation->file_upload('goal_pic','goal_pic');
          $response=$this->m_api->add_inspiration($params);
          echo json_encode($response);
  }

  function shoutouts_goal(){
        $params=$_REQUEST;
        $params=$this->validation->required_fields('user_id,goal_id','',$params);
        $response=$this->m_api->shoutouts_goal($params);
        echo json_encode($response);
  }

  function send_group_msg(){
       $params=$_REQUEST;
        $params=$this->validation->required_fields('type','',$params);
        if($params['type']==1){
         
          $params['msg']=$this->validation->file_upload('msg','msg_media/'.$params['goal_id']);
         $params=$this->validation->required_fields('user_id,goal_id,msg','',$params);
        
        }else{
              $params=$this->validation->required_fields('user_id,goal_id,msg','',$params);
        }
        $response=$this->m_api->send_group_msg($params);
        echo json_encode($response);
  }

  function recieve_msg(){
       $params=$_REQUEST;
        $params=$this->validation->required_fields('goal_id,limit,offset','',$params);
        $response=$this->m_api->recieve_msg($params);
        echo json_encode($response);
  }

  function test_notification(){


    $message['alert']="Nice Application";
    $message['sound']="default";
    $message['type']=0;
    $message['badge']=1;

  echo json_encode(push_notification_ios('DF146F6DA3B6FDF06FE46C9239C671FE1E0B865B197835B3F5F2EAA8AF310FB8',$message));
  }

  function update_device_token(){
        $params=$_REQUEST;
        $params=$this->validation->required_fields('user_id,token','',$params);
        $response=$this->m_api->update_device_token($params);
        echo json_encode($response);
  }



}
