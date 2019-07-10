<?php
class M_Apiv1 extends CI_Model{




    function signup($params){


        $this->db->select('user_id,email_verified');
        $this->db->from('ml_users');
        $this->db->where('email', $params['email']);
        // $this->db->where('email_verified',1);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)> 0){
            if($row[0]['email_verified']==1){
               $return=error_res("Email already exists, Please try with another email",409); 
           }else{
               $return=error_res("Email already exists, Please verfiy your email",409);
           }
        	
        }else{


            $data['display_name']=$params['display_name'];
            $data['email']=$params['email'];
            $data['password']=md5($params['password']);
            $data['user_type']=1;
            $data['avatar']=$params['avatar'];
            $data['age']=$params['age'];
            $data['gender']=$params['gender'];
            $data['created']=created();
            $data['updated']=created();
            $this->db->insert('ml_users',$data);

            $user_id=$this->db->insert_id();



            $return=success_res("Signup successfully",201);
            // $return['user']=$this->get_user($user_id);

      
        }
        return $return;
    }

    function signin($params){
      $this->db->select('user_id,email_verified');
        $this->db->from('ml_users');
        $this->db->where('email', $params['email']);
        $this->db->where('password', md5($params['password']));
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)> 0){
          if($row[0]['email_verified']==0){
            $return=error_res("Please verfiy your email",409);
          }else{
               $return=success_res("Sigin successfully",200);
               $return['user']=$this->get_user($row[0]['user_id']);     
          }
             
        }else{
          $return=error_res("Email or password incorrect");  
        }
        return $return;
    }


	function facebook_signin($params){
	  $this->db->select('user_id,email_verified');
        $this->db->from('ml_users');
        $this->db->where('facebook_id', $params['facebook_id']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)> 0){
               $return=success_res("Facebook sigin successfully",200);
               $return['user']=$this->get_user($row[0]['user_id']);      
        }else{
          $data['display_name']=$params['display_name'];
            $data['email']=$params['email'];
            $data['password']="";
            $data['facebook_id']=$params['facebook_id'];
            $data['user_type']=2;
            $data['avatar']=$params['avatar'];
            $data['age']=$params['age'];
            $data['gender']=$params['gender'];
            $data['created']=created();
            $data['updated']=created();
            $this->db->insert('ml_users',$data);
            $user_id=$this->db->insert_id();
            $return=success_res("Facebook signup successfully",201);
            $return['user']=$this->get_user($user_id);
            
            
        }
        return $return;
	}

    function google_signin($params){
        $this->db->select('user_id,email_verified');
        $this->db->from('ml_users');
        $this->db->where('google_id', $params['google_id']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)> 0){

               $return=success_res("Google signup successfully",200);
               $return['user']=$this->get_user($row[0]['user_id']);      
        }else{
            $data['display_name']=$params['display_name'];
            $data['email']=$params['email'];
            $data['password']="";
            $data['google_id']=$params['google_id'];
            $data['user_type']=3;
            $data['avatar']=$params['avatar'];
            $data['age']=$params['age'];
            $data['gender']=$params['gender'];
            $data['created']=created();
            $data['updated']=created();

            $this->db->insert('ml_users',$data);
            $user_id=$this->db->insert_id();

            $return=success_res("Google sigin successfully",201);
            $return['user']=$this->get_user($user_id);
            
            
        }
        return $return;
    }

    function get_user($user_id){
       $this->db->select('user_id,display_name,email,avatar,age,gender,created,updated');
        $this->db->from('ml_users');
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)>0){
           // $row[0]['avatar']=base_url('avatar/'.$row[0]['avatar']);
            $return=$row[0];
        }else{
             $return=error_res("Invalid user_id");
        }
        return $return;
    }

    function get_leagues(){
        $this->db->select('*');
        $this->db->from('ml_league');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of leagues");
        $return['leagues']=$row;
        return $return;
    }

    function get_payout(){
        $this->db->select('*');
        $this->db->from('ml_payouts');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of leagues");
        $return['payouts']=$row;
        return $return;
    }


    function get_contests($params){
         $this->db->select('contest_id,league_id,name,start_dt,end_dt,type,payout_id,entries,amount');
        $this->db->from('ml_contests');
        if($params['league_id']!="all"){
            $this->db->where('league_id', $params['league_id']);
        }
         if($params['type']!="all"){
            $this->db->where('type', $params['type']);
        }
        $current_date_time=created();
        $this->db->where('start_dt <', $current_date_time);
        $this->db->where('end_dt >', $current_date_time);
        $this->db->limit($params['limit'],$params['offset']);

        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of contests");
        $return['contests']=$row;
        return $return;
    }

    function save_payment($params){

     $this->db->trans_start();
     $params['amount']=$params['amount']/100;
     $data['user_id']=$params['user_id'];
     $data['charge_id']=$params['charge_id'];
     $data['amount']=$params['amount'];
     $data['created']=created();

     $this->db->insert('ml_payment_history',$data);
     $query="UPDATE ml_users SET money = money+".($params['amount'])." WHERE user_id='".$params['user_id']."' ";
     $this->db->query($query);
     $this->db->trans_complete();
     $return=success_res("success");
     return $return;
    }


    function join_contest($params){

       $this->db->select('contest_id,start_dt,end_dt,amount,entries,join_entries,payout_id');
        $this->db->from('ml_contests');
        $this->db->where('contest_id', $params['contest_id']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)>0){

         $current_date_time=created();
         if($row[0]['entries']!=$row[0]['join_entries']){


          $join_entries=intval($row[0]['join_entries'])+1;
 
          $payout_id=$row[0]['payout_id'];
          $amount=intval($row[0]['amount']);
         if($current_date_time > $row[0]['start_dt'] && $current_date_time < $row[0]['end_dt']){

        $this->db->select('join_contest_id');
        $this->db->from('ml_join_contest');
        $this->db->where('user_id', $params['user_id']);
        $this->db->where('contest_id', $params['contest_id']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();

    
        if(count($row)==0){



          $data=array();
          $data['contest_id']=$params['contest_id'];
          $data['user_id']=$params['user_id'];
          $data['created']=created();
          $this->db->insert('ml_join_contest',$data);

          $data=array();
          $data['join_entries']=$join_entries;

          ///
          if($payout_id==1){
            $total_prizes=$join_entries * ($amount * 0.15);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==2){
               $total_prizes=$join_entries * ($amount * 0.02);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==3){
            $total_prizes=$join_entries * ($amount * 0.02);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==4){
            $total_prizes=$join_entries * ($amount * 0.068);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==5){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==6){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==7){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==8){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==9){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==10){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==11){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }else if($payout_id==12){
            $total_prizes=$join_entries * ($amount * 0.09);
            $payment_prizes=$total_prizes*0.9;
            $data['total_prizes']=$total_prizes;
            $data['payment_prizes']=$payment_prizes;
          }


    


      
           


          $this->db->where('contest_id', $params['contest_id']);
          $this->db->update('ml_contests',$data);
          $return=success_res("Successfully join contest");
          

          
        }else{
           $return=success_res("Successfully join contest");
        }
      }else{
        $return=error_res("Sorry, contest was expired");
      }
    }else{
      $return=error_res("Sorry, contest no longer available for join");
    }

        }else{
           $return=error_res("Invalid contest ");
        }



       
        return $return;

    }

    function live_game($params){
        $this->db->select('mc.contest_id,mc.league_id,mc.name,mc.start_dt,mc.end_dt,mc.type,mc.payout_id,mc.entries,mc.amount');
        $this->db->from('ml_join_contest as mjc');
        $this->db->join('ml_contests as mc','mc.contest_id=mjc.contest_id');
         $this->db->where('mjc.user_id', $params['user_id']);
         $this->db->where('mc.is_completed',0);
        $this->db->limit($params['limit'],$params['offset']);
        $this->db->order_by('mjc.join_contest_id');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of contests");
        $return['contests']=$row;
        return $return;
    }

    function history_game($params){
        $this->db->select('mc.contest_id,mc.league_id,mc.name,mc.start_dt,mc.end_dt,mc.type,mc.payout_id,mc.entries,mc.amount');
        $this->db->from('ml_join_contest as mjc');
        $this->db->join('ml_contests as mc','mc.contest_id=mjc.contest_id');
         $this->db->where('mjc.user_id', $params['user_id']);
          $this->db->where('mc.is_completed',1);
        $this->db->limit($params['limit'],$params['offset']);
        $this->db->order_by('mjc.join_contest_id');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of contests");
        $return['contests']=$row;
        return $return;
    }

    function contest_detail($params){
      
    }

    
	

}