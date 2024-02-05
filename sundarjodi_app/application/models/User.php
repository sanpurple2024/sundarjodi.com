<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Model {

    public function __construct() {
        parent::__construct();
        
        // Load the database library
        $this->load->database();
        
        $this->userTbl = 'user_register';
        $this->contactTbl = 'contact_info';
        $this->habbitTbl = 'personal_habits';
        $this->familyTbl = 'family_information';
        $this->eduworkTbl = 'education_work';
        $this->horoscopeTbl = 'horoscope_details';
        $this->partnerexpTbl = 'partner_expection';
    }

    /*
     * Get rows from the users table
     */
    function getRows($params = array()){
        $this->db->select('*');
        $this->db->from($this->userTbl);
        
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("id",$params)){
            $this->db->where('id',$params['id']);
            $query = $this->db->get();
            $result = $query->row_array();
        }else{
            //set start and limit
            if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
                $this->db->limit($params['limit'],$params['start']);
            }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
                $this->db->limit($params['limit']);
            }
            
            if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
                $result = $this->db->count_all_results();    
            }elseif(array_key_exists("returnType",$params) && $params['returnType'] == 'single'){
                $query = $this->db->get();
                $result = ($query->num_rows() > 0)?$query->row_array():false;
            }else{
                $query = $this->db->get();
                $result = ($query->num_rows() > 0)?$query->result_array():false;
            }
        }

        //return fetched data
        return $result;
    }
    
    /*
     * Insert user data
     */
    public function insert($userData){
        
        //insert user data to users table
        $insert = $this->db->insert($this->userTbl, $userData);
        
        //return the status
        return $insert?$this->db->insert_id():false;
     //  return $insert;
    }
    
 /*******************************************************************/
 
  public function personal_data_update($personalData, $id){

        //update user data in users table
        $update = $this->db->update($this->userTbl, $personalData, array('id'=>$id));
        return $update?true:false;
    }
    
     public function contact_data_update($contactData, $id){

        //update user data in users table
        $update = $this->db->update($this->contactTbl, $contactData, array('id'=>$id));
        return $update?true:false;
    }
    
     public function habbit_data_update($habbitData, $id){

        //update user data in users table
        $update = $this->db->update($this->habbitTbl, $habbitData, array('id'=>$id));
        return $update?true:false;
    }
    
    
    public function family_data_update($personalData, $id){

        //update user data in users table
        $update = $this->db->update($this->familyTbl, $personalData, array('id'=>$id));
        return $update?true:false;
    }
    
      public function education_work_data_update($personalData, $id){

        //update user data in users table
        $update = $this->db->update($this->eduworkTbl, $personalData, array('id'=>$id));
        return $update?true:false;
    }
    
      public function horoscope_data_update($horoscopeData, $id){

        //update user data in users table
        $updateh = $this->db->update($this->horoscopeTbl, $horoscopeData, array('id'=>$id));
        return $updateh?true:false;
    }
    
      public function partnerexp_data_update($partnerexpData, $id){

        //update user data in users table
        $updateh = $this->db->update($this->partnerexpTbl, $partnerexpData, array('id'=>$id));
        return $updateh?true:false;
    }
    
    
 /********************************************************************/
    public function update($data, $id){
        //add modified date if not exists
        if(!array_key_exists('modified', $data)){
            $data['modified'] = date("Y-m-d H:i:s");
        }
        
        //update user data in users table
        $update = $this->db->update($this->userTbl, $data, array('id'=>$id));
        
        //return the status
        return $update?true:false;
    }
    
    /*
     * Delete user data
     */
    public function delete($id){
        //update user from users table
        $delete = $this->db->delete('users',array('id'=>$id));
        //return the status
        return $delete?true:false;
    }

//=====================================================================//
  public function fcm($registatoin_ids, $notification,$device_type){

       $url = 'https://fcm.googleapis.com/fcm/send';
      if($device_type == "Android"){
            $fields = array(
                'to' => $registatoin_ids,
                'data' => $notification
            );
      } else {
            $fields = array(
                'to' => $registatoin_ids,
                'notification' => $notification
            );
      }
      // Firebase API Key
      $headers = array('Authorization:key=AAAAUTy3jOM:APA91bHUUeesvyaqco6WtQ0zhuK2iVDfTVybfNKd62IgFOVBl-D5mrbKT8Z7L4DLE2sSBmxz1uhHJlR2z27oI5dCX7bT4SWQaLvo9GrBQsA1BrD4-ZMG-GZAmtYMhxCVdNmKko74aqcQ','Content-Type:application/json');
  
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
      $result = curl_exec($ch);
    //   var_dump($result);die;
      if ($result === FALSE) {
          die('Curl failed: ' . curl_error($ch));
      }else{
          return $result;
          
      }
      curl_close($ch);
      
    }
    
//=====================================================================//
   function getUsers($postData=null){

     $response = array();

     ## Read value
     $draw = $postData['draw'];
     $start = $postData['start'];
     $rowperpage = $postData['length']; // Rows display per page
     $columnIndex = $postData['order'][0]['column']; // Column index
     $columnName = $postData['columns'][$columnIndex]['data']; // Column name
     //$columnSortOrder = $postData['order'][0]['dir']; // asc or desc
     $searchValue = $postData['search']['value']; // Search value

     // Custom search filter 
     $searchstatus = $postData['searchstatus'];
     $searchGender = $postData['searchGender'];
      $searchads = $postData['searchads'];
    $date_frm_regs = $postData['date_frm_regs'];
     $date_to_regs = $postData['date_to_regs'];
     $date_frm = $postData['date_frm'];
     $date_to = $postData['date_to'];
     //$searchName = $postData['searchName'];

     ## Search 
     $search_arr = array();
     $searchQuery = "";
     if($searchValue != ''){
        $search_arr[] = " (first_name like '%".$searchValue."%' or 
         email like '%".$searchValue."%' or 
         mobile like'%".$searchValue."%' or 
         profile like'%".$searchValue."%' or 
         id like'%".$searchValue."%' or 
         admin_userid like'%".$searchValue."%' or 
         created_user like'%".$searchValue."%' or 
         caste like'%".$searchValue."%' or 
         gender like'%".$searchValue."%' or
         ads like'%".$searchValue."%' or
         martial_status like'%".$searchValue."%' ) ";
         
     }
     if($searchstatus != ''){
        $search_arr[] = " status='".$searchstatus."' ";
     }
     if($searchGender != ''){
        $search_arr[] = " gender='".$searchGender."' ";
     }
     if($searchads != ''){
        $search_arr[] = " ads='".$searchads."' ";
     }
      if($date_frm != ''){
        $search_arr[] = " login_session  >='".$date_frm."' ";
     }
      if($date_to != ''){
        $search_arr[] = " login_session <='".$date_to."' ";
     }
     
if($date_frm_regs != ''){
        $search_arr[] = " created_user  >='".date('Y-m-d', strtotime($date_frm_regs))."' ";
     }
      if($date_to_regs != ''){
        $search_arr[] = " created_user <='".date('Y-m-d', strtotime($date_to_regs))."' ";
     }
     if(count($search_arr) > 0){
        $searchQuery = implode(" and ",$search_arr);
     }

     ## Total number of records without filtering
     $this->db->select('count(*) as allcount');
     $records = $this->db->get('user_register')->result();
     $totalRecords = $records[0]->allcount;

     ## Total number of record with filtering
     $this->db->select('count(*) as allcount');
     if($searchQuery != '')
     $this->db->where($searchQuery);
     $records = $this->db->get('user_register')->result();
     $totalRecordwithFilter = $records[0]->allcount;

     ## Fetch records
     $this->db->select('*');
     if($searchQuery != '')
     $this->db->where($searchQuery);
     $this->db->order_by('created_user', 'desc');
    //  $this->db->order_by('unread', 'desc');
     $this->db->limit($rowperpage, $start);
     $records = $this->db->get('user_register')->result();

     $data = array();

          foreach($records as $record ){
            if($record->status == 1)$newstatus = '<a style="font-size:11px" class="badge badge-pill badge-success">A</a>';
            else if($record->status == 0)$newstatus = '<a style="font-size:11px" class="badge badge-pill badge-warning">InA</a>';
            else if($record->status == 3)$newstatus = '<a style="font-size:11px" class="badge badge-pill badge-danger">DeA</a>';
            
           
            $query = $this->db->query("SELECT *  FROM profile_images where reg_id = '$record->id' ");
             $noimges = $query->num_rows();
             
               $this->db->select("*");
                $this->db->from('memberships');  
                $this->db->where('member_profile_id',$record->profile ); 
                $querypci = $this->db->get();
                foreach($querypci->result() as $row_pf_2)
                {
               $payment_mode =  $row_pf_2->payment_mode;
                }
             
            // $query = $this->db->query("SELECT *  FROM profile_images where reg_id = '$record->id' limit 1");
            // foreach($querypci->result() as $row_pf_2)
            //     {
            //   $img_id =  $row_pf_2->reg_id;
            //   $img_file_name =  $row_pf_2->file_name;
            //     }
            // $imgfile = '<img src="'.base_url.'">';
            
             $query1 = $this->db->query("SELECT *  FROM interest where profile_id = '$record->id' AND reject = 0 AND accept = 0 group by logged_user_id");
              $Recieved = $query1->num_rows();
              $query3 = $this->db->query("SELECT *  FROM interest where profile_id = '$record->id' AND reject = 0 AND accept = 1 group by logged_user_id");
              $accept = $query3->num_rows();
              
              $query2 = $this->db->query("SELECT *  FROM interest where logged_user_id = '$record->id' AND reject = 0 AND accept = 0 group by profile_id");
                 $sent = $query2->num_rows();
              
             
               
              if($record->unread == 1){
                  $unr = '<i class="fa fa-eye-slash text-center"></i>';
                  $unread = 'style="background-color:#D6DBDF;padding:5px;display:block;width:100% ! importnat;height:100% ! importnat;"';
              }else{
                  $unr = '<i class="fa fa-eye text-center"></i>';
                 $unread = 'style="background-color:#fff;padding:5px;display:block;width:100% ! importnat;height:100% ! importnat;"';  
              }  
              
         
        $data[] = array( 
           "id"=>'<span '.$unread.'>'.$record->id.'<br>&nbsp;</span>',
           "status"=> '<span '.$unread.'>'.$newstatus.'<br>&nbsp;&nbsp;</span>',
            "gender"=>'<span '.$unread.'>'.$record->gender.'<br>&nbsp;&nbsp;</span>',
             "login_session"=>'<span '.$unread.'>'.date("d-m-Y", strtotime($record->login_session)).'<br>'.date("H:i:s", strtotime($record->login_session)).'</span>',
             "caste"=>'<span '.$unread.'>'.$record->caste.'<br>&nbsp;&nbsp;</span>',
             "account"=>'<span '.$unread.'><a class="badge badge-pill badge-info" target="_blank" href="https://sundarjodi.com/welcome/master_login/'.$record->mobile.'"><i class="fa fa-user"></i></a><br>&nbsp;&nbsp;</span>',
           "profile"=>'<span '.$unread.'><b>'.$record->first_name.'</b><br> <a target="_blank" href="'.site_url('Welcome/profile_view/'.$record->id.'/profile/'.$record->profile).'">'.$record->profile.'</a></span>',
           
           "contact"=>'<span '.$unread.'> Mobile: '.$record->mobile.'<br> Email: '.$record->email.'</span>',
           "creatby"=>$record->admin_userid,
           "interest"=>'<span style="padding:5px">Reciv: '.$Recieved.'<br>Accept: '.$accept.'<br>Sent: '.$sent.'</span>',
           "creaton"=>'<span style="padding:5px">'.date("d-m-Y", strtotime($record->created_user)).'<br>'.date("H:i:s", strtotime($record->created_user)).'</span>',
           "delete"=>'<span style="padding:5px"><a  target="_blank" href="'.site_url('Welcome/upload_img/'.$record->id).'" class="badge badge-primary">'.$noimges.' <i class="fa fa-file-image-o"></i></a> &nbsp;
           <a  target="_blank" href="'.site_url('Welcome/edit_prof_id/'.$record->id.'/profile/'.$record->profile).'" class="badge badge-info"><i class="fa fa-edit"></i></a> &nbsp;
           
            <a class="badge badge-danger" href="'.site_url('Welcome/delete_prof_id/'.$record->id).'"><i class="fa fa-trash"></i></a></span>',
            
            "mail"=>'<span '.$unread.'><input type="checkbox" name="mail_reguser[]" class="checkitem_email" value="'.$record->id.'"><br>&nbsp;</span>',
            "sms"=>'<span '.$unread.'><input type="checkbox" name="sms_reguser[]" class="checkitem" id="smsreg" value="'.$record->id.'"><br>&nbsp;</span>',
             "read_unread"=>$unr,
        ); 
       
     }

     $response = array(
       "draw" => intval($draw),
       "iTotalRecords" => $totalRecords,
       "iTotalDisplayRecords" => $totalRecordwithFilter,
       "aaData" => $data
     );

     return $response; 
   }
/**********************************************************************/
}