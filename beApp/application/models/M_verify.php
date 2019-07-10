
<?php
class M_verify extends CI_Model {


   

    function process($code){
        $this->db->select('user_id');
        $this->db->from('be_users');
        $this->db->where('verify_code', $code);
        $this->db->where('is_active', 0);
        $this->db->limit(1);
        $result = $this->db->get();
        $row = $result->result_array();
        if(count($row)>0){
 
        	 $data['is_active']=1;
        	 $this->db->where('user_id', $row[0]['user_id']);
        	 $this->db->update('be_users',$data);



        	$return=success_res("Successfully verified the account.");
        }else{
        	$return=error_res("Your verification link was expired.");
        }
        return $return;
    }

}