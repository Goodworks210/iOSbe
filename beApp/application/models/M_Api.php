<?php
class M_api extends CI_Model {


    function expertise_list(){
       $this->db->select('*');
        $this->db->from('be_expertise');
        $this->db->order_by('expertise_id','asc');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get List of expertise",200);
        $return['expertise_list']=$row;
        return $return;
    }


  function get_user($user_id){
        $this->db->select('user_id,email_id,display_name,avatar,motto,birthday,gender,expertise_name,location_name,goal_count,community_count,assist_count,created,updated');
        $this->db->from('be_users');
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)==0){
          $return=error_res("Invalid user_id",203);
        }else{
            $row[0]['avatar']=base_url('avatar/'.$row[0]['avatar']);
          $return=$row[0];
        }
        return $return;
  }

  function signup($params){

        $this->db->select('user_id');
        $this->db->from('be_users');
        $this->db->where('email_id', $params['email_id']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)> 0){
          $return=error_res("Email already exists",409);
        }else{

            $verify_code=generateRandomString(50);
          $data['email_id']=$params['email_id'];
          $data['password']=md5($params['password']);
            $data['verify_code']=$verify_code;
          $data['created']=created();
          $data['updated']=created();
          $this->db->insert('be_users',$data);
          $user_id=$this->db->insert_id();
          $return=success_res("Signup successfully",201);
           $return['user_id']=$user_id;
            $return['verify_code']=$verify_code;
          
        }
        return $return;

    }

  
    function login($params){

        $this->db->select('user_id');
        $this->db->from('be_users');
        $this->db->where('email_id', $params['email_id']);
        $this->db->where('password', md5($params['password']));
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();

        if(count($row)==0){
          $return=error_res("Email or password incorrect",203);
        }else{
          $return=success_res("Login successfully",200);
          $return['user']=$this->get_user($row[0]['user_id']);
        }
        return $return;

    }


    function update_profile($params){
        $response=$this->get_user($params['user_id']);

        if(isset($response['status'])){
           $return=$response;
        }else{

            if($params['avatar']!=""){
                $avatar=explode("/", $response['avatar']);
         
                $avatar=end($avatar);
                if($avatar!="default.png"){
                     unlink("avatar/".$avatar);
                }
               
            }

       if($params['display_name']!=""){$data['display_name']=$params['display_name']; }
        if($params['avatar']!=""){$data['avatar']=$params['avatar']; }
        if($params['motto']!=""){$data['motto']=$params['motto']; }
        if($params['birthday']!=""){$data['birthday']=$params['birthday']; }
        if($params['expertise_name']!=""){$data['expertise_name']=$params['expertise_name']; }
        if($params['location_name']!=""){$data['location_name']=$params['location_name']; }
        if($params['gender']!=""){$data['gender']=$params['gender']; }
         $data['updated']=created();
         $this->db->where('user_id', $params['user_id']);
         $this->db->update('be_users',$data);
         $return=success_res("Successfully update profile",200);
         $return['user']=$this->get_user($params['user_id']);

        }
         return $return;

    }

    function goal_category(){
        $this->db->select('*');
        $this->db->from('be_goal_category');
        $this->db->order_by('goal_category_id','asc');
        $result = $this->db->get();
        $row = $result->result_array();
        $return=success_res("Get list of category",200);
        $return['goal_categories']=$row;
        return $return;   
    }

    function create_goal($params){

        if($params['goal_pic']==""){
            $params['goal_pic']="default.jpg";
        }

            $data['user_id']=$params['user_id'];
            $data['goal_name']="Be ".$params['goal_name'];
            $data['goal_pic']=$params['goal_pic'];
            $data['finish_line']=date("Y-m-d h:i:s", strtotime($params['finish_line']));
            $data['goal_category_id']=$params['goal_category_id'];
            $data['created']=created();
            $data['updated']=created();
            $this->db->insert('be_goal',$data);
            $goal_id=$this->db->insert_id();
              mkdir('msg_media/'.$goal_id, 0777, true);
            $query="UPDATE be_users SET goal_count = goal_count+1 WHERE user_id='".$params['user_id']."'";
             $this->db->query($query);
             $goal_team['goal_id']=$goal_id;
             $goal_team['user_id']=$params['user_id'];
             $goal_team['created']=created();
             $goal_team['updated']=created();
             $this->db->insert('be_goal_team',$goal_team);

            $gdata=array();
            $gdata['action_type']=1; 
            $gdata['user_id']=$params['user_id']; 
            $gdata['goal_id']=$goal_id; 
            $this->db->insert('be_home_feed',$gdata);


            $return=success_res("Create goal successfully",201);
             $return['goal_detail']=$this->get_goal_detail($goal_id);
            return $return;
    }

    function my_goals($params){
        $this->db->select('bg.goal_id,bg.goal_name,bg.goal_pic,bg.finish_line,bg.goal_category_id,bgc.goal_category,bg.created,bg.updated');
        $this->db->from('be_goal as bg');
        $this->db->join('be_goal_category as bgc','bg.goal_category_id=bgc.goal_category_id','LEFT');
          $this->db->where('bg.user_id', $params['user_id']);
        $this->db->where('bg.goal_type', 1);
        $this->db->order_by('goal_id','desc');
        $result = $this->db->get();
        $row = $result->result_array();
        for($i=0;$i<count($row);$i++){
              $row[$i]['goal_pic']=base_url('goal_pic/'.$row[$i]['goal_pic']);

              $this->db->select('*');
              $this->db->from('be_goal_like');
              $this->db->where('goal_id', $row[$i]['goal_id']);
              $stride = $this->db->get();
              $row[$i]['like'] = $stride->num_rows();

              $this->db->select('*');
              $this->db->from('be_goal_stride');
              $this->db->where('goal_id', $row[$i]['goal_id']);
              $stride = $this->db->get();
              $row[$i]['stride'] = $stride->result_array();
              $stride_completed=0;

              for($j=0;$j<count($row[$i]['stride']);$j++){
                if(strtotime($row[$i]['stride'][$j]['finish_line'])<strtotime("now")){
                 $stride_completed++;
                }
                $this->db->select('*');
                $this->db->from('be_goal_steps');
                $this->db->where('stride_id', $row[$i]['stride'][$j]['goal_stride_id']);
                $steps = $this->db->get();
                $row[$i]['stride'][$j]['steps'] = $steps->result_array();
              }
              $row[$i]['stride_count'] = $stride->num_rows();
              $row[$i]['stride_completed'] = $stride_completed;
        }
        $return=success_res("Get list of goals",200);
        $return['goal_list']=$row;
         return $return;
    }

    function get_goal_detail($goal_id){
        $this->db->select('*');
        $this->db->from('be_goal');
        $this->db->where('goal_id', $goal_id);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)==0){
            $return=error_res("Invalid goal id");
        }else{
           $row[0]['goal_pic']= base_url('goal_pic/'.$row[0]['goal_pic']);
           $return=$row[0];
        }
        return $return;
    }

    function edit_goal($params){


      
            if($params['goal_pic']!=""){

                       $this->db->select('goal_pic');
                       $this->db->from('be_goal');
                       $this->db->where('goal_id', $params['goal_id']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                       unlink("goal_pic/".$row[0]['goal_pic']);
            }   

       if($params['goal_name']!=""){$data['goal_name']=$params['goal_name']; }
        if($params['goal_pic']!=""){$data['goal_pic']=$params['goal_pic']; }
        if($params['finish_line']!=""){$data['finish_line']=date("Y-m-d h:i:s", strtotime($params['finish_line'])); }
        
         $data['updated']=created();
         $this->db->where('goal_id', $params['goal_id']);
         $this->db->where('user_id', $params['user_id']);
         $this->db->update('be_goal',$data);
         $return=success_res("Successfully update goat detail",200);       
         $return['goal_detail']=$this->get_goal_detail($params['goal_id']);
         return $return;
    }

    function delete_goal($params){
                       $this->db->select('goal_pic');
                       $this->db->from('be_goal');
                       $this->db->where('goal_id', $params['goal_id']);
                        $this->db->where('user_id', $params['user_id']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                       if(count($row)>0){
                         unlink("goal_pic/".$row[0]['goal_pic']); 
                        $this->db->where('goal_id', $params['goal_id']);
                        $this->db->delete('be_goal');
                         $query="UPDATE be_users SET goal_count = goal_count-1 WHERE user_id='".$params['user_id']."'";
                         $this->db->query($query);
                         $return=success_res("Successfully removed goal");
                       }else{
                             $return=error_res("Invalid goal id");
                       }
                       return $return;
                       
    }

    function get_goal_steps($params){
                       $this->db->select('goal_step_id,description,created,updated');
                       $this->db->from('be_goal_steps');
                       $this->db->where('goal_id', $params['goal_id']);
                       $this->db->where('user_id', $params['user_id']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                       $return=success_res("Get list of goal steps");
                        $return['goal_steps']=$row;
                       return $return;
    }

    function get_goal_step_detail($goal_step_id){
                       $this->db->select('*');
                       $this->db->from('be_goal_steps');
                       $this->db->where('goal_step_id', $goal_step_id);
                       $result = $this->db->get();
                       $row = $result->result_array();
                        if(count($row)==0){
            $return=error_res("Invalid goal step id");
        }else{
       
           $return=$row[0];
        }
        return $return;

    }

    function get_goal_stride_detail($goal_stride_id){
                       $this->db->select('*');
                       $this->db->from('be_goal_stride');
                       $this->db->where('goal_stride_id', $goal_stride_id);
                       $result = $this->db->get();
                       $row = $result->result_array();
                        if(count($row)==0){
            $return=error_res("Invalid goal stride id");
        }else{
       
           $return=$row[0];
        }
        return $return;

    }

    function add_goal_step($params){
            $data['user_id']=$params['user_id'];
            //$data['goal_id']=$params['goal_id'];
            $data['title']=$params['title'];
            $data['stride_id']=$params['stride_id'];
            $data['created']=created();
            $data['updated']=created();
            $this->db->insert('be_goal_steps',$data);
            $goal_step_id=$this->db->insert_id();           
            $return=success_res("Successfully added goal step",201);
            $return['goal_step_detail']=$this->get_goal_step_detail($goal_step_id);
            return $return;
    }

    function edit_goal_step($params){
         $data['title']=$params['title'];
          $data['updated']=created();

          $this->db->where('goal_step_id', $params['goal_step_id']);
          $this->db->where('user_id', $params['user_id']);
          $this->db->update('be_goal_steps',$data);
          $return=success_res("Successfully updated goal step",200);
          $return['goal_step_detail']=$this->get_goal_step_detail($params['goal_step_id']);
        return $return;
    }

    function delete_goal_step($params)
   {
          $this->db->where('goal_step_id', $params['goal_step_id']);
          $this->db->where('user_id', $params['user_id']);
          $this->db->delete('be_goal_steps');
          $return=success_res("Successfully removed goal step",200);
          return $return;
   }

   function add_goal_stride($params){
            $data['user_id']=$params['user_id'];
            $data['goal_id']=$params['goal_id'];
            $data['title']=$params['title'];
            $data['finish_line']=date("Y-m-d h:i:s", strtotime($params['finish_line']));
            $data['created']=created();
            $data['updated']=created();
            $this->db->insert('be_goal_stride',$data);
            $goal_stride_id=$this->db->insert_id(); 

             ///Start-Home Feed   
            $gdata=array();
            $gdata['action_type']=2; 
            $gdata['user_id']=$params['user_id']; 
            $gdata['goal_id']=$params['goal_id']; 
            $gdata['goal_stride_id']=$goal_stride_id; 
            $this->db->insert('be_home_feed',$gdata);
            /// End-Home Feed 


            $return=success_res("Successfully added goal stride",201);

            //$expload_step = explode(",", $params['steps']);
            //$params['steps'] = array('testing3','testing4');
            foreach($params['steps'] as $expload_step){
              $data1['user_id']=$params['user_id'];
              //$data['goal_id']=$params['goal_id'];
              $data1['title']=$expload_step;
              $data1['stride_id']=$goal_stride_id;
              $data1['created']=created();
              $data1['updated']=created();
              $this->db->insert('be_goal_steps',$data1);
            }
            $return['goal_stride_detail']=$this->get_goal_stride_detail($goal_stride_id);
            return $return;
    }

    function edit_goal_stride($params){
         $data['title']=$params['title'];
         $data['finish_line']=date("Y-m-d h:i:s", strtotime($params['finish_line']));
          $data['updated']=created();

          $this->db->where('goal_stride_id', $params['goal_stride_id']);
          $this->db->where('user_id', $params['user_id']);
          $this->db->update('be_goal_stride',$data);
          $return=success_res("Successfully updated goal stride",200);
          $return['goal_step_detail']=$this->get_goal_stride_detail($params['goal_stride_id']);

          //$expload_step = explode(",", $params['steps']);
          foreach($params['steps'] as $expload_step){
            $data1['user_id']=$params['user_id'];
            //$data['goal_id']=$params['goal_id'];
            $data1['title']=$expload_step;
            $data1['stride_id']=$params['goal_stride_id'];
            $data1['created']=created();
            $data1['updated']=created();
            $this->db->insert('be_goal_steps',$data1);
          }

        return $return;
    }

    function delete_goal_stride($params)
   {
          $this->db->where('goal_stride_id', $params['goal_stride_id']);
          $this->db->where('user_id', $params['user_id']);
          $this->db->delete('be_goal_stride');
          $return=success_res("Successfully removed goal stride",200);
          return $return;
   }

   function search_users($params){
                       $this->db->select('user_id,display_name,avatar,motto');
                       $this->db->from('be_users');
                       $this->db->where('display_name !=', '');
                       $this->db->where('user_id !=', $params['user_id']);
                       if($params['keyword']!=""){
                          $this->db->like('display_name', $params['keyword'],"both");
                       }
                       $this->db->limit($params['limit'],$params['offset']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                       for($i=0;$i<count($row);$i++){
                                    $row[$i]['avatar']=base_url('avatar/'.$row[$i]['avatar']);
                       }
                       $return=success_res("Search result of users");
                       $return['users']=$row;
                     
                       return $return;
   }

   function add_goal_team($params){
      $team_memebers=explode(",", $params['team_memeber']);

      for($i=0;$i<count($team_memebers);$i++){

                       $this->db->select('goal_team_id');
                       $this->db->from('be_goal_team');
                       $this->db->where('user_id',$team_memebers[$i]);
                       $this->db->where('goal_id',$params['goal_id']);                   
                       $result = $this->db->get();
                       $row = $result->result_array();

                       if(count($row)==0){
                           $goal_team=array();
                           $goal_team['goal_id']=$params['goal_id'];
                           $goal_team['user_id']=$team_memebers[$i];
                           $goal_team['created']=created();
                           $goal_team['updated']=created();
                         $this->db->insert('be_goal_team',$goal_team);
                       }
           
      }
       $return=success_res("Successfully added goal memebers");
       return $return;

   }

   function get_goal_team($params){

                       $this->db->select('bu.user_id,bu.display_name,bu.avatar,bu.motto,bgt.approval_status');
                       $this->db->from('be_goal_team as bgt');
                       $this->db->join('be_users as bu','bu.user_id=bgt.user_id','LEFT');
                       $this->db->where('bu.user_id !=', null);
                       $this->db->where('bgt.goal_id',$params['goal_id']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                       for($i=0;$i<count($row);$i++){
                                    $row[$i]['avatar']=base_url('avatar/'.$row[$i]['avatar']);
                       }
                       $return=success_res("Get goal memebers");
                       $return['users']=$row;
                       return $return;
   }

   function send_message_to_goal($params){
                           $data=array();
                           $data['goal_id']=$params['goal_id'];
                           $data['user_id']=$params['user_id'];
                           $data['message_type']=$params['message_type'];
                           $data['message']=$params['message'];
                           $data['created']=created();
                           $data['updated']=created();
                           $this->db->insert('be_group_msg',$data);
                            $return=success_res("Successfully added goal memebers");
                           return $return;
   }


   function get_goal_message($params){
                       $this->db->select('*');
                       $this->db->from('be_group_msg');
                       $this->db->where('goal_id',$params['goal_id']);
                       if($params['msg_id']!=""){
                         $this->db->where('msg_id > ',$params['msg_id']);
                       }
                        $this->db->order_by('msg_id','desc');
                       $this->db->limit($params['limit'],$params['offset']);
                       $result = $this->db->get();
                       $row = $result->result_array();
                        for($i=0;$i<count($row);$i++){
                          if( $row[$i]['message_type']==2){
                           $row[$i]['message']=base_url('msg_media/'.$params['goal_id']."/".$row[$i]['message']); 
                          }
                                    
                       }
                       $return=success_res("Get goal message ");
                       $return['messages']=$row;
                       return $return;
   }


  function get_stride_data($params){
    $this->db->select('*');
    $this->db->from('be_goal_stride');
    $this->db->where('goal_id',$params['goal_id']);
  //  $this->db->where('user_id',$params['user_id']);
    
    $result = $this->db->get();
    $row = $result->result_array();

    if(count($row)>0){
      for($i=0;$i<count($row);$i++){
        $this->db->select('*');
        $this->db->from('be_goal_steps');
        $this->db->where('stride_id', $row[$i]['goal_stride_id']);
        $steps = $this->db->get();
        $row[$i]['steps'] = $steps->result_array();
      }
      $return=success_res("Get stride data");
      $return['data']=$row;
    }
    else{
      $return=error_res("no stride data found");
    }
    
    return $return;
  }


  function get_stride_step($params){
    $this->db->select('*');
    $this->db->from('be_goal_stride');
    $this->db->where('goal_stride_id',$params['goal_stride_id']);
    $this->db->where('user_id',$params['user_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
        $this->db->select('*');
        $this->db->from('be_goal_steps');
        $this->db->where('stride_id', $row['goal_stride_id']);
        $this->db->where('user_id', $row['user_id']);
        $steps = $this->db->get();
        $row['steps'] = $steps->result_array();

      $return=success_res("Get stride data");
      $return['data']=$row;
    }
    else{
      $return=error_res("no stride data found");
    }
    
    return $return;
  }


  function goal_like($params){
    if($params['is_like']==1){
     $this->db->select('like_id');
    $this->db->from('be_goal_like');
    $this->db->where('goal_id',$params['goal_id']);
    $this->db->where('user_id',$params['user_id']);
    $this->db->limit(1);
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
      $return=error_res("goal already liked.");
    }
    else{
      $data['goal_id'] = $params['goal_id'];
      $data['user_id'] = $params['user_id'];
      $data['created']=created();
      $this->db->insert('be_goal_like',$data);
      $id = $this->db->insert_id();

           ///Start-Home Feed   
            $gdata=array();
            $gdata['action_type']=3; 
            $gdata['user_id']=$params['user_id']; 
            $gdata['goal_id']=$params['goal_id']; 
            $this->db->insert('be_home_feed',$gdata);
            /// End-Home Feed 

      $return=success_res("goal liked successfully.");
      $return['id'] = $id;
    }

    }else{
     $this->db->select('like_id');
    $this->db->from('be_goal_like');
    $this->db->where('goal_id',$params['goal_id']);
    $this->db->where('user_id',$params['user_id']);
    $this->db->limit(1);
    $result = $this->db->get();
    $row = $result->row_array();
     if(count($row)>0){

     $this->db->where('like_id',$row[0]['like_id']);
     $this->db->delete('be_home_feed');


     $this->db->where('goal_id',$params['goal_id']);
     $this->db->where('user_id',$params['user_id']);
     $this->db->where('action_type',3);
     $this->db->delete('be_home_feed');
       $return=success_res("Successfully removed goal like.");


     }

    }


    
    
    return $return;
  }


  function user_profile($params){
    $this->db->select('*');
    $this->db->from('be_users');
    $this->db->where('user_id',$params['user_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
      $return=success_res("Get User data.");
      $return['data']=$row;
      $return['data']['avatar']=base_url('avatar/'.$row['avatar']);
    }
    else{
      $return=error_res("user not found.");
    }
    
    return $return;
  }


  function user_list($params){
    $this->db->select('*');
    $this->db->from('be_users');
    $this->db->where('user_id !=', $params['user_id']);
    if($params['limit']!="" && $params['offset']){

     $this->db->limit($params['limit'],$params['offset']);
    }else{
      $this->db->limit(20);
    }
  //  
    
    $result = $this->db->get();
    $row = $result->result_array();

    if(count($row)>0){
      $return=success_res("Get User data.");
      $return['data']=$row;

      for($i=0;$i<count($row);$i++){
        $return['data'][$i]['avatar']=base_url('avatar/'.$return['data'][$i]['avatar']);
      }
    }
    else{
      $return=error_res("user not found.");
    }
    
    return $return;
  }


  function user_list_with_contact($params){
    $this->db->select('*');
    $this->db->from('be_users');
    $this->db->where_in('email_id',explode(",", $params['email_list']));
    
    $result = $this->db->get();
    $row = $result->result_array();

    if(count($row)>0){
      $return=success_res("Get User data.");
      $return['data']=$row;

      for($i=0;$i<count($row);$i++){
        $return['data'][$i]['avatar']=base_url('avatar/'.$return['data'][$i]['avatar']);
      }
    }
    else{
      $return=error_res("user not match with your contact.");
    }
    
    return $return;
  }


  function goal_detail($params){
    $this->db->select('*');
    $this->db->from('be_goal');
    $this->db->where('goal_id',$params['goal_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
      $return=success_res("Goal data.");
      $return['data']=$row;

      $return['data']['goal_pic']=base_url('goal_pic/'.$return['data']['goal_pic']);

      $this->db->select('*');
      $this->db->from('be_goal_like');
      $this->db->where('goal_id', $return['data']['goal_id']);
      $stride = $this->db->get();
      $return['data']['like'] = $stride->num_rows();

      $this->db->select('*');
      $this->db->from('be_goal_stride');
      $this->db->where('goal_id', $return['data']['goal_id']);
      $stride = $this->db->get();
      $return['data']['stride'] = $stride->result_array();
      $stride_completed=0;

      for($j=0;$j<count($return['data']['stride']);$j++){
        if(strtotime($return['data']['stride'][$j]['finish_line'])<strtotime("now")){
          $stride_completed++;
        }
        $this->db->select('*');
        $this->db->from('be_goal_steps');
        $this->db->where('stride_id', $return['data']['stride'][$j]['goal_stride_id']);
        $steps = $this->db->get();
        $return['data']['stride'][$j]['steps'] = $steps->result_array();
      }
      $return['data']['stride_count'] = $stride->num_rows();
      $return['data']['stride_completed'] = $stride_completed;

    }
    else{
      $return=error_res("Goal not found.");
    }
    
    return $return;
  }


  function send_friend_request($params){


    $to_user_id=explode(",", $params['to_user_id']);


    for($i=0;$i<count($to_user_id);$i++){


    $this->db->select('friend_id');
    $this->db->from('be_friends');
    $this->db->where(' (from_user_id="'.$params['user_id'].' " AND to_user_id="'.$to_user_id[$i].'"  ) OR (to_user_id="'.$params['user_id'].' " AND from_user_id="'.$to_user_id[$i].'") AND (status="1" or status="0") ',null,false);
    $this->db->limit(1);
    $result = $this->db->get();
    $row = $result->result_array();


    if(count($row)==0){

          $data['from_user_id']=$params['user_id'];
          $data['to_user_id']=$to_user_id[$i];
          $data['created']=created();
          $data['updated']=created();
          $this->db->insert('be_friends',$data);

          $user_detail=$this->get_user($params['user_id']);

            $message['alert']=$user_detail['display_name']." send friend request";
    $message['sound']="default";
    $message['type']=0;
    $message['badge']=0;


          $devicetoken=$this->get_user_device_token($to_user_id[$i]);
          for($t=0;$t<count($devicetoken);$t++){
            push_notification_ios($devicetoken[$t]['token'],$message);
          }
    }

    }
    
    
  $return=success_res("sent successfully freind request.");
    return $return;
  }

  function get_user_device_token($user_id){
       $this->db->select('token');
    $this->db->from('be_device_token');
    $this->db->where('user_id',$user_id);
    $result = $this->db->get();
    $row = $result->row_array();
    return $row;
  }


  function acc_dec_teammate_req($params){
    $this->db->select('*');
    $this->db->from('be_goal_team');
    $this->db->where('goal_team_id',$params['goal_team_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
      $data['approval_status'] = $params['status'];
      $this->db->where('goal_team_id', $params['goal_team_id']);
      $this->db->update('be_goal_team',$data);

      if($params['status']==1){
        $return=success_res("Team mate request accepted.");
      }
      else{
        $return=success_res("Team mate request declined.");
      }
    }
    else{
      $return=error_res("Goal Team id not found.");
    }
    return $return;
  }


  function get_teammate_req_list($params){
    $this->db->select('*');
    $this->db->from('be_users');
    $this->db->where('user_id',$params['user_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
      $this->db->select('*');
      $this->db->from('be_goal');
      $this->db->where('user_id', $params['user_id']);

      $result = $this->db->get();
      $goal = $result->result_array();
      //$data['goal']=$goal;
      if(count($goal)>0){
        for($i=0;$i<count($goal);$i++){
          $this->db->select('*');
          $this->db->from('be_goal_team');
          $this->db->where('goal_id', $goal[$i]['goal_id']);
          $result = $this->db->get();
          $team = $result->result_array();
          for($j=0;$j<count($team);$j++){
            $this->db->select('*');
            $this->db->from('be_users');
            $this->db->where('user_id', $team[$j]['user_id']);
            $result = $this->db->get();
            $user = $result->row_array();
            $team[$j]['user']=$user;
          }
          $goal[$i]['team']=$team;
        }
      }
      $return=success_res("Team mate request list.");
      $return['data']=$goal;
    }
    else{
      $return=error_res("user not found or invalid.");
    }
    return $return;
  }


  function acc_dec_friend_req($params){
    $this->db->select('*');
    $this->db->from('be_friends');
    $this->db->where('friend_id',$params['friend_id']);
    
    $result = $this->db->get();
    $row = $result->row_array();

    if(count($row)>0){
    

      if($params['status']==1){
          $data['status'] = $params['status'];
      $this->db->where('friend_id', $params['friend_id']);
      $this->db->update('be_friends',$data);


             $message=array();
            $user_detail=$this->get_user($row[0]['to_user_id']);
            $message['alert']=$user_detail['display_name']." accepted friend request";
            $message['sound']="default";
            $message['type']=0;
            $message['badge']=0;
            $devicetoken=$this->get_user_device_token($row[0]['from_user_id']);
            for($t=0;$t<count($devicetoken);$t++){
            push_notification_ios($devicetoken[$t]['token'],$message);
             }


      $return=success_res("friend request accepted.");
      }
      else{
          
      $data['status'] = $params['status'];
      $this->db->where('friend_id', $params['friend_id']);
      $this->db->update('be_friends',$data);

            $message=array();
            $user_detail=$this->get_user($row[0]['to_user_id']);
            $message['alert']=$user_detail['display_name']." rejected friend request";
            $message['sound']="default";
            $message['type']=0;
            $message['badge']=0;
            $devicetoken=$this->get_user_device_token($row[0]['from_user_id']);
            for($t=0;$t<count($devicetoken);$t++){
            push_notification_ios($devicetoken[$t]['token'],$message);
             }
        $return=success_res("friend request declined.");
      }
    }else{
      $return=error_res("Invalid friend_id");
    }
    return $return;
  }

  function invite_user($params){
    $user_id=explode(",", $params['invite_user_id_list']);


    for($i=0;$i<count($user_id);$i++){
      $this->db->select('goal_invitation_id');
    $this->db->from('be_goal_invitation');
    $this->db->where('user_id',$user_id[$i]);
    $this->db->where('goal_id',$params['goal_id']);
    $result = $this->db->get();
    $row = $result->row_array();
    if(count($row)==0){
      $data=array();
      $data['goal_id']=$params['goal_id'];
      $data['user_id']=$user_id[$i];
      $data['owner_id']=$params['owner_id'];
      $data['status']=0;
      $data['created']=created();
      $data['updated']=created();
     
      $this->db->insert('be_goal_invitation',$data);
    }


    }
    $return=success_res("Successfully invited user ");
    return $return;

  }

  function invitation_list($params){
   $this->db->select('bgi.goal_id,bgi.goal_invitation_id,bg.goal_name,bg.goal_pic,bgi.status,bgi.created,bgi.updated,u.display_name,u.avatar,u.motto');
      $row=array();
      //$this->db->select('*');
    $this->db->from('be_goal_invitation as bgi');
    $this->db->join('be_goal as bg', 'bgi.goal_id = bg.goal_id', 'left');
     $this->db->join('be_users as u','bgi.owner_id=u.user_id','left');
    $this->db->where('bgi.user_id',$params['user_id']);
    $this->db->order_by('bgi.status','asc');
    $result = $this->db->get();
    $row = $result->result_array();

    for($i=0;$i<count($row);$i++){
      if($row[$i]['goal_pic']!="") {
        $row[$i]['goal_pic']= base_url('goal_pic/'.$row[$i]['goal_pic']);
      }
      $row[$i]['avatar']=base_url('avatar/'.$row[$i]['avatar']);
    }
    $return=success_res("invitation list");
    $return['invitation']=$row;
     
   
    return $return;
  }

  function accept_invitation($params){
    $data['status'] = 1;
      $this->db->where('goal_invitation_id', $params['goal_invitation_id']);
      $this->db->update('be_goal_invitation',$data);
      $return=success_res("successfully accpted invitation");
      return $return;
  }

  function reject_invitation($params){

      $this->db->where('goal_invitation_id', $params['goal_invitation_id']);
      $this->db->delete('be_goal_invitation');
      $return=success_res("successfully rejected invitation");
      return $return;
  }

  function friend_list($params){
    $this->db->select('bf.friend_id,bf.status,bfrom.user_id as from_user_id,bfrom.email_id as from_email,bfrom.display_name as from_display_name,bfrom.avatar as from_avatar,bfrom.motto as from_motto,bto.user_id as to_user_id,bto.email_id as to_email,bto.display_name as to_display_name,bto.avatar as to_avatar,bto.motto as to_motto');
   

    // $this->db->select('*');
    $this->db->from('be_friends as bf');
    $this->db->join('be_users as bfrom','bf.from_user_id=bfrom.user_id','LEFT');
   $this->db->join('be_users as bto','bf.to_user_id=bto.user_id','LEFT');
    $this->db->where(' (bf.from_user_id="'.$params['user_id'].' " OR bf.to_user_id="'.$params['user_id'].'") AND  bf.status="1" ',null,false);
    $this->db->order_by('bf.friend_id','desc');
    $result = $this->db->get();
    $row = $result->result_array();

$return_row=array();

    for($i=0;$i<count($row);$i++){
       $row[$i]['from_avatar']=base_url('avatar/'.$row[$i]['from_avatar']);
        $row[$i]['to_avatar']=base_url('avatar/'.$row[$i]['to_avatar']);

      if($row[$i]['from_user_id']==$params['user_id']){
           
             $return_row[$i]['user_id']=$row[$i]['to_user_id'];
             $return_row[$i]['email']=$row[$i]['to_email'];
             $return_row[$i]['display_name']=$row[$i]['to_display_name'];
             $return_row[$i]['avatar']=$row[$i]['to_avatar'];
             $return_row[$i]['motto']=$row[$i]['to_motto'];
       }else{
             $return_row[$i]['user_id']=$row[$i]['from_user_id'];
             $return_row[$i]['email']=$row[$i]['from_email'];
             $return_row[$i]['display_name']=$row[$i]['from_display_name'];
             $return_row[$i]['avatar']=$row[$i]['from_avatar'];
             $return_row[$i]['motto']=$row[$i]['from_motto'];
      }

        
     }


     $return=success_res("successfully get friendlist");
     $return['friendlist']=$return_row;
     return $return;







  }

  function notification($params){
  $this->db->select('bf.friend_id,bf.status,bto.user_id as to_user_id,bto.email_id as to_email,bto.display_name as to_display_name,bto.avatar as to_avatar,bto.motto as to_motto,bf.status');
   

    // $this->db->select('*');
    $this->db->from('be_friends as bf');
   // $this->db->join('be_users as bfrom','bf.from_user_id=bfrom.user_id','LEFT');
   $this->db->join('be_users as bto','bf.from_user_id=bto.user_id','LEFT');
    $this->db->where(' (bf.to_user_id="'.$params['user_id'].'" AND bf.status="0" AND bto.user_id is not null) ',null,false);
    $this->db->order_by('bf.friend_id','desc');
    $result = $this->db->get();
    $row = $result->result_array();

    $return_row=array();

    for($i=0;$i<count($row);$i++){
       //$row[$i]['from_avatar']=base_url('avatar/'.$row[$i]['from_avatar']);
        $row[$i]['to_avatar']=base_url('avatar/'.$row[$i]['to_avatar']);

      //if($row[$i]['from_user_id']==$params['user_id']){
           
             $return_row[$i]['friend_id']=$row[$i]['friend_id'];
             $return_row[$i]['status']=$row[$i]['status'];
           //  $return_row[$i]['send_by_me']=1;
             $return_row[$i]['user_id']=$row[$i]['to_user_id'];
             $return_row[$i]['email']=$row[$i]['to_email'];
             $return_row[$i]['display_name']=$row[$i]['to_display_name'];
             $return_row[$i]['avatar']=$row[$i]['to_avatar'];
             $return_row[$i]['motto']=$row[$i]['to_motto'];
             $return_row[$i]['status']=$row[$i]['status'];
      //  }else{
      //        $return_row[$i]['friend_id']=$row[$i]['friend_id'];
      //        $return_row[$i]['user_id']=$row[$i]['from_user_id'];
      //        // $return_row[$i]['send_by_me']=0;
      //        $return_row[$i]['email']=$row[$i]['from_email'];
      //        $return_row[$i]['display_name']=$row[$i]['from_display_name'];
      //        $return_row[$i]['avatar']=$row[$i]['from_avatar'];
      //        $return_row[$i]['motto']=$row[$i]['from_motto'];
      //        $return_row[$i]['status']=$row[$i]['status'];
      // }

        
     }
     $return=success_res("successfully get notification");
     $return['otheraction']=$return_row;


     $this->db->select('bf.friend_id,bf.status,bfrom.user_id as from_user_id,bfrom.email_id as from_email,bfrom.display_name as from_display_name,bfrom.avatar as from_avatar,bfrom.motto as from_motto,bf.status');
   

    // $this->db->select('*');
    $this->db->from('be_friends as bf');
    $this->db->join('be_users as bfrom','bf.to_user_id=bfrom.user_id','LEFT');
   //$this->db->join('be_users as bto','bf.to_user_id=bto.user_id','LEFT');
    $this->db->where(' (bf.from_user_id="'.$params['user_id'].' " AND bfrom.user_id is not null ) ',null,false);
    $this->db->order_by('bf.friend_id','desc');
    $result = $this->db->get();
    $row = $result->result_array();

    $return_row=array();

    for($i=0;$i<count($row);$i++){
       $row[$i]['from_avatar']=base_url('avatar/'.$row[$i]['from_avatar']);
       // $row[$i]['to_avatar']=base_url('avatar/'.$row[$i]['to_avatar']);

      // if($row[$i]['from_user_id']==$params['user_id']){
           
      //        $return_row[$i]['friend_id']=$row[$i]['friend_id'];
      //        $return_row[$i]['status']=$row[$i]['status'];
      //      //  $return_row[$i]['send_by_me']=1;
      //        $return_row[$i]['user_id']=$row[$i]['to_user_id'];
      //        $return_row[$i]['email']=$row[$i]['to_email'];
      //        $return_row[$i]['display_name']=$row[$i]['to_display_name'];
      //        $return_row[$i]['avatar']=$row[$i]['to_avatar'];
      //        $return_row[$i]['motto']=$row[$i]['to_motto'];
      //        $return_row[$i]['status']=$row[$i]['status'];
      //  }else{
             $return_row[$i]['friend_id']=$row[$i]['friend_id'];
             $return_row[$i]['user_id']=$row[$i]['from_user_id'];
             // $return_row[$i]['send_by_me']=0;
             $return_row[$i]['email']=$row[$i]['from_email'];
             $return_row[$i]['display_name']=$row[$i]['from_display_name'];
             $return_row[$i]['avatar']=$row[$i]['from_avatar'];
             $return_row[$i]['motto']=$row[$i]['from_motto'];
             $return_row[$i]['status']=$row[$i]['status'];
     // }

        
     }
      $return['myaction']=$return_row;


     
     return $return;
  }


  function userDetail($params){

                      $this->db->select('goal_id,goal_name');
                       $this->db->from('be_goal');
                       $this->db->where('user_id', $params['user_id']);
                       $this->db->where('goal_type', 1);
                       $result = $this->db->get();
                       $goal_list = $result->result_array();

                       $this->db->select('bf.friend_id,bf.status,bfrom.user_id as from_user_id,bfrom.email_id as from_email,bfrom.display_name as from_display_name,bfrom.avatar as from_avatar,bfrom.motto as from_motto,bto.user_id as to_user_id,bto.email_id as to_email,bto.display_name as to_display_name,bto.avatar as to_avatar,bto.motto as to_motto');
   

    // $this->db->select('*');
    $this->db->from('be_friends as bf');
    $this->db->join('be_users as bfrom','bf.from_user_id=bfrom.user_id','LEFT');
   $this->db->join('be_users as bto','bf.to_user_id=bto.user_id','LEFT');
    $this->db->where(' (bf.from_user_id="'.$params['user_id'].' " OR bf.to_user_id="'.$params['user_id'].'") AND  bf.status="1" ',null,false);
    $this->db->order_by('bf.friend_id','desc');
    $result = $this->db->get();
    $row = $result->result_array();

$return_row=array();

    for($i=0;$i<count($row);$i++){
       $row[$i]['from_avatar']=base_url('avatar/'.$row[$i]['from_avatar']);
        $row[$i]['to_avatar']=base_url('avatar/'.$row[$i]['to_avatar']);

      if($row[$i]['from_user_id']==$params['user_id']){
           
             $return_row[$i]['user_id']=$row[$i]['to_user_id'];
             $return_row[$i]['email']=$row[$i]['to_email'];
             $return_row[$i]['display_name']=$row[$i]['to_display_name'];
             $return_row[$i]['avatar']=$row[$i]['to_avatar'];
             $return_row[$i]['motto']=$row[$i]['to_motto'];
       }else{
             $return_row[$i]['user_id']=$row[$i]['from_user_id'];
             $return_row[$i]['email']=$row[$i]['from_email'];
             $return_row[$i]['display_name']=$row[$i]['from_display_name'];
             $return_row[$i]['avatar']=$row[$i]['from_avatar'];
             $return_row[$i]['motto']=$row[$i]['from_motto'];
      }

        
     }


   $this->db->select('bf.goal_invitation_id,bg.goal_name,bfrom.user_id,bfrom.email_id,bfrom.display_name,bfrom.avatar,bfrom.motto');
    $this->db->from('be_goal_invitation as bf');
    $this->db->join('be_users as bfrom','bf.user_id=bfrom.user_id','LEFT');
    $this->db->join('be_goal as bg','bg.goal_id=bf.goal_id','LEFT');
    $this->db->where(' (bf.owner_id="'.$params['user_id'].' " ) AND  bf.status="1" ',null,false);
    $this->db->order_by('bf.goal_invitation_id','desc');
    $result = $this->db->get();
    $assistant = $result->result_array();
    for($i=0;$i<count($assistant);$i++){
      $assistant[$i]['avatar']=base_url('avatar'. $assistant[$i]['avatar']);
    }

     $return=success_res("successfully get userDetail");
     $return['goal_list']=$goal_list;
     $return['friendlist']=$return_row;
     $return['assistant']=$assistant;
     return $return;






  }

  function home_feed($params){


     $this->db->select('from_user_id,to_user_id');
     $this->db->from('be_friends as bf');
     $this->db->where(' (bf.from_user_id="'.$params['user_id'].' " OR bf.to_user_id="'.$params['user_id'].'") AND  bf.status="1" ',null,false);
          $result = $this->db->get();
        $friends = $result->result_array();

          $friend_id=array();
        if(count($friends)>0){
            for($i=0;$i<count($friends);$i++){
              if($friends[$i]['from_user_id']==$params['user_id']){
                   $friend_id[count($friend_id)]=$friends[$i]['to_user_id'];
              }else{
                 $friend_id[count($friend_id)]=$friends[$i]['from_user_id'];
              }
            }
        }
         $friend_id2=$friend_id;

         $friend_id[count($friend_id)]=$params['user_id'];




     $this->db->select('bg.goal_id,bg.goal_type,bhf.action_type,bhf.user_id as action_user_id,au.display_name as action_display_name,bg.goal_name,bg.goal_pic,bg.finish_line,bg.goal_category_id,bgc.goal_category,bg.created,bg.updated,bu.user_id,bu.display_name,bu.avatar');
        $this->db->from('be_home_feed as bhf');
        ///$this->db->from('be_goal as bg');
        $this->db->join('be_goal as bg','bg.goal_id=bhf.goal_id','LEFT');
        $this->db->join('be_goal_category as bgc','bg.goal_category_id=bgc.goal_category_id','LEFT');
        //$this->db->join('be_shoutouts_goal as bsg','bsg.goal_id=bg.goal_id','LEFT');
        $this->db->join('be_users as bu','bu.user_id=bg.user_id','LEFT');
        $this->db->join('be_users as au','bu.user_id=bhf.user_id','LEFT');

        $this->db->where_in('bhf.user_id', $friend_id);
        $this->db->group_by('bhf.home_feed_id');
      // $this->db->or_where_in('bsg.user_id', $friend_id2);
        $this->db->order_by('bhf.home_feed_id','desc');
        $this->db->limit($params['limit'],$params['offset']);
        $result = $this->db->get();
        $row = $result->result_array();

         for($i=0;$i<count($row);$i++){
          if($row[$i]['goal_pic']!=""){
             $row[$i]['goal_pic']=base_url('goal_pic/'.$row[$i]['goal_pic']);
          }

          if($row[$i]['goal_stride_id']!=0){
          $this->db->select('title');
          $this->db->from('be_goal_stride');
          $this->db->where('goal_stride_id',$row[$i]['goal_stride_id']);
          $this->db->limit(1);
          $result = $this->db->get();
          $stride = $result->row_array();
          if(count($stride)>0){
                   $stride_name=$stride[0]['title'];
          }
          }else{
            $stride_name="";
          }

          $row[$i]['stride_name']=$stride_name;

        
             
          $row[$i]['avatar']=base_url('avatar/'.$row[$i]['avatar']);


          $this->db->select('like_id');
          $this->db->from('be_goal_like');
          $this->db->where('goal_id',$row[$i]['goal_id']);
          $this->db->where('user_id',$params['user_id']);
          $this->db->limit(1);
          $result = $this->db->get();
          $like = $result->row_array();
          if(count($like)>0){
            $is_like="1";
          }else{
            $is_like="0";
          }
          $row[$i]['is_liked']=$is_like;

              
        }
        $return=success_res("Get list of goals",200);
        $return['goal_list']=$row;
         return $return;
  
  }

  function add_inspiration($params){
   // if($params['goal_pic']==""){
   //          $params['goal_pic']="default.jpg";
   //      }

            $data['user_id']=$params['user_id'];
            $data['goal_type']=2;
            $data['goal_name']=$params['goal_name'];
            $data['goal_pic']=$params['goal_pic'];
            $data['finish_line']=created();
            $data['goal_category_id']=1;
            $data['created']=created();
            $data['updated']=created();
            
            $this->db->insert('be_goal',$data);
            $goal_id=$this->db->insert_id();

            $gdata=array();
            $gdata['action_type']=5; 
            $gdata['user_id']=$params['user_id']; 
            $gdata['goal_id']=$goal_id; 
            $this->db->insert('be_home_feed',$gdata);

              mkdir('msg_media/'.$goal_id, 0777, true);
            // $query="UPDATE be_users SET goal_count = goal_count+1 WHERE user_id='".$params['user_id']."'";
            //  $this->db->query($query);
     
            $return=success_res("Create inspiration successfully",201);
            $return['goal_detail']=$this->get_goal_detail($goal_id);
            return $return; 
  }

  function shoutouts_goal($params){
     ///Start-Home Feed   
            $gdata=array();
            $gdata['action_type']=4; 
            $gdata['user_id']=$params['user_id']; 
            $gdata['goal_id']=$params['goal_id']; 
            $this->db->insert('be_home_feed',$gdata);
            /// End-Home Feed 
            
           // $this->db->insert('be_shoutouts_goal',$data);
            $return=success_res("Successfully shoutout goal");
            return $return;
  }

  function send_group_msg($params){
    $data['goal_id']=$params['goal_id'];
    $data['user_id']=$params['user_id'];
    $data['type']=$params['type'];
    $data['msg']=$params['msg'];
    $data['created']=created();
    $this->db->insert('be_group_message',$data);
    $return=success_res("Successfully sent group message");
    return $return;

  }

  function recieve_msg($params){


        $this->db->select('bgm.msg_id,bgm.type,bgm.msg,bgm.created,bu.user_id,bu.display_name,bu.avatar');
        $this->db->from('be_group_message as bgm');
        $this->db->join('be_users as bu','bgm.user_id=bu.user_id','LEFT');
        $this->db->where('bgm.goal_id', $params['goal_id']);
        $this->db->limit($params['limit'],$params['offset']);
        $this->db->order_by('bgm.msg_id','desc');
        $result = $this->db->get();
        $row = $result->result_array();
        for($i=0;$i<count($row);$i++){
          if($row[$i]['type']==1){
            $row[$i]['msg']=base_url("msg_media/".$params['goal_id']."/".$row[$i]['msg']);
          }
            $row[$i]['avatar']=base_url('avatar/'.$row[$i]['avatar']);
        }
        $row=array_reverse($row);
      

        $return = success_res("Successfully get message",200);
        $return['chat'] = $row;
        return $return;
  }

  function update_device_token($params){

        $this->db->select('device_token_id');
        $this->db->from('be_device_token');
        $this->db->where('user_id', $params['user_id']);
        $this->db->where('token', $params['token']);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();


        if(count($row)==0){


    $data['user_id']=$params['user_id'];
    $data['token']=$params['token'];
    $data['created']=created();
    $data['updated']=created();
    $this->db->insert('be_device_token',$data);


        }



     
    $return=success_res("Successfully saved device token");
    return $return;
  }




}
