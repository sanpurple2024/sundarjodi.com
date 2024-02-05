<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Load the Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Authentication extends REST_Controller {

    public function __construct() { 
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        // Load the user model
        $this->load->model('User_model_api_3');
        // $this->load->library('sendgrid_lib');
    }
    
    
    function send_mail_curl($emailArray=array()){
	    
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($emailArray),
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer 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",
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 008ded4b-4756-c41d-3a9e-2edfe032acce"
          ),
        ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}
    
    
    function set_profile_photo_post(){
        
        $user_id = $this->input->post("user_id");
        $file_name = $this->input->post("file_name");
        
        if($user_id==""){
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide user id',
                ], REST_Controller::HTTP_OK);
        }
        
        if($file_name==""){
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide file name',
                ], REST_Controller::HTTP_OK);
        }
        $response = $this->db->query("SELECT * FROM profile_images WHERE reg_id= '$user_id' AND main_pic='1'");
        $files = $response->result();
        $file_data['main_pic'] = '1';
        
        if(empty($files)){
            $update = $this->db->update("profile_images", $file_data, array('reg_id'=>$user_id,'file_name'=>$file_name));
        }else{
            
            $update = $this->db->update("profile_images", array("main_pic"=>'0'), array('reg_id'=>$user_id));
            $this->db->update("profile_images", $file_data, array('reg_id'=>$user_id,'file_name'=>$file_name));
        }
        
        if($update){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong',
                ], REST_Controller::HTTP_OK);
        }
        
        
        
    }
    
    function validity_pkg_post(){
        
        $memberships_profileid = $this->input->post("profile");
        
        $response = $this->db->query("SELECT * FROM memberships WHERE member_profile_id= '$memberships_profileid' ");
        $banners = $response->result();
        
        $arr = array();
        if(!empty($banners)){
      
             $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$banners
                ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
    function get_banners_post(){
        
        $response = $this->db->query("SELECT * FROM banners WHERE is_active = '1' ");
        $banners = $response->result();
        
        
        $arr = array();
        if(!empty($banners)){
            
            foreach($banners AS $val){
                $arr[] = array(
                    "title"=>$val->title,
                    "image"=>"image/banners/".$val->image,
                    "redirect_url"=>$val->redirect_url,
                    );
            }
            
             $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$arr
                ], REST_Controller::HTTP_OK);
        }
    }
    
    
    function return_biodata_post(){
        $response = $this->db->query("SELECT * FROM biodata_api WHERE status = '1' ");
        $biodata = $response->result();
        
        
        $arr = array();
        if(!empty($biodata)){
            
            
            foreach($biodata AS $val){
                $arr[] = array(
                    "file_name_download"=>"img/biodata/blank/".$val->file_name_download,
                    "file_name_thumbnail"=>"img/biodata/text/".$val->file_name_thumbnail,
                    "type"=>$val->type,
                    );
            }
            
             $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$arr
                ], REST_Controller::HTTP_OK);
        }
    }
    
    function what_looking_for_post(){
        
        $logged_user_id = $this->input->post("logged_user_id");
        $another_profile_id = $this->input->post("another_profile_id");
        
        if($logged_user_id!="" && $another_profile_id!=""){
             
              $queryw2 = $this->db->query("SELECT * FROM partner_expection WHERE reg_profil_id = '$logged_user_id' ");
              $response = $queryw2->result();
              
              $query_another_profile = $this->db->query("SELECT * FROM user_register WHERE profile = '$another_profile_id' ");
              $response_other_profile = $query_another_profile->result();
              
              if(empty($response_other_profile)){
                  	$this->response([
                    'status' => FALSE,
                    'message' => 'Another profile id is not found',
                ], REST_Controller::HTTP_OK);
              }
              
              
              
              $arr = array();
              if(!empty($response)){
                  
                  //Another profile data
                  
                   $today = date('Y-m-d');
                   $diff = date_diff(date_create($response_other_profile[0]->dob), date_create($today));
                   
	               $partner_age = $diff->format('%y'); 
	               $partner_height = $response_other_profile[0]->height;
	               $partner_martial_status = $response_other_profile[0]->martial_status;
	               $partner_caste = $response_other_profile[0]->caste;
	               
	               
	                $fetch_city = $this->db->query("SELECT * FROM contact_info WHERE reg_profil_id = '$another_profile_id' ");
                    $fetch_city_another = $fetch_city->result();
                    $fetch_diet = $this->db->query("SELECT * FROM personal_habits WHERE reg_profil_id = '$another_profile_id' ");
                    $fetch_diet_another = $fetch_diet->result();
                    $fetch_education_work = $this->db->query("SELECT * FROM education_work WHERE reg_profil_id = '$another_profile_id' ");
                    $fetch_highest_education_another = $fetch_education_work->result();
                    
                    $partner_diet = (!empty($fetch_diet_another)) ? $fetch_diet_another[0]->diet : "";
                    $partner_smooking = (!empty($fetch_diet_another)) ? $fetch_diet_another[0]->smooking : "";
                    $partner_liv_city = (!empty($fetch_city_another)) ? $fetch_city_another[0]->perm_city : "";
                    $partner_highest_education= (!empty($fetch_highest_education_another)) ? $fetch_highest_education_another[0]->highest_education : "";
                    $partner_education_field= (!empty($fetch_highest_education_another)) ? $fetch_highest_education_another[0]->education_field : "";
                    $partner_occup = (!empty($fetch_highest_education_another)) ? $fetch_highest_education_another[0]->occup : "";
                  
                  
                  //current logged in profile partner data
                  $age_from = $response[0]->age_from;
                  $age_to = $response[0]->age_to;
                  $height_from = $response[0]->height_from;
                  $height_to = $response[0]->height_to;
                  $marital_status = $response[0]->marital_status;
                  $caste = $response[0]->caste;
                  $liv_city = $response[0]->liv_city;
                  $diet = $response[0]->diet;
                  $highest_education = $response[0]->highest_education;
                  $education_field = $response[0]->education_field;
                  $occup = $response[0]->occup;
                  $smooking = $response[0]->smooking;
                  
                  
                  //condition check
                  
                  $final_age =($partner_age >= $age_from && $partner_age <= $age_to) ? "Yes" : "No";
                  $final_height =($partner_height >= $height_from && $partner_height <= $height_to) ? "Yes" : "No";
                  $final_marital_status = ($marital_status==$partner_martial_status) ? "Yes" :"No";
                  $final_caste = ($caste==$partner_caste) ? "Yes" :"No";
                  $final_living_city = ($liv_city=="Any" || $liv_city==$partner_liv_city) ? "Yes" :"No";
                  $final_diet = ($diet=="Any" || $diet==$partner_diet) ? "Yes" :"No";
                  $final_highest_education = ($highest_education=="Any" || $highest_education==$partner_highest_education) ? "Yes" :"No";
                  $final_education_field = ($education_field=="Any" || $education_field==$partner_education_field) ? "Yes" :"No";
                  $final_occupation = ($occup=="Any" || $occup==$partner_occup) ? "Yes" :"No";
                  $final_smooking = ($smooking=="Any" || $smooking==$partner_smooking) ? "Yes" :"No";
                  
                  //count check 
                  
                  $cnt_age =($partner_age >= $age_from && $partner_age <= $age_to) ? "1" : "0";
                  $cnt_height =($partner_height >= $height_from && $partner_height <= $height_to) ? "1" : "0";
                  $cnt_marital_status = ($marital_status==$partner_martial_status) ? "1" : "0";
                  $cnt_caste = ($caste==$partner_caste) ? "1" : "0";
                  $cnt_living_city = ($liv_city=="Any" || $liv_city==$partner_liv_city) ? "1" : "0";
                  $cnt_diet = ($diet=="Any" || $diet==$partner_diet) ? "1" : "0";
                  $cnt_highest_education = ($highest_education=="Any" || $highest_education==$partner_highest_education) ? "1" : "0";
                  $cnt_education_field = ($education_field=="Any" || $education_field==$partner_education_field) ? "1" : "0";
                  $cnt_occupation = ($occup=="Any" || $occup==$partner_occup) ? "1" : "0";
                  $cnt_smooking = ($smooking=="Any" || $smooking==$partner_smooking) ? "1" : "0";
                  
                  $total_count = $cnt_age+$cnt_height+$cnt_marital_status+$cnt_caste+$cnt_living_city+$cnt_diet+$cnt_highest_education+$cnt_education_field+$cnt_occupation+$cnt_smooking;
                  
                  $arr[] = array(
                      "age"=>$final_age,
                      'height'=>$final_height,
                      'marital_status'=>$final_marital_status,
                      'community'=>$final_caste,
                      'living_city'=>$final_living_city,
                      'diet'=>$final_diet,
                      'final_highest_education'=>$final_highest_education,
                      'education_field'=>$final_education_field,
                      'working'=>$final_occupation,
                      'smooking'=>$final_smooking,
                      'count'=>$total_count
                      
                      );
              }
             $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$arr
                ], REST_Controller::HTTP_OK);
            
        }else{
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    function payment_success_post(){
        
        $payment_id = $data['payment_id'] = $this->input->post("payment_id");
        $member_profile_id = $data['member_profile_id'] = $this->input->post("member_profile_id");
        $total_alloted_contact = $data['total_profiles_alloted'] = $this->input->post("total_profiles_alloted");
        $package_id = $data['package_id'] = $this->input->post("package_id");
        $gender = $data['gender'] = $this->input->post("gender");
        $package_name = $this->input->post("package_name");
        $data['assign_to'] = "rupali";
        $data['remaining_profiles'] = $data['topup_status'] = "0";
        $data['payment_mode'] = "Paid";
        $data['status'] = "1";
        
        if($payment_id==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'payment id is required',
                ], REST_Controller::HTTP_OK);
        }
        
        if($member_profile_id==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Member profile id is required',
                ], REST_Controller::HTTP_OK);
        }
        if($total_alloted_contact==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Total profiles alloted is required',
                ], REST_Controller::HTTP_OK);
        }
        if($package_id==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Package id is required',
                ], REST_Controller::HTTP_OK);
        }
        if($gender==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Gender is required',
                ], REST_Controller::HTTP_OK);
        }
        if($package_name==""){
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Package Name is required',
                ], REST_Controller::HTTP_OK);
        }
        
        
        $last_date = date("Y-m-d");
        
                    if(trim($package_name)=="1 Year"){
                        $inc_date = date("Y-m-d", strtotime("+1 year", strtotime($last_date))); //inc 1 month
                    }
                    if(trim($package_name)=="6 Month"){
                        $inc_date = date("Y-m-d", strtotime("+6 month", strtotime($last_date))); //inc one years
                    }
                    
                    if(trim($package_name)=="1 Month"){
                        $inc_date = date("Y-m-d", strtotime("+1 month", strtotime($last_date))); //inc 3 month
                    }
                    
                     if(trim($package_name)=="Personalized Diamond"){
                        $inc_date = date("Y-m-d", strtotime("+1 year", strtotime($last_date))); //inc 3 month
                        
                    }
                     if(trim($package_name)=="Personalized Services"){
                        $inc_date = date("Y-m-d", strtotime("+1 year", strtotime($last_date))); //inc 3 month
                        
                    }
                    
                    
        $data['package_validity'] = date("Y-m-d H:i:s",strtotime($inc_date));
        $data['payment_status'] = "Success";
        $update = $this->User_model_api_3->update_memberships($data);
      
   
        $payment_history = array(
             "price"=>$package_id,
             "total_alloted_contact"=>$total_alloted_contact,
             "from_date"=>date('Y-m-d'),
             "to_date"=>$inc_date,
             "profile_id"=>$member_profile_id,
             "payment_mode"=>"Pay_Gateway",
             "payment_id"=>$payment_id,
            );
        $insert = $this->db->insert('payment_history', $payment_history); 
        
        
        if($insert){
        
           $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
                
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong',
                ], REST_Controller::HTTP_OK);
        }
        
        
    }
    
    function get_education_field_post(){
        
        $query = $this->db->get('education_filed');
        $result = $query->result();
        
        if(!empty($result)){
            	$this->response([
                    'status' => TRUE,
                    'total'=>count($result),
                    'data'=>$result,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
            	$this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
        
    }
    
    function favourite_profiles_listing_post(){
        
        $reg_id =  $data['user_logged_id'] = $this->input->post('reg_id');
        $data['gender'] = ($this->input->post('gender')=="F") ? "M" :"F";
        $data['limit'] = $this->input->post("limit");
        $data['offset'] = ($this->input->post("offset")!="") ? $this->input->post("offset") : 0;
        
        if($reg_id==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'reg_id is required',
                ], REST_Controller::HTTP_OK);
        }
        if($data['limit']==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'Limit is required',
                ], REST_Controller::HTTP_OK);
        }
        if($data['offset']==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'Offset is required',
                ], REST_Controller::HTTP_OK);
        }
        
        if($reg_id!="" && $data['gender']!=""){
           $fav_data = $this->User_model_api_3->get_favourites($data);
           
        //   echo $this->db->last_query();die;
           
         
         
         $datas = array();
         if($fav_data){
             
             foreach($fav_data AS $key){
                  $userprofile_id = $key->profile_id;
                  $datas[] = $userprofile_id;
             }
           
             
                if(!empty($datas)){
             
                    $valuesArray =$datas;// explode(',', $int_id);
                    $cleanedValues = array_map(function($value) {
                    $value = trim($value);
                    $value = stripslashes($value);
                         return "'" . addslashes($value) . "'"; 
                    }, $valuesArray);
                        
                    $finalString = implode(',', $cleanedValues);
                        
                }else{
                    $finalString ="";
                }  
            
             
             $current_logged_data = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString,"gender"=>$data['gender']));
           
               
                $arg = array();
                if(!empty($current_logged_data)){
                    
                    $today = date('Y-m-d');
                     
                    foreach($current_logged_data AS $reg){
                        
                         $diff = date_diff(date_create($reg->dob), date_create($today));
                         $age = $diff->format('%y');
                         
                        $string = "'"; 
                        $position = '1'; 
                       $height_cal = substr_replace( $reg->height, $string, $position, 0 )." ft";
                       //calculate height
                        $inputs = $reg->height ;
                        if(isset($inputs[1]) && $inputs[1] === '0') {
                            $inputs[1] = "'";
                        }
                        $string = "'"; 
                        $position = '1'; 
                        $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                        $ht_cal = str_replace("''","'",$heights);
                   //   ...................................
                        
                        $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$reg->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc'); 
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        
                        
                        $this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$reg_id);
                	    $this->db->where('profile_id',$reg->regester_id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$reg_id);
                	    $this->db->where('profile_id',$reg->regester_id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";

            		
            		$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$reg->profile,"payment_mode"=>"Paid"));
             
                       
                        $arg[] = array(
                            'id'=>$reg->regester_id,
                            "profile"=>$reg->profile,
                            "age"=>$age,
                            "height"=>$ht_cal,
                            "martial_status"=>$reg->martial_status,
                            'profession'=>$reg->occup,
                            "education"=>$reg->education_field,
                            "caste"=>$reg->caste,
                            "city"=>$reg->perm_city,
                            "file_name"=>$file,
                            'interest'=>$interest,
                            'favourite'=>$favourite,
                            "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                            );
                        
                        
                    }
                 
           
                    
                    $this->response([
                        'status' => TRUE,
                         'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
                    
                  
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
                }
             
             
         }else{
             	$this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
         }
        }else{
            	$this->response([
                    'status' => FALSE,
                    'message' => 'Please provide reg id & gender',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    function get_live_city_post(){
        
        $query = $this->db->get('district');
        $result = $query->result();
        
        if(!empty($result)){
            	$this->response([
                    'status' => TRUE,
                    'total'=>count($result),
                    'data'=>$result,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
            	$this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    
    
    function favourite_unfavorite_post(){
        
        $action =  $this->input->post('action');
        $profile=$data['profile_id'] =$this->input->post("profile"); //current logged user
        $current_logged_data = $this->User_model_api_3->get_new_macthed_data($data);
        
        $partner_profile= $args['profile_id'] = $this->input->post("partner_profile_id"); //partner id
        $partner_profile_data = $this->User_model_api_3->get_new_macthed_data($args);
        
        if($profile!="" && $partner_profile!="" && $action!=""){
        
                if($action == 'fav'){
               
                $subject = "Profile Added to Your Favorites on Sundar Jodi";
                $msg = "Dear ".ucwords($partner_profile_data[0]->first_name).",<br/><br/>
        
                        Congratulations! You've just added a profile to your favorites on Sundar Jodi, your trusted matrimonial platform. This small step could lead to a beautiful connection. Best of luck in your search for love!<br/><br/>
                        
                        Warm regards,<br/>
                        Sundar Jodi Team";
        
        
                     $data_cont = array(
                        'profile_id' => $partner_profile_data[0]->id,
                        'user_logged_id' => $current_logged_data[0]->id,
                        'created_date' => date('Y-m-d H:i:s'),
                     );
                    $this->db->insert('favourites', $data_cont); 
          
                $data_notify = array(
                    'logged_user' => $partner_profile_data[0]->profile,
                    'second_user' => $current_logged_data[0]->profile,
                    'date_created' => date('Y-m-d'),
                     'read_unread' => 0,
                    'action' => 'add_fav'
                     );
                 $notifications =   $this->db->insert('notifications', $data_notify); 
                 
                 
                $send_mail['form_name'] = 'SundarJodi';
                $send_mail['form'] = 'help@sundarjodi.com';
                $send_mail['message'] = $msg;
                $send_mail['to'] = $partner_profile_data[0]->email;
                $send_mail['subject'] =$subject;
                $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                  "from" => ["email" => "help@sundarjodi.com"],
                  "subject" => $send_mail['subject'], 
                  "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                $this->send_mail_curl($emailArray);
                 
                  $this->response([
                    'status' => TRUE,
                    'message' => 'Favourite Successfull',
                ], REST_Controller::HTTP_OK);
                 
         
                }else if($action == 'unfav'){
                   
                $subject = "Profile Removed from Your Favorites on Sundar Jodi";
                $msg = "Dear ".ucwords($partner_profile_data[0]->first_name).",<br/><br/>
        
                        This is to confirm that you've successfully removed a profile from your favorites on Sundar Jodi. We understand that your preferences may change, and we're here to support you in your search for the perfect match. Feel free to continue exploring profiles on Sundar Jodi, and we wish you the best on your journey to find love.<br/><br/>
                        
                        Warm regards,<br/>
                        Sundar Jodi Team";
                        
                        
                        
                        
            
                        
                    $this->db->select("profile_id,user_logged_id,id");
            		$this->db->from('favourites');  
            		$this->db->where('user_logged_id',$current_logged_data[0]->id );
            		$this->db->where('profile_id',$partner_profile_data[0]->id ); 
            		$queryf = $this->db->get();
            	
            		    foreach($queryf->result() as $rowf){
            		
                    	   $f_int_id =  $rowf->id;
                           $this->db->where('id', $f_int_id);
                           $this->db->delete('favourites');
                		}
                		
        		$this->db->select("*");
        		$this->db->from('notifications');  
        		$this->db->where('logged_user',$partner_profile_data[0]->profile );
        		$this->db->where('second_user',$current_logged_data[0]->profile ); 
        		
        		$queryn = $this->db->get();
        		foreach($queryn->result() as $rown){
        		    
        		      $notify_id =  $rown->id;
        		      $this->db->where('id', $notify_id);
                      $this->db->delete('notifications');
        		}
        		
        	
        		$send_mail['form_name'] = 'SundarJodi';
                $send_mail['form'] = 'help@sundarjodi.com';
                $send_mail['message'] = $msg;
                $send_mail['to'] = $partner_profile_data[0]->email;
                $send_mail['subject'] =$subject;
                $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                  "from" => ["email" => "help@sundarjodi.com"],
                  "subject" => $send_mail['subject'], 
                  "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                $this->send_mail_curl($emailArray);
        		
        		
        		$this->response([
                    'status' => TRUE,
                    'message' => 'Unfavourite Successfull',
                ], REST_Controller::HTTP_OK);
                
            }
            
            
            if(!empty($partner_profile_data)){
                
               
             
                
            
                //     $send_mail['form_name'] = 'Sundarjodi';
                //     $send_mail['form'] = 'help@sundarjodi.com';
                //     $send_mail['message'] = $msg;
                //     $send_mail['to'] = $partner_profile_data[0]->email;
                //     $send_mail['subject'] = $subject;
              
                //     $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                //       "from" => ["email" => "help@sundarjodi.com"],
                //       "subject" => $send_mail['subject'], 
                //       "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
            
                //   $curl = curl_init();
                //       curl_setopt_array($curl, array(
                //       CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
                //       CURLOPT_RETURNTRANSFER => true,
                //       CURLOPT_ENCODING => "",
                //       CURLOPT_MAXREDIRS => 10,
                //       CURLOPT_TIMEOUT => 30,
                //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //       CURLOPT_CUSTOMREQUEST => "POST",
                //       CURLOPT_POSTFIELDS => json_encode($emailArray),
                //       CURLOPT_HTTPHEADER => array(
                //         "authorization: Bearer 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",
                //         "cache-control: no-cache",
                //         "content-type: application/json",
                //         "postman-token: 008ded4b-4756-c41d-3a9e-2edfe032acce"
                //   ),
                // ));
                
                // $response = curl_exec($curl);
                // $err = curl_error($curl);
                // curl_close($curl);
    
            }
    
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
        }
    
    }
    
 //-----------------------------------------------------------------------------
 
     function user_interest_post(){
         
         $action =  $this->input->post('action');
        $profile=$data['profile_id'] =$this->input->post("profile"); //current logged user
        $current_logged_data = $this->User_model_api_3->get_new_macthed_data($data);
        
        $partner_profile= $args['profile_id'] = $this->input->post("partner_profile_id"); //partner id
        $partner_profile_data = $this->User_model_api_3->get_new_macthed_data($args);
        
        
        if($profile!="" && $partner_profile!="" && $action!=""){  
            
            
               
    if($action == 'intr'){
        
             $data_cont = array(
                'profile_id' => $partner_profile_data[0]->id,
                'logged_user_id' => $current_logged_data[0]->id,
                 'sent' => 1,
                'sent_date' => date('Y-m-d H:i:s'),
             );
 
            $interest =   $this->db->insert('interest', $data_cont); 
   
            // $data = array(
            //     'interest_receive_user_id' => $post_id,
            //     'interest_send_user_id' => $userlogin_id,
            //     'action' => $action,
            //     'sent_date' => date('Y-m-d H:i:s'),
            // );
         
          $this->db->select("*");
          $this->db->from('notifications');  
          $this->db->where('logged_user',$partner_profile_data[0]->profile );
          $this->db->where('second_user',$current_logged_data[0]->profile );
          $this->db->where('action','interest_recive' );
          $queryrw = $this->db->get();    
          
        if ($queryrw->num_rows() > 0){}else{  
            
            $data_notify = array(
            'logged_user' => $partner_profile_data[0]->profile,
            'second_user' => $current_logged_data[0]->profile,
            'date_created' => date('Y-m-d'),
             'read_unread' => 0,
            'action' => 'interest_recive'
             );
             $notifications =   $this->db->insert('notifications', $data_notify); 
        } 
        
        
        $receive_username = $partner_profile_data[0]->first_name;
        $receive_profile = $partner_profile_data[0]->profile;
        $sender_profile = $current_logged_data[0]->profile;
        
        $sender_caste =  $current_logged_data[0]->caste;
        $sender_martial_status = $current_logged_data[0]->martial_status;
        
        $get_education_details = $this->User_model_api_3->get_education_details(array("reg_id"=>$partner_profile[0]->id));
        
        $highest_education = (!empty($get_education_details) && $get_education_details[0]->highest_education!="") ? $get_education_details[0]->highest_education : "";
        $education_field = (!empty($get_education_details) && $get_education_details[0]->education_field!="") ? $get_education_details[0]->education_field : "";
        $occup = (!empty($get_education_details) && $get_education_details[0]->occup!="") ? $get_education_details[0]->occup : "";
        
        $msg = "Dear ".$receive_username." (".$receive_profile."), <br/><br/>
          ".$sender_profile." viewed your profile and shown interest 
                to communicate with you further. Please view ".$sender_profile." 
                profile and confirm your decision of accept/decline. <br/><br/>
                
                  <table style='border:0;width: 100%;'>
        <tr>
          
             <td style='border:0;width:70%;'>
               <span style='font-size:14px;color:#34495E;'> Caste: $sender_caste</span><br>
                <span style='font-size:14px;color:#34495E;'> Marital Status: $sender_martial_status</span><br>
                 <span style='font-size:14px;color:#34495E;'> Education: $highest_education($education_field)</span><br>
                  <span style='font-size:14px;color:#34495E;'> Occupation: $occup</span><br>
            </td>
            
        </tr>
        
        
    </table><br/><br/>
                
                Best Wishes<br/>
                Team sundarjodi";
        
                //     $send_mail['form_name'] = 'Sundarjodi';
                //     $send_mail['form'] = 'help@sundarjodi.com';
                //     $send_mail['message'] = $msg;
                //     $send_mail['to'] = $partner_profile_data[0]->email;
                //     $send_mail['subject'] = "New interest Received";
              
                //     $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                //       "from" => ["email" => "help@sundarjodi.com"],
                //       "subject" => $send_mail['subject'], 
                //       "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
            
                //   $curl = curl_init();
                //       curl_setopt_array($curl, array(
                //       CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
                //       CURLOPT_RETURNTRANSFER => true,
                //       CURLOPT_ENCODING => "",
                //       CURLOPT_MAXREDIRS => 10,
                //       CURLOPT_TIMEOUT => 30,
                //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //       CURLOPT_CUSTOMREQUEST => "POST",
                //       CURLOPT_POSTFIELDS => json_encode($emailArray),
                //       CURLOPT_HTTPHEADER => array(
                //         "authorization: Bearer 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",
                //         "cache-control: no-cache",
                //         "content-type: application/json",
                //         "postman-token: 008ded4b-4756-c41d-3a9e-2edfe032acce"
                //   ),
                // ));
                
                // $response = curl_exec($curl);
                // $err = curl_error($curl);
               
                // curl_close($curl);
                
                $send_mail['form_name'] = 'SundarJodi';
                $send_mail['form'] = 'help@sundarjodi.com';
                $send_mail['message'] = $msg;
                $send_mail['to'] = $partner_profile_data[0]->email;
                $send_mail['subject'] ="New interest Received";
                $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                  "from" => ["email" => "help@sundarjodi.com"],
                  "subject" => $send_mail['subject'], 
                  "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                $this->send_mail_curl($emailArray);
        
        	$this->response([
                    'status' => TRUE,
                    'message' => 'Interest send successful',
                ], REST_Controller::HTTP_OK);
        
        
      }else if($action == 'unintr'){
          
        $this->db->select("*");
		$this->db->from('interest');  
		$this->db->where('logged_user_id',$current_logged_data[0]->id );
		$this->db->where('profile_id',$partner_profile_data[0]->id ); 
		$queryf = $this->db->get();
		foreach($queryf->result() as $rowf){
		
		      $f_int_id =  $rowf->id;
		      $this->db->where('id', $f_int_id);
              $this->db->delete('interest');
		}	
		
    		 $this->db->select("*");
    		 $this->db->from('notifications');  
    		 $this->db->where('logged_user',$partner_profile_data[0]->profile );
    		 $this->db->where('second_user',$current_logged_data[0]->profile ); 
    		 $queryn = $this->db->get();
    		foreach($queryn->result() as $rown){
    		      $notify_id =  $rown->id;
    		      $this->db->where('id', $notify_id);
                  $this->db->delete('notifications');
    		}
    		
    		
    			$this->response([
                    'status' => TRUE,
                    'message' => 'uninterest Successfull',
                ], REST_Controller::HTTP_OK);
    		
        }else{
              $this->response([
                    'status' => FALSE,
                    'message' => 'This action is not found',
                ], REST_Controller::HTTP_OK);
        }
            
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
        }
         
     }
     
//-------------------------------------------------------------------------
     
     function education_wise_profile_post(){
        
          $data['highest_education'] =  $highest_education = $this->input->post("highest_education");
         $profile =  $data['profile'] = $this->input->post('profile');
         $gender = $this->input->post('gender');
         $user_id = $this->input->post("user_id");
         
          
         
         if($user_id==""){
              $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide user id',
                ], REST_Controller::HTTP_OK);
         }
         
         if($highest_education!="" && $profile!="" && $gender!=""){
             
             
             $partner_data = $this->User_model_api_3->get_partner_data(array("profile"=>$profile));
             
             
             if(!empty($partner_data)){
                 
                 $data['gender'] = ($gender=="F") ? "M" : "F";
                 $data['marital_sts'] = $partner_data[0]->marital_status;
                 $data['height_from'] = $partner_data[0]->height_from;
                 $data['height_to'] = $partner_data[0]->height_to;
                 $data['age_from'] =  $partner_data[0]->age_from;
                 $data['age_to'] =  $partner_data[0]->age_to;
                
                 $caste = $partner_data[0]->caste;
                $valuesArray = explode(',', $caste);
                 $cleanedValues = array_map(function($value) {
                    $value = trim($value);
                    $value = stripslashes($value);
                    return "'" . addslashes($value) . "'"; 
                 }, $valuesArray);
                
              
                $data['caste'] =  implode(',', $cleanedValues);
             }else{
               $data['age_to'] = $data['age_from'] =   $data['marital_sts'] =$data['height_from'] =$data['height_to'] = $data['caste'] = "";
             }
             
              $get_educationwise_data = $this->User_model_api_3->get_educationwise_search_data($data);
              $arr = array();
              
              if(!empty($get_educationwise_data)){
                  
                  
                  foreach($get_educationwise_data AS $key){
                      
                      
                      	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		$profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$key->id));
                		
                		$file_path1 = FCPATH . 'uploads/'.$profile_img[0]->file_name; 
                			             
                			             
                        $file_name  = (!empty($profile_img) && file_exists($file_path1)) ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
                        
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));

                        $arr[] = array(
                            "id"=> $key->id,
                            "gender"=> $key->gender,
                            "profile"=> $key->profile,
                            "status"=> $key->status,
                            "first_name"=> $key->first_name,
                            "created_user"=> $key->created_user,
                            "dob"=> $key->dob,
                            "height"=> $key->height,
                            "caste"=> $key->caste,
                            "martial_status"=> $key->martial_status,
                            "verified"=> $key->verified,
                            "perm_city"=> $key->perm_city,
                            "highest_education"=> $key->highest_education,
                            "education_field"=> $key->education_field,
                            "occup"=> $key->occup,
                            'favourite'=>$favourite,
                            'interest'=>$interest,
                            'file_name'=>$file_name,
                            "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                  }
                    $this->response([
                    'status' => TRUE,
                    'total'=>count($get_educationwise_data),
                    'data'=>$arr,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
              }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
              }
             
             
         }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
         }
    }
    
//=======================================================================//

     function logout_post(){
         $this->session->sess_destroy();
          $this->response([
                        'status' => TRUE,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);

     }
//=======================================================================//

    function received_interestpending_listing_post(){
        
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['sent']="1";
          $data['reject']="0";
          $data['accept']="0";
          $data['sent_date']="DESC";
          $partner_data = $this->User_model_api_3->get_interest_data($data);
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->logged_user_id;
                 $datas['send_date'] = $db->sent_date;
                 $int_id[] = $db->logged_user_id;
                 
             }
             
         }
        
     
            if(!empty($int_id)){
             
                $valuesArray =$int_id;// explode(',', $int_id);
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
                          
                          $arg[] = array(
                              "row_id"=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              'send_date_days'=>$this->timeago($db->sent_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
     function interestsent_pending_listing_post(){
        
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
            $data['sent']="1";
            $data['reject']="0";
            $data['accept']="0";
            $data['sent_date']="DESC";
            $this->db->select("profile_id,logged_user_id,sent_date");
		    $this->db->from('interest');  
			$this->db->where('logged_user_id',$data['reg_id']); 
			$this->db->where('sent',1 );
			$this->db->where('reject',0 );
			$this->db->where('accept',0 );
			$this->db->order_by('sent_date', 'desc'); 
			$this->db->group_by('profile_id');
			$partner_data = $this->db->get()->result();
		
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->profile_id;
                 $datas['send_date'] = $db->sent_date;
                 $int_id[] = $db->profile_id;
                 
             }
             
         }
      
     
            if(!empty($int_id)){
             
                $valuesArray =$int_id;
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
    
                          $arg[] = array(
                              "row_id"=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              'send_date_days'=>$this->timeago($db->sent_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
     function received_archive_listing_post(){
        
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['sent']="1";
          $data['reject']="0";
          $data['accept']="0";
          $data['sent_date']="DESC";
          $partner_data = $this->User_model_api_3->get_interest_data($data);
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->logged_user_id;
                 $datas['send_date'] = $db->sent_date;
                 $int_id[] = $db->logged_user_id;
                 
             }
             
         }
        
     
            if(!empty($int_id)){
             
                $valuesArray =$int_id;// explode(',', $int_id);
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
                          
                          $arg[] = array(
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              'send_date_days'=>$this->timeago($db->sent_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
    function timeago($date) {
	   $timestamp = strtotime($date);	
	   
	   $strTime = array("second", "minute", "hour", "day", "month", "year");
	   $length = array("60","60","24","30","12","10");

	   $currentTime = time();
	   if($currentTime >= $timestamp) {
			$diff     = time()- $timestamp;
			for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
			$diff = $diff / $length[$i];
			}

			$diff = round($diff);
			return $diff . " " . $strTime[$i] . "(s) ago ";
	   }
	}
    
//=======================================================================//

    function received_accepted_listing_post(){
        
            
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['reject']="0";
          $data['accept']="1";
          $data['accept_date']="DESC";
        //   $partner_data = $this->User_model_api_3->get_interest_data($data);
          
          
                    $this->db->select("profile_id,logged_user_id,sent_date");
			        $this->db->from('interest');  
			        $this->db->where('profile_id',$data['reg_id']); 
			        $this->db->where('reject',0 );
			        $this->db->where('accept',1 );
			        $this->db->order_by('accept_date', 'desc'); 
			        $this->db->group_by('logged_user_id'); 
			        $partner_data = $this->db->get()->result();
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->logged_user_id;
                 $datas['send_date'] = $db->sent_date;
                 
                 $int_id[] = $db->logged_user_id;
                                
                 
             }
             
         }
        
     
            if(!empty($int_id)){
             
                $valuesArray =$int_id;// explode(',', $int_id);
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
              
              
            if(!empty($finalString)){
                 
                $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
                $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                          
                        $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
  
                        $arg[] = array(
                              'row_id'=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              'days'=>$this->timeago($db->sent_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                              "mobile"=>$key->mobile,
                          );
                          
                      }
                  }
                  
            }else{
                $arg = "";
            }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
    //=======================================================================//

    function interestsent_accepted_listing_post(){
        
            
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['reject']="0";
          $data['accept']="1";
          $data['accept_date']="DESC";
                   $this->db->select("profile_id,logged_user_id,sent_date");
			        $this->db->from('interest');  
			        $this->db->where('logged_user_id',$data['reg_id']); 
			        $this->db->where('reject',0 );
			        $this->db->where('accept',1 );
			        $this->db->order_by('accept_date', 'desc'); 
			        $this->db->group_by('profile_id'); 
			        $partner_data = $this->db->get()->result();
			      
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->profile_id;
                 $datas['send_date'] = $db->sent_date;
                 $int_id[] = $db->profile_id;
             }
             
         }
        
     
            if(!empty($int_id)){
             
                $valuesArray =$int_id;// explode(',', $int_id);
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
              
              
            if(!empty($finalString)){
                 
                $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
                $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                          
                        $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));

                          
                          $arg[] = array(
                              "row_id"=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              'days'=>$this->timeago($db->sent_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                              "mobile"=>$key->mobile,
                          );
                          
                      }
                  }
                  
            }else{
                $arg = "";
            }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
    //====================================================================//

    function interestsent_declined_listing_post(){
        
            
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
    
                    $this->db->select("profile_id,logged_user_id,sent_date,reject_date");
			        $this->db->from('interest');  
			        $this->db->where('logged_user_id',$data['reg_id']); 
			        $this->db->where('reject','1' );
			        $this->db->where('accept','0' );
			        $this->db->order_by('reject_date', 'desc'); 
			        $this->db->group_by('profile_id'); 
			        $partner_data = $this->db->get()->result();
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->profile_id;
                 $datas['send_date'] = $db->sent_date;
                 $datas['reject_date'] = $db->reject_date;
                 $int_id[] = $db->profile_id;
             }
         }
     
            if(!empty($int_id)){
                $valuesArray =$int_id;
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
            
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));

                        $this->db->select("profile_id,logged_user_id,sent_date,reject_date");
    			        $this->db->from('interest');  
    			        $this->db->where('logged_user_id',$data['reg_id']); 
    			        $this->db->where('profile_id',$key->regester_id);
    			        $this->db->order_by('reject_date', 'desc'); 
    			        $this->db->group_by('profile_id'); 
    			        $partnerSS = $this->db->get()->result();
                        $reject_date = (!empty($partnerSS)) ? $partnerSS[0]->reject_date : "";

                          $arg[] = array(
                              'row_id'=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$reject_date,
                              'days'=>$this->timeago($reject_date),
                              "caste"=>$key->caste,
                              "dob"=>$key->dob,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
//====================================================================//

    function received_declined_listing_post(){
        
            
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['sent']="0";
          $data['reject']="1";
          $data['accept']="0";
          $data['reject_date']="DESC";
        //   $partner_data = $this->User_model_api_3->get_interest_data($data);
                    $this->db->select("profile_id,logged_user_id,sent_date,reject_date");
			        $this->db->from('interest');  
			        $this->db->where('profile_id',$data['reg_id']); 
			        $this->db->where('reject','1' );
			        $this->db->where('accept','0' );
			        $this->db->order_by('reject_date', 'desc'); 
			        $this->db->group_by('logged_user_id'); 
			        $partner_data = $this->db->get()->result();
      
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->logged_user_id;
                 $datas['send_date'] = $db->sent_date;
                  $datas['reject_date'] = $db->reject_date;
                 $int_id[] = $db->logged_user_id;
             }
         }
     
            if(!empty($int_id)){
                $valuesArray =$int_id;
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
          
           
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                    $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->regester_id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));


                        $this->db->select("profile_id,logged_user_id,sent_date,reject_date");
    			        $this->db->from('interest');  
    			        $this->db->where('logged_user_id',$key->regester_id); 
    			        $this->db->where('profile_id',$data['reg_id']);
    			        $this->db->order_by('reject_date', 'desc'); 
    			        $this->db->group_by('profile_id'); 
    			        $partnerSS = $this->db->get()->result();
    			        
    			        $reject_date = (!empty($partnerSS)) ? $partnerSS[0]->reject_date : "";
                          
                          $arg[] = array(
                              'row_id'=>$key->regester_id,
                              "profile_id"=>$key->profile,
                              "occupation"=>$key->occup,
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$reject_date,
                              'days'=>$this->timeago($reject_date),
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                              "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
//========================================================//

     function quick_search_post(){
        
        $data['marital_status'] = $mt = $this->input->post("marital_status");
        $data['caste'] = $caste = $this->input->post("caste");
        $user_id = $this->input->post("user_id");
        $data['state'] = $state = $this->input->post("state");
        $city = $this->input->post("city");
        $occup = $this->input->post("occupation");
        $gender = ($this->input->post("gender")=="M") ? "F" :"M";
        $diet = $this->input->post("diet");
        $smooking = $this->input->post("smooking");
        $drinking = $this->input->post("drinking");
        $education_field = $this->input->post("education_field");
        $highest_education = $this->input->post("highest_education");
        $data['age_from'] = $age_from = $this->input->post("age_from");
        $data['age_to'] = $age_to =  $this->input->post("age_to");
        $data['height_from'] = $height_from = $this->input->post("height_from");
        $data['height_to'] = $height_to = $this->input->post("height_to");
        
        
        if($user_id==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Please provide user id',
                    ], REST_Controller::HTTP_OK);
        }
        
        // if($state==""){
        //     $this->response([
        //                 'status' => FALSE,
        //                 'message' => 'Please provide State',
        //             ], REST_Controller::HTTP_OK);
        // }
        if($this->input->post("gender")==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Please provide Gender',
                    ], REST_Controller::HTTP_OK);
        }
        
        // if($occup==""){
        //     $this->response([
        //                 'status' => FALSE,
        //                 'message' => 'Please provide Occupation',
        //             ], REST_Controller::HTTP_OK);
        // }
        
            
         $height = $age = "";
             
         
       	 $marital_status = ($mt=="") ? "" : 'AND martial_status = "'.$mt.'"';  
    	
		    $state_s = explode(", ",$state);
			 $state1 = '"' . implode('", "', $state_s) . '"';
			if($state == 'Any' || $state_s == '' ){
			   $instate = ''; 
			}else{
			   $instate = ($state1 == '""') ? '' : 'AND perm_state ='.$state1; 
			}
			
			
		
			$highest_s = explode(", ",$highest_education);
			$highest1 = '"' . implode('", "', $highest_s) . '"';
			
			if($highest_education == 'Any' || $highest_s == '' ){
			   $inhighest = ''; 
			}else{
			  $inhighest = ($highest1 == '""') ? '' :'OR highest_education ='.$highest1;  
			}
			
			$occup_s = explode(", ",$occup);
			$occup1 = '"' . implode('", "', $occup_s) . '"';
			if($occup == 'Any' || $occup_s == '' ){
			   $inoccup = ''; 
			}else{
			  $inoccup = ($occup1 == '""') ? '' : 'AND occup ='.$occup1; 
			}
			
		
			$diet_s = explode(", ",$diet);
			 $diet1 = '"' . implode('", "', $diet_s) . '"';
			if($diet == 'Any' || $diet_s == '' ){
			   $indiet = ''; 
			}else{
			   $indiet = ($diet1 == '""') ? '' : 'AND diet IN ('.$diet1.')';  
			}
			
			
			$smooking_s = explode(", ",$smooking);
			$smooking1 = '"' . implode('", "', $smooking_s) . '"';
			if($smooking == 'Any' || $smooking_s == '' ){
			   $insmooking = ''; 
			}else{
			   $insmooking = ($smooking1 == '""') ? '' : 'AND smooking IN ('.$smooking1.')';  
			}
			
		
			
			
			$education_field_s = explode(", ",$education_field);
			$education_field1 = '"' . implode('", "', $education_field_s) . '"';
			if($education_field == 'Any' || $education_field_s == '' ){
			   $ineducation_field = ''; 
			}else{
			   $ineducation_field = ($education_field1 == '""') ? '' : 'OR education_field ='.$education_field1;  
			}
			
		    $city_s = explode(", ",$city);
			$city1 = '"' . implode('", "', $city_s) . '"';
			if($city == 'Any' || $city == '' ){
			   $incity = ''; 
			}else{
			  $incity = ($city1 == '""')  ? '' : 'AND perm_city ='.$city1; 
			}
			
			
            if(!empty($data['height_from']) && !empty($data['height_to'])){
			    $height.= "AND height >= '$height_from' AND height <= '$height_to'";
			}
			if(!empty($age_from) && !empty($age_to)){
			    $age.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_from'
AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
			}
			
			$caste_s = explode(", ",$caste);
			 $incaste1 = '"' . implode('", "', $caste_s) . '"';
			if($caste == 'Any' || $caste_s == '' ){
			   $incaste = ''; 
			}else{
			  if($incaste1 == '""'){
			      $incaste = '';
			  }else{
			      $incaste = 'AND caste = '.$incaste1;  
			  }
			}
			
			
			

          $qus_age_from = $this->input->post('age_from');
          $qus_age_to = $this->input->post('age_to');
          
          $qus_height_from =  str_replace(".","",$this->input->post('height_from'));
          $qus_height_to =  str_replace(".","",$this->input->post('height_to'));
          $qus_marital_status  = $this->input->post('marital_status'); 
          $qus_state  = $this->input->post('perm_state');   
          $qus_city  = $this->input->post('city'); 
          $qus_caste  = $this->input->post('caste'); 
          $qus_education_field  = $this->input->post('education_field'); 
          $qus_highest_education = $this->input->post('highest_education'); 
          $qus_occup  = $this->input->post('occup');
          $qus_diet  = $this->input->post('diet'); 
          $qus_smooking  = $this->input->post('smooking'); 
          $qus_drinking  = $this->input->post('drinking'); 
  
 
            $length="5";
            $characters = '123456ABCDEFGXYZ';
            $string = '';
            for ($i = 0; $i < $length; $i++) {
                $string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            $search_id = $string; 
            
           
            $data_insert = array(  
                'profile_id'  => $this->input->post('profile'),
                'search_id'  => $search_id, 
                'marital_status'  =>  $this->input->post('marital_status'),
                'age_from' => $this->input->post('age_from'), 
                'age_to' => $this->input->post('age_to'), 
                'height_from' => str_replace(".","",$this->input->post('height_from')), 
                'height_to' => str_replace(".","",$this->input->post('height_to')), 
                'state' =>  $this->input->post('state'),
                'city' =>  $this->input->post('city'),
                'caste'  =>  $this->input->post('caste'), 
                'education_field'  =>  $this->input->post('education_field'), 
                'highest_education'  =>  $this->input->post('highest_education'),
                'occup'  =>  $this->input->post('occupation'), 
                'diet'  => $this->input->post('diet'),
                'smooking'  =>  $this->input->post('smooking'),
                 'drinking'  =>  $this->input->post('drinking'),
                 
                );  
        $search = $this->db->insert('quick_search', $data_insert); 
    		
			
			$ReadSql =$this->db->query("select user_register.id,user_register.gender,user_register.profile, user_register.status,user_register.first_name,user_register.created_user,user_register.dob,user_register.height,user_register.caste,user_register.martial_status, user_register.verified,contact_info.perm_city,education_work.highest_education,education_work.education_field,education_work.occup ,personal_habits.diet
	 from user_register
        JOIN contact_info ON contact_info.reg_profil_id = user_register.profile
        JOIN education_work ON education_work.reg_profil_id = user_register.profile
        JOIN personal_habits ON personal_habits.reg_profil_id = user_register.profile
        where user_register.gender = '$gender'  AND  user_register.status = '1' $marital_status $age $height $incaste $incity $inhighest $inoccup 
        
     	ORDER BY id DESC");
			
      $res=	$ReadSql->result();
      
      $arr = array();
      if(!empty($res)){
          
          foreach($res AS $key){
              
              
              			$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$key->id));
                // 		$file_path1 = FCPATH . 'uploads/'.$profile_img[0]->file_name; 
                        $file_name  = (!empty($profile_img) && $profile_img[0]->file_name!="") ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
              
               $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
            
              $arr[] = array(
                  "id"=>$key->id,
                    "gender"=>$key->gender,
                    "profile"=>$key->profile,
                    "status"=>$key->status,
                    "first_name"=>$key->first_name,
                    "created_user"=>$key->created_user,
                    "dob"=>$key->dob,
                    "height"=>$key->height,
                    "caste"=>$key->caste,
                    "martial_status"=>$key->martial_status,
                    "verified"=>$key->verified,
                    "perm_city"=>$key->perm_city,
                    "highest_education"=>$key->highest_education,
                    "education_field"=>$key->education_field,
                    "occup"=>$key->occup,
                    "diet"=>$key->diet,
                    'favourite'=>$favourite,
                    'interest'=>$interest,
                    'file_name'=>$file_name,
                    "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                );
          }
           $this->response([
                    'status' => TRUE,
                    'search_id'=>$search_id,
                    'total' => count($res),
                    'data' => $arr,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
      }else{
          $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
      }
            
       
        
    }
    
    //==========================================================================//
    
    public function send_reminder_post(){
     
        
       $logged_user_id  =  $this->input->post('logged_user_id');
       $profile_id =   $this->input->post('profile_id');
       $reminder_date =  date('Y-m-d H:i:s');
       
       if($logged_user_id!="" && $profile_id!=""){
        
            $this->db->set('reminder_status', 1);
            $this->db->set('sent_date', $reminder_date);
            $this->db->set('reminder_date', $reminder_date);
            $this->db->where('logged_user_id', $logged_user_id);
            $this->db->where('profile_id', $profile_id);
            $reminder = $this->db->update('interest');
        if($reminder){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Reminder send successfully'
                ], REST_Controller::HTTP_OK);
        }
        
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data'
                ], REST_Controller::HTTP_OK);
       }
        
    }
    
    
    
   //==========================================================================// 
   
    function change_password_post(){
        
        $password  =  $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        $profile  =  $this->input->post('profile');
        
        if($password!="" && $confirm_password!="" &&  $profile!=""){
            
            if($password==$confirm_password){
                
                $dataa = $this->User_model_api_3->get_user_all_data(array("profile_id"=>$profile));
          
                if(!empty($dataa)){
                    $this->db->set('password', sha1($password));
                    $this->db->where('profile', $profile);
                    $update = $this->db->update('user_register');
                    
                    if($update){
                         $this->response([
                        'status' => TRUE,
                        'message' => 'Password updated successfull..',
                      ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                        'status' => FALSE,
                        'message' => 'Something went wrong',
                      ], REST_Controller::HTTP_OK);
                    }
                    
                }else{
                      $this->response([
                        'status' => FALSE,
                        'message' => 'Record is not found',
                      ], REST_Controller::HTTP_OK);
                }
                
            }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Password & confirm password should not be matched'
                ], REST_Controller::HTTP_OK);
            }
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data'
                ], REST_Controller::HTTP_OK);
        }
        
       
    }
   
//=============================================================================//    

    function do_you_want_to_search_post(){
        
        $search_id  =  $this->input->post('search_id');
        $name_search = $this->input->post('name_search');
        
        if($search_id!="" && $name_search!=""){
   
            $this->db->where('search_id',$search_id);
            $query2 = $this->db->get('quick_search');
            
            foreach ($query2->result() as $row){
                $q_id   =	$row->id;
            }
            $this->db->set('search_name', $name_search);
            $this->db->where('id', $q_id);
            $quick_search = $this->db->update('quick_search');
        
        if($quick_search){
            
            $this->response([
                    'status' => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Not updated'
                ], REST_Controller::HTTP_OK);
        }
        
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Provide all data'
                ], REST_Controller::HTTP_OK);
        }
        
    }

//=============================================================================//

    function saved_search_view_action_post(){
        
        
        
        $search_id = $this->input->post("search_id");
        
        if($search_id!=""){
            
            $this->db->select("*");
    		$this->db->from('quick_search');  
    		$this->db->where('search_id',$search_id ); 
    		$querypci = $this->db->get();
    		$result = $querypci->result();
    		
    		
    		if(!empty($result)){
    		    
    		    foreach($result AS $rowcf){
    		        
    		        
    		        $data['marital_status'] = $mt = $rowcf->marital_status;
                    $data['caste'] = $caste = $rowcf->caste;
                    $data['state'] = $state = $rowcf->state;
                    $city = $rowcf->city;
                    $occup = $rowcf->occup; 
                    $gender = ($this->input->post("gender")=="M") ? "F" :"M";
                    $diet = $rowcf->diet;
                    $smooking = $rowcf->smooking;
                    $drinking =$rowcf->drinking;
                    $education_field = $rowcf->education_field;
                    $highest_education = $rowcf->highest_education;
                    $data['age_from'] = $age_from = $rowcf->age_from;
                    $data['age_to'] = $age_to =  $rowcf->age_to;
                    $data['height_from'] = $height_from = $rowcf->height_from; 
                    $data['height_to'] = $height_to = $rowcf->height_to; 
                    
                    
                     
            
         $height = $age = "";
             
         
       	 $marital_status = ($mt=="") ? "" : 'AND martial_status = "'.$mt.'"';  
    	
		    $state_s = explode(", ",$state);
			 $state1 = '"' . implode('", "', $state_s) . '"';
			if($state == 'Any' || $state_s == '' ){
			   $instate = ''; 
			}else{
			   $instate = ($state1 == '""') ? '' : 'AND perm_state ='.$state1; 
			}
			
			
		
			$highest_s = explode(", ",$highest_education);
			$highest1 = '"' . implode('", "', $highest_s) . '"';
			
			if($highest_education == 'Any' || $highest_s == '' ){
			   $inhighest = ''; 
			}else{
			  $inhighest = ($highest1 == '""') ? '' :'OR highest_education ='.$highest1;  
			}
			
			$occup_s = explode(", ",$occup);
			$occup1 = '"' . implode('", "', $occup_s) . '"';
			if($occup == 'Any' || $occup_s == '' ){
			   $inoccup = ''; 
			}else{
			  $inoccup = ($occup1 == '""') ? '' : 'AND occup ='.$occup1; 
			}
			
		
			$diet_s = explode(", ",$diet);
			 $diet1 = '"' . implode('", "', $diet_s) . '"';
			if($diet == 'Any' || $diet_s == '' ){
			   $indiet = ''; 
			}else{
			   $indiet = ($diet1 == '""') ? '' : 'AND diet IN ('.$diet1.')';  
			}
			
			
			$smooking_s = explode(", ",$smooking);
			$smooking1 = '"' . implode('", "', $smooking_s) . '"';
			if($smooking == 'Any' || $smooking_s == '' ){
			   $insmooking = ''; 
			}else{
			   $insmooking = ($smooking1 == '""') ? '' : 'AND smooking IN ('.$smooking1.')';  
			}
			
		
			
			
			$education_field_s = explode(", ",$education_field);
			$education_field1 = '"' . implode('", "', $education_field_s) . '"';
			if($education_field == 'Any' || $education_field_s == '' ){
			   $ineducation_field = ''; 
			}else{
			   $ineducation_field = ($education_field1 == '""') ? '' : 'OR education_field ='.$education_field1;  
			}
			
		    $city_s = explode(", ",$city);
			$city1 = '"' . implode('", "', $city_s) . '"';
			if($city == 'Any' || $city == '' ){
			   $incity = ''; 
			}else{
			  $incity = ($city1 == '""')  ? '' : 'AND perm_city ='.$city1; 
			}
			
			
            if(!empty($data['height_from']) && !empty($data['height_to'])){
			    $height.= "AND height >= '$height_from' AND height <= '$height_to'";
			}
			if(!empty($age_from) && !empty($age_to)){
			    $age.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_from'
AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
			}
			
			$caste_s = explode(", ",$caste);
			 $incaste1 = '"' . implode('", "', $caste_s) . '"';
			if($caste == 'Any' || $caste_s == '' ){
			   $incaste = ''; 
			}else{
			  if($incaste1 == '""'){
			      $incaste = '';
			  }else{
			      $incaste = 'AND caste = '.$incaste1;  
			  }
			}
    		
			
			$ReadSql =$this->db->query("select user_register.id,user_register.gender,user_register.profile, user_register.status,user_register.first_name,user_register.created_user,user_register.dob,user_register.height,user_register.caste,user_register.martial_status, user_register.verified,contact_info.perm_city,education_work.highest_education,education_work.education_field,education_work.occup ,personal_habits.diet
        	 FROM user_register
                JOIN contact_info ON contact_info.reg_profil_id = user_register.profile
                JOIN education_work ON education_work.reg_profil_id = user_register.profile
                JOIN personal_habits ON personal_habits.reg_profil_id = user_register.profile
                where user_register.gender = '$gender'  AND  user_register.status = '1' $marital_status $age $height $incaste $incity $inhighest $inoccup 
                
             	ORDER BY id DESC");
        			
              $res=	$ReadSql->result();
              
              if(!empty($res)){
                   $this->response([
                            'status' => TRUE,
                            'search_id'=>$search_id,
                            'total' => count($res),
                            'data' => $res,
                            'message' => 'Success'
                        ], REST_Controller::HTTP_OK);
              }else{
                  $this->response([
                            'status' => FALSE,
                            'message' => 'No data found'
                        ], REST_Controller::HTTP_OK);
              }
                    
    		        
    		    }
    		}
         
         
        
      
      
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide search id'
                ], REST_Controller::HTTP_OK);
        }
          
        
        
        
    }
//=============================================================================//
    function saved_search_post(){
        
         $data['profile_id'] = $this->input->post("profile_id");
         
        //   if($data['search_id']!=""){
              
               $this->db->select("*");
        		$this->db->from('quick_search');  
        	
        		if($data['profile_id']!=""){
        		$this->db->where('profile_id', $data['profile_id'] ); 
        		}
        		$querypci = $this->db->get();
        		
        		if(!empty($querypci->result())){
        		     $this->response([
                        'status' => TRUE,
                        'total'=>count($querypci->result()),
                        'data'=>$querypci->result(),
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK); 
        		}else{
        		   $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK); 
        		}
              
              
        //   }else{
        //       $this->response([
        //                 'status' => FALSE,
        //                 'message' => 'Search id should not be blank',
        //             ], REST_Controller::HTTP_OK);
        //   }
         
    }
    
    
 
    
//=============================================================================//

    function search_by_id_post(){
        
        $data['profile_id'] = $this->input->post("profile");
        $user_id = $this->input->post("user_id");
        // $data['gender'] = ($this->input->post("gender")=="M") ? "F" : "M";
        
        
        if($user_id==''){
             $this->response([
                        'status' => FALSE,
                        'message' => 'user id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
        if($data['profile_id']!=""){
            
             $data = $this->User_model_api_3->get_user_all_data($data);
          $datas = array();
            if(!empty($data)){
                
                foreach($data AS $alc){
                    
                        $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$alc->user_id); 
                	    $this->db->where('main_pic','1');
                		$this->db->order_by('id','desc' );
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
                		$file  = (!empty($query3->result()) && $query3->result()[0]->file_name!="") ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                		
                		$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$alc->user_id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$alc->user_id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$alc->profile,"payment_mode"=>"Paid"));

                    
                    $datas[] = array(
                                "id"=> $alc->user_id,
                                'file'=>$file,
                                "profile"=> $alc->profile,
                                "first_name"> $alc->first_name,
                                "email"=> $alc->email,
                                "mobile"=> $alc->mobile,
                                "watsapp_num"=> $alc->watsapp_num,
                                "mobile_verified"=>$alc->mobile_verified,
                                "gender"=> $alc->gender,
                                "martial_status"=> $alc->martial_status,
                                "caste"=> $alc->caste,
                                "sub_caste"=> $alc->sub_caste,
                                "marry_other_caste"=> $alc->marry_other_caste,
                                "profile_created_for"=>  $alc->profile_created_for,
                                "dob"=>  $alc->dob,
                                "mother_tongue"=>  $alc->mother_tongue,
                                "body_type"=>   $alc->body_type,
                                "body_complexion"=>   $alc->body_complexion,
                                "weight"=>   $alc->weight,
                                "height"=>  $alc->height,
                                "phy_disable"=>   $alc->phy_disable,
                                "phy_disable_details"=>  $alc->phy_disable_details,
                                "status"=>  $alc->status,
                                "reg_id"=>  $alc->reg_id,
                                "perm_country"=>  $alc->perm_country,
                                "perm_state"=> $alc->perm_state,
                                "perm_city"=> $alc->perm_city,
                                "perm_address"=>  $alc->perm_address,
                                "perm_pincode"=>  $alc->perm_pincode,
                                "staying_with"=> $alc->staying_with,
                                "primary_edu"=> $alc->primary_edu,
                                "highest_education"=> $alc->highest_education,
                                "education_field"=>$alc->education_field,
                                "education"=> $alc->education,
                                "college_univ"=> $alc->college_univ,
                                "occup"=>$alc->occup,
                                'interest'=>$interest,
                                'favourite'=>$favourite,
                                "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                             );
                }
              
             
                
            }
            
             $this->response([
                    'status' => TRUE,
                    'total' => count($datas),
                    'data' => $datas,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            
            
        }else{
            $this->response([
                        'status' => FALSE,
                        'message' => 'Profile id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
   //=========================================================================//

    function search_by_caste_post(){
        
        $caste_name = $this->input->post("caste_name");
        $user_id = $this->input->post("logged_user_row_id");
        $show_gender = $this->input->post("gender");
     
        $limit = $this->input->post("limit");
        $offset = $this->input->post('offset');
        $nw_offset = ($offset == '') ? 0 : $offset;
        if($caste_name !="" && $show_gender != ''){
            
        $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,education_work.occup,education_work.education_field,memberships.payment_mode,profile_images.file_name, COALESCE(interest.sent, "0") as interest, COALESCE(favourites.profile_id, "0") as favourite' );

        $this->db->from('user_register');
        $this->db->where('user_register.caste', $caste_name); 
        
        $this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
        
        $this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
        
        $this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
        
        $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
        $this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
        $this->db->join('profile_images', 'profile_images.reg_id = user_register.id ','left', 'limit 1');
        $this->db->where('user_register.gender', $show_gender); 
        $this->db->where('user_register.status', 1); 
        $this->db->order_by('user_register.id','desc'); 
        $this->db->group_by('user_register.profile'); 
       ($offset == 0) ? $this->db->limit($limit) : $this->db->limit($limit, $offset);
        $query1 = $this->db->get();

       $newmatches = $query1->result_array();
       $newmatches_tcount = $query1->num_rows() ;  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'data Fetch Successfully',
                    'tcount' => $newmatches_tcount,
                    'data' => $newmatches,
                   
                ], REST_Controller::HTTP_OK);
       
        }else{
            $this->response([
                        'status' => FALSE,
                        'message' => 'Caste name and gender should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    } 
//=============================================================================//

    function archive_listing_post(){
            
        $data['reg_id'] = $this->input->post("reg_id");
        
        if($data['reg_id']!=""){
            
          $data['sent']="1";
          $data['reject']="0";
          $data['accept']="0";
          $data['sent_date']="DESC";
          $partner_data = $this->User_model_api_3->get_interest_data($data);
     
         $int_id = array();
       
         if(!empty($partner_data)){
             foreach($partner_data  AS $db){
                 
                 $datas['reg_id'] = $db->logged_user_id;
                 $datas['send_date'] = $db->sent_date;
                 $int_id[] = $db->logged_user_id;
             }
         }
     
            if(!empty($int_id)){
                $valuesArray =$int_id;
                $cleanedValues = array_map(function($value) {
                $value = trim($value);
                $value = stripslashes($value);
                     return "'" . addslashes($value) . "'"; 
                }, $valuesArray);
                    
                $finalString = implode(',', $cleanedValues);
                    
            }else{
                $finalString ="";
            }  
               
                 
            $reg_pro_con = $this->User_model_api_3->get_user_con_profile_data(array("reg_id"=>$finalString));
            $arg = array();   
                   
                  if(!empty($reg_pro_con)){
                      foreach($reg_pro_con AS $key){
                          
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($key->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $key->height, $string, $position, 0 );
	                    
	                     $this->db->select("*");
                        $this->db->from('profile_images');  
                        $this->db->where('reg_id',$key->id); 
                        $this->db->where('main_pic','1');
                        $this->db->order_by('id', 'desc');
                        $this->db->limit(1);
                         $query3 = $this->db->get();
                        $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                          
                          $arg[] = array(
                              "first_name"=>$key->first_name,
                              "perm_city"=>$key->perm_city,
                              "sent_date"=>$db->sent_date,
                              "caste"=>$key->caste,
                              "age"=>$age." Yrs",
                              "file_name"=>$file,
                              'height'=>$height." ft",
                          );
                          
                      }
                  }
        
        
            if(!empty($arg)){
                
                  $this->response([
                        'status' => TRUE,
                        'total'=>count($arg),
                        'data'=>$arg,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
            }else{
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK);
            }
        
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Reg id should not be blank',
                    ], REST_Controller::HTTP_OK);
        }
        
    }
    
//=======================================================================//
    
    function profession_wise_profile_post(){
        
         $occupation =  $data['occup'] = $this->input->post('occupation');
         $profile =  $data['profile'] = $this->input->post('profile');
         $gender = $this->input->post('gender');
         $user_id = $this->input->post('user_id');
         
         if($user_id==""){
              $this->response([
                        'status' => FALSE,
                        'message' => 'user id should not be blank',
                    ], REST_Controller::HTTP_OK);
         }
         
         
         if($occupation!="" && $profile!="" && $gender!=""){
             
             $partner_data = $this->User_model_api_3->get_partner_data(array("profile"=>$profile));
            
             
             if(!empty($partner_data)){
                 
             $data['gender'] = ($gender=="F") ? "M" : "F";
             $data['marital_sts'] = $partner_data[0]->marital_status;
             $data['height_from'] = $partner_data[0]->height_from;
             $data['height_to'] = $partner_data[0]->height_to;
             $data['age_from'] =  $partner_data[0]->age_from;
             $data['age_to'] =  $partner_data[0]->age_to;
              $caste = $partner_data[0]->caste;
                $valuesArray = explode(',', $caste);
                 $cleanedValues = array_map(function($value) {
                    $value = trim($value);
                    $value = stripslashes($value);
                    return "'" . addslashes($value) . "'"; 
                 }, $valuesArray);
                
              
                $data['caste'] =  implode(',', $cleanedValues);
             }else{
               $data['age_to'] = $data['age_from'] =   $data['marital_sts'] =$data['height_from'] =$data['height_to'] = $data['caste'] = "";
             }
             
              $get_occp_data = $this->User_model_api_3->get_occup_wise_data($data);
              
              $arr = array();
              if(!empty($get_occp_data)){
                  
                  
                  foreach($get_occp_data AS $key){
                      
                      	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$key->id));
                // 		$file_path1 = FCPATH . 'uploads/'.$profile_img[0]->file_name; 
                        $file_name  = (!empty($profile_img) && $profile_img[0]->file_name!="") ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
                        
                        
					$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));

                      
                      $arr[] = array(
                          "id"=>$key->id,
                        "gender"=>$key->gender,
                        "profile"=>$key->profile,
                        "status"=>$key->status,
                        "first_name"=>$key->first_name,
                        "created_user"=>$key->created_user,
                        "dob"=>$key->dob,
                        "height"=>$key->height,
                        "caste"=>$key->caste,
                        "martial_status"=>$key->martial_status,
                        "verified"=>$key->verified,
                        "perm_city"=>$key->perm_city,
                        "highest_education"=>$key->highest_education,
                        "education_field"=>$key->education_field,
                        "occup"=>$key->occup,
                        'interest'=>$interest,
                        'favourite'=>$favourite,
                         'file_name'=>$file_name,
                         "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                  }
                    $this->response([
                    'status' => TRUE,
                    'total'=>count($get_occp_data),
                    'data'=>$arr,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
              }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
              }
             
         }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
         }
         
         
    }
     
//------------------------------------------------------------------------------     
     function location_wise_profile_post(){
         
         $city_name =  $data['perm_city'] = $this->input->post('city_name');
         $profile =  $data['profile'] = $this->input->post('profile');
         $gender = $this->input->post('gender');
         $user_id = $this->input->post('user_id');
         
         if($user_id==""){
              $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide user id',
                ], REST_Controller::HTTP_OK);
         }
         
         
         if($city_name!="" && $profile!="" && $gender!=""){
             
             $partner_data = $this->User_model_api_3->get_partner_data(array("profile"=>$profile));
            
             
             if(!empty($partner_data)){
                 
             $data['gender'] = ($gender=="F") ? "M" : "F";
             $data['marital_sts'] = $partner_data[0]->marital_status;
             $data['height_from'] = $partner_data[0]->height_from;
             $data['height_to'] = $partner_data[0]->height_to;
             $data['age_from'] =  $partner_data[0]->age_from;
             $data['age_to'] =  $partner_data[0]->age_to;
              $caste = $partner_data[0]->caste;
                $valuesArray = explode(',', $caste);
                 $cleanedValues = array_map(function($value) {
                    $value = trim($value);
                    $value = stripslashes($value);
                    return "'" . addslashes($value) . "'"; 
                 }, $valuesArray);
                
              
                $data['caste'] =  implode(',', $cleanedValues);
             }else{
               $data['age_to'] = $data['age_from'] =   $data['marital_sts'] =$data['height_from'] =$data['height_to'] = $data['caste'] = "";
             }
             
              $get_work_city = $this->User_model_api_3->get_city_search_data($data);
              
              
              $arr = array();
              if(!empty($get_work_city)){
                  
                  
                  foreach($get_work_city AS $key){
                      
                      
                      	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$key->id));
                // 		$file_path1 = FCPATH . 'uploads/'.$profile_img[0]->file_name;
                        $file_name  = (!empty($profile_img) && $profile_img[0]->file_name!="") ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
                        
                        $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key->profile,"payment_mode"=>"Paid"));
                
                
                  $string = "'"; 
                  $position = '1'; 
                  $height_cal = substr_replace( $key->height, $string, $position, 0 )." ft";
				//calculate height
                    $inputs = $key->height ;
                    if(isset($inputs[1]) && $inputs[1] === '0') {
                        $inputs[1] = "'";
                    }
                    $string = "'"; 
                    $position = '1'; 
                    $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                    $ht_cal = str_replace("''","'",$heights);
               //   ...................................	
                      
                      $arr[] = array(
                          
                            "id"=>$key->id,
                            "gender"=>$key->gender,
                            "profile"=>$key->profile,
                            "status"=>$key->status,
                            "first_name"=>$key->first_name,
                            "created_user"=>$key->created_user,
                            "dob"=>$key->dob,
                            "height"=>$ht_cal,
                            "caste"=>$key->caste,
                            "martial_status"=>$key->martial_status,
                            "verified"=>$key->verified,
                            "perm_city"=>$key->perm_city,
                            "highest_education"=>$key->highest_education,
                            "education_field"=>$key->education_field,
                            "occup"=>$key->occup,
                            'favourite'=>$favourite,
                            'interest'=>$interest,
                            'file_name'=>$file_name,
                            "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                          );
                  }
                    $this->response([
                    'status' => TRUE,
                    'data'=>$arr,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
              }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
              }
             
         }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide all data',
                ], REST_Controller::HTTP_OK);
         }
     }
     
     
     function height_calculate_post(){
         
         $input = $this->input->post("height");
         
            if(isset($input[1]) && $input[1] === '0') {
                $input[1] = "'";
            }
            
            
            var_dump($input);
     }
    
//------------------------------------------------------------------------------

    function get_height_post(){
        
        $height = array(
            
            array("key"=>"4.0", "value"=>"400"),
            array("key"=>"4.1", "value"=>"401"),
            array("key"=>"4.2", "value"=>"402"),
            array("key"=>"4.3", "value"=>"403"),
            array("key"=>"4.4", "value"=>"404"),
             array("key"=>"4.5","value"=>"405"),
             array("key"=>"4.6","value"=>"406"),
             array("key"=>"4.7","value"=>"407"),
             array("key"=>"4.8","value"=>"408"),
             array("key"=>"4.9", "value"=>"409"),
            array("key"=>"4.10","value"=>"410"),
             array("key"=>"4.11","value"=>"411"),
             array("key"=>"5.0","value"=>"500"),
             array("key"=>"5.1","value"=>"501"),
             array("key"=>"5.2","value"=>"502"),
             array("key"=>"5.3", "value"=>"503"),
             array("key"=>"5.4", "value"=>"504"),
            array("key"=>"5.5", "value"=>"505"),
          
             array("key"=>"5.6","value"=>"506"),
             array("key"=>"5.7", "value"=>"507"),
             array("key"=>"5.8","value"=>"508"),
             array("key"=>"5.9","value"=>"509"),
            array("key"=>"5.10","value"=>"510"),
             array("key"=>"5.11","value"=>"511"),
             array("key"=>"6.0", "value"=>"600"),
             array("key"=>"6.1","value"=>"601"),
             array("key"=>"6.2","value"=>"602"),
             array("key"=>"6.3","value"=>"603"),
             array("key"=>"6.4","value"=>"604"),
             array("key"=>"6.5","value"=>"605"),
            array("key"=>"6.6", "value"=>"606"),
             array("key"=>"6.7", "value"=>"607"),
             array("key"=>"6.8","value"=>"608"),
             array("key"=>"6.9", "value"=>"609"),
            array("key"=>"6.10","value"=>"610"),
             array("key"=>"6.11","value"=>"611"),
            array("key"=>"7.0","value"=>"700")
        );
        
        if(!empty($height)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$height,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    function replaceZeroWithDot($number) {
$numberStr = $number;

    if (strpos($numberStr, '0') === 1) {
        // Remove the zero and add a dot after the first character
        $formattedNumber = substr_replace($numberStr, '.', 1, 1);
    } else {
        // Add a dot after the first character
        $formattedNumber = substr($numberStr, 0, 1) . '.' . substr($numberStr, 1);
    }

    return $formattedNumber;
}
    
    function get_height_backup_post(){
$number2 = "500";
 echo  $result2 = $this->replaceZeroWithDot($number2);
  
        $height = array(
            
            array("key"=>"4.00", "value"=>"400"),
            array("key"=>"4.01", "value"=>"401"),
            array("key"=>"4.02", "value"=>"402"),
            array("key"=>"4.03", "value"=>"403"),
            array("key"=>"4.04", "value"=>"404"),
             array("key"=>"4.05","value"=>"405"),
             array("key"=>"4.06","value"=>"406"),
             array("key"=>"4.07","value"=>"407"),
             array("key"=>"4.08","value"=>"408"),
             array("key"=>"4.09", "value"=>"409"),
            array("key"=>"4.10","value"=>"410"),
             array("key"=>"4.11","value"=>"411"),
             array("key"=>"5.00","value"=>"500"),
             array("key"=>"5.01","value"=>"501"),
             array("key"=>"5.02","value"=>"502"),
             array("key"=>"5.03", "value"=>"503"),
            array("key"=>"5.05", "value"=>"505"),
             array("key"=>"5.05","value"=>"505"),
             array("key"=>"5.06","value"=>"506"),
             array("key"=>"5.07", "value"=>"507"),
             array("key"=>"5.08","value"=>"508"),
             array("key"=>"5.09","value"=>"509"),
            array("key"=>"5.10","value"=>"510"),
             array("key"=>"5.11","value"=>"511"),
             array("key"=>"6.00", "value"=>"600"),
             array("key"=>"6.01","value"=>"601"),
             array("key"=>"6.02","value"=>"602"),
             array("key"=>"6.03","value"=>"603"),
            array("key"=>"6.06", "value"=>"606"),
             array("key"=>"6.06","value"=>"606"),
             array("key"=>"6.06", "value"=>"606"),
             array("key"=>"6.07", "value"=>"607"),
             array("key"=>"6.08","value"=>"608"),
             array("key"=>"6.09", "value"=>"609"),
            array("key"=>"6.10","value"=>"610"),
             array("key"=>"6.11","value"=>"611"),
            array("key"=>"7.00","value"=>"700")
        );
        
        if(!empty($height)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$height,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    //------------------------------------------------------------------------------

    function get_annual_income_post(){
        
        $height = array(
            
            array("key"=>"0-1", "value"=>"0-1 Lakh"),
            array("key"=>"1-2", "value"=>"1-2 Lakh"),
            array("key"=>"2-3", "value"=>"2-3 Lakh"),
            array("key"=>"3-4", "value"=>"3-4 Lakh"),
            array("key"=>"4-5", "value"=>"4-5 Lakh"),
            array("key"=>"5-6", "value"=>"5-6 Lakh"),
            array("key"=>"6-7", "value"=>"6-7 Lakh"),
            array("key"=>"7-8", "value"=>"7-8 Lakh"),
            array("key"=>"8-9", "value"=>"8-9 Lakh"),
            array("key"=>"9-10", "value"=>"9-10 Lakh"),
            array("key"=>"10-12", "value"=>"10-12 Lakh"),
            array("key"=>"12-14", "value"=>"12-14 Lakh"),
            array("key"=>"14-16", "value"=>"14-16 Lakh"),
            array("key"=>"16-18", "value"=>"16-18 Lakh"),
            array("key"=>"18-20", "value"=>"18-20 Lakh"),
            array("key"=>"20-25", "value"=>"20-25 Lakh"),
            array("key"=>"25-30", "value"=>"25-30 Lakh"),
            array("key"=>"30-35", "value"=>"30-35 Lakh"),
            array("key"=>"35-40", "value"=>"35-40 Lakh"),
            array("key"=>"40-45", "value"=>"40-45 Lakh"),
            array("key"=>"45-50", "value"=>"45-50 Lakh"),
             array("key"=>"50-60", "value"=>"50-60 Lakh"),
            array("key"=>"60-70", "value"=>"60-70 Lakh"),
            array("key"=>"70-80", "value"=>"70-80 Lakh"),
            array("key"=>"80-90", "value"=>"80-90 Lakh"),
            array("key"=>"90-01", "value"=>"90 Lakh & Above"),
        );
        
        if(!empty($height)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$height,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    //------------------------------------------------------------------------------

    function get_rashi_post(){
        
        $height = array(
            
            array("key"=>"Mesh", "value"=>"Mesh"),
            array("key"=>"Vrushabh", "value"=>"Vrushabh"),
            array("key"=>"Mithun", "value"=>"Mithun"),
            array("key"=>"Kark", "value"=>"Kark"),
            array("key"=>"Sinha", "value"=>"Sinha"),
            array("key"=>"Kanya", "value"=>"Kanya"),
            array("key"=>"Tula", "value"=>"Tula"),
            array("key"=>"Vrischik", "value"=>"Vrischik"),
            array("key"=>"Dhanu", "value"=>"Dhanu"),
            array("key"=>"Makar", "value"=>"Makar"),
            array("key"=>"Kumbh", "value"=>"Kumbh"),
             array("key"=>"Meen", "value"=>"Meen"),
        );
        
        if(!empty($height)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$height,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    
    
    function get_nakshtra_post(){
        
        $height = array(
            
            array("key"=>"Unspecified", "value"=>"Unspecified"),
            array("key"=>"Ashwini", "value"=>"Ashwini"),
            array("key"=>"Ardra", "value"=>"Ardra"),
            array("key"=>"Aslesha", "value"=>"Aslesha"),
            array("key"=>"Anuradha", "value"=>"Anuradha"),
            array("key"=>"Bharani", "value"=>"Bharani"),
            array("key"=>"Chitra", "value"=>"Chitra"),
            array("key"=>"Dhanishta", "value"=>"Dhanishta"),
            array("key"=>"Hasta", "value"=>"Hasta"),
            array("key"=>"Jyeshta", "value"=>"Jyeshta"),
            array("key"=>"Krittika", "value"=>"Krittika"),
             array("key"=>"Moola", "value"=>"Moola"),
               array("key"=>"Magha", "value"=>"Magha"),
            array("key"=>"Mrigasira", "value"=>"Mrigasira"),
            array("key"=>"Purva Phalgini", "value"=>"Purva Phalgini"),
            array("key"=>"Purva Bhadra", "value"=>"Purva Bhadra"),
             array("key"=>"Purva Shadha", "value"=>"Purva Shadha"),
            array("key"=>"Punarvasu", "value"=>"Punarvasu"),
            array("key"=>"Rohini", "value"=>"Rohini"),
            array("key"=>"Swati", "value"=>"Swati"),
            array("key"=>"Revati", "value"=>"Revati"),
            array("key"=>"Shattarka", "value"=>"Shattarka"),
            array("key"=>"Shravan", "value"=>"Shravan"),
            array("key"=>"Uttara Phalguni", "value"=>"Uttara Phalguni"),
            array("key"=>"Uttara Bhadra", "value"=>"Uttara Bhadra"),
            array("key"=>"Uttara Shadha", "value"=>"Uttara Shadha"),
            array("key"=>"Vishakha", "value"=>"Vishakha"),
        );
        
        if(!empty($height)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$height,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
    
//------------------------------------------------------------------------------

// function get_work_city_post(){
        
//         $get_work_city = $this->User_model_api_3->get_work_city();
        
//         if(!empty($get_work_city)){
//               $this->response([
//                     'status' => TRUE,
//                     'data'=>$get_work_city,
//                     'message' => 'Success',
//                 ], REST_Controller::HTTP_OK);
//         }else{
//              $this->response([
//                     'status' => FALSE,
//                     'message' => 'No record found',
//                 ], REST_Controller::HTTP_OK);
//         }
        
        
//     }
    
//------------------------------------------------------------------------------  

    function get_mother_tongue_post(){
        
        $mother_tongue = $this->User_model_api_3->get_mother_tongue();
        
        if(!empty($mother_tongue)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$mother_tongue,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
        
        
    }
//----------------------------------------------------------------------- // 

    function get_ages_post(){
        
        $ages = $this->User_model_api_3->get_ages();
        
        if(!empty($ages)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$ages,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
//----------------------------------------------------------------------- // 

    function get_primary_education_post(){
        
        $ages = $this->User_model_api_3->get_primary_education();
        
        if(!empty($ages)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$ages,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }
  //----------------------------------------------------------------------- // 

    function get_highest_education_post(){
        
        $highest_education = $this->User_model_api_3->get_highest_education();
        
        if(!empty($highest_education)){
              $this->response([
                    'status' => TRUE,
                    'data'=>$highest_education,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'No record found',
                ], REST_Controller::HTTP_OK);
        }
    }   
    //------------------------------------------------------------------- //
    
    function delete_user_post(){
        
       $mobile = $this->input->post("mobile");
       $delete = $this->db->delete('user_register',array('mobile'=>$mobile));
       
       if($delete){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Deleted successful.',
                ], REST_Controller::HTTP_OK);
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Not deleted',
                ], REST_Controller::HTTP_OK);
       }
    }
    
   //========================================================//  
    function delete_account_post(){
        
       $mobile = $this->input->post("mobile");
             $this->db->set('status', '3');
            $this->db->where('mobile', $mobile);
            $result=$this->db->update('user_register');
       
       if($result){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Deleted successful.',
                ], REST_Controller::HTTP_OK);
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Not deleted',
                ], REST_Controller::HTTP_OK);
       }
    }
//========================================================//
 public function login_post() {
     
        $token = trim($this->post('token'));
        $mobile = $this->post('mobile');
        $password = $this->post('password');
        
        
     if(!empty($mobile) && !empty($password)){
            
            // Check if any user exists with the given credentials
            $con['returnType'] = 'single';
            $con['conditions'] = array(
                'mobile' => $mobile,
                'password' => sha1($password),
                'status !=' => '3',
            );
            $user = $this->User_model_api_3->getusers_details($con);
            
            if($user){
                $login_time =   date('Y-m-d H:i:s');
                if($token!=""){
                  $this->db->set('token', $token);
                }
                $this->db->set('login_session', $login_time);
                $this->db->where('mobile', $mobile);
                $result=$this->db->update('user_register');
            
                $data_logged = array(
                     'mobile' => $mobile,   
                    'logged_date_time' => date('Y-m-d H:i:s'),
                 );
                $query_in =  $this->db->insert('user_logged_info', $data_logged);
                
                         $this->db->select("payment_mode");
                		$this->db->from('memberships');  
                	    $this->db->where('member_profile_id',$user['profile']);
                		$query3 = $this->db->get()->result();
                		
                		
                		 $this->db->select("occup");
                		$this->db->from('education_work');  
                	    $this->db->where('reg_profil_id',$user['profile']);
                		$query44 = $this->db->get()->result();
                		
                $profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$user['id']));
			  	
    		
                $file_name  = (!empty($profile_img) && $profile_img[0]->file_name!="") ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
			 
                $this->response([
                    'status' => TRUE,
                    'message' => 'User login successful.',
                    'data' => $user,
                    'membership'=>$query3[0]->payment_mode,
                    'file_name'=>$file_name,
                    'occupation'=>(!empty($query44)) ? $query44[0]->occup : "",
                ], REST_Controller::HTTP_OK);
                
            }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid details',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
            }
     }else{
          $this->response([
                    'status' => FALSE,
                    'message' => 'Provide Mobile number and Password',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
     }
 }
 //========================================================//
 
//  function resend_otp_post(){
//       $mobile = $this->input->post('resend_mobile');
//       $otp = $this->input->post('resend_otp');
//           //Your authentication key
//           $authKey = "310291AGuGg48FZ2k5e060d12P1";
//           $senderId = "SUNDJD";
//           $route = "4";
//             //Multiple mobiles numbers separated by comma
//             $mobileNumber = '91'.$mobile;
//             //Your message to send, Add URL encoding here.
//             $message = urlencode("Sundarjodi : Your verification code is ".$otp.". PupH6VD96vr");
           
//             //Prepare you post parameters
//             $postData = array(
//                 'authkey' => $authKey,
//                 'mobiles' => $mobileNumber,
//                 'message' => $message,
//                 'sender' => $senderId,
//                 'route' => $route
//             );
//             //API URL
//             $url="http://api.msg91.com/api/sendhttp.php";
//             // init the resource
//             $ch = curl_init();
//             curl_setopt_array($ch, array(
//                 CURLOPT_URL => $url,
//                 CURLOPT_RETURNTRANSFER => true,
//                 CURLOPT_POST => true,
//                 CURLOPT_POSTFIELDS => $postData
//                 //,CURLOPT_FOLLOWLOCATION => true
//             ));
            
//             //Ignore SSL certificate verification
//             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//             //get response
//             $output = curl_exec($ch);
//             //Print error if any
            
//             var_dump(curl_errno($ch));die;
//             if(curl_errno($ch)){
//                 // echo 'error:' . curl_error($ch);
//                  $this->response([
//                     'status' => FALSE,
//                     'message' => 'Error',
//                     'data' => curl_error($ch)
//                 ], REST_Controller::HTTP_OK);
//             }
//             curl_close($ch);
            
//              $this->response([
//                     'status' => TRUE,
//                     'message' => 'User login successful.',
//                     'data' => $user
//                 ], REST_Controller::HTTP_OK);
//         //   echo '<p class="alert alert-success">OTP code is send to your mobile number</p>';
// }


//========================================================//

    function forget_password_post(){
        
        $data['mobile'] = $this->input->post("mobile");
        $data['otp'] = $otp = rand(10000,99999);
        
        if($data['mobile']!=""){
            
            $con['conditions'] = array('mobile' => $data['mobile']);
            
            $user = $this->User_model_api_3->getusers_details($con);
            
            if(!empty($user)){
                
              $user_update = $this->User_model_api_3->update_user(array("mobile"=>$data['mobile'],'otp'=>$data['otp']));
              
              $authKey = "310291AGuGg48FZ2k5e060d12P1";
              $senderId = "SUNDJD";
              $route = "4";
            //Multiple mobiles numbers separated by comma
                $mobileNumber = '91'.$data['mobile'];
                //Your message to send, Add URL encoding here.
                $message = urlencode("Sundarjodi : Your verification code is ".$data['otp'].". PupH6VD96vr");
           
            //Prepare you post parameters
            $postData = array(
                'authkey' => $authKey,
                'mobiles' => $mobileNumber,
                'message' => $message,
                'sender' => $senderId,
                'route' => $route
            );
            //API URL
            $url="http://api.msg91.com/api/sendhttp.php";
            // init the resource
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
                //,CURLOPT_FOLLOWLOCATION => true
            ));
            
          
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
         
            if(curl_errno($ch)){
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Error',
                    'rep'=>curl_error($ch)
                ], REST_Controller::HTTP_OK);
            }
           
            curl_close($ch);
            $this->response([
                    'status' => TRUE,
                    'message' => 'OTP code is send to your mobile number'
                ], REST_Controller::HTTP_OK);
                
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Mobile number is not available'
                ], REST_Controller::HTTP_OK);
            }
            
        }else{
              $this->response([
                    'status' => FALSE,
                    'message' => 'Provide Mobile number'
                ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
    
    function resend_otp_post(){
    
        $mobile = $this->input->post('resend_mobile');
        // $otp = $this->input->post('resend_otp');
        
        
        if($mobile!=""){
            
            $otp = rand(10000,99999);
       
        $this->db->where('mobile',$mobile);
        $query2 = $this->db->get('user_register');
                             
        if(!empty($query2->result())){
            
            
            $user_update = $this->User_model_api_3->update_user(array("mobile"=>$mobile,'otp'=>$otp));
            
            $msg = "Dear ".$query2->result()[0]->first_name.",<br/><br/>

                 Your verification code is ".$otp." <br/><br/>
                
                Best regards,<br/>
                Sundar Jodi Team";
           
                
                $send_mail['form_name'] = 'SundarJodi';
                $send_mail['form'] = 'help@sundarjodi.com';
                $send_mail['message'] = $msg;
                $send_mail['to'] = $query2->result()[0]->email;
                $send_mail['subject'] ="Resend OTP";
                $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                  "from" => ["email" => "help@sundarjodi.com"],
                  "subject" => $send_mail['subject'], 
                  "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                $this->send_mail_curl($emailArray);
                
                
                
                
            } 
            
            
          $authKey = "310291AGuGg48FZ2k5e060d12P1";
          $senderId = "SUNDJD";
          $route = "4";
            //Multiple mobiles numbers separated by comma
            $mobileNumber = '91'.$mobile;
            $message = urlencode("Sundarjodi : Your verification code is ".$otp.". PupH6VD96vr");
            $postData = array(
                'authkey' => $authKey,
                'mobiles' => $mobileNumber,
                'message' => $message,
                'sender' => $senderId,
                'route' => $route
            );
        
            $url="http://api.msg91.com/api/sendhttp.php";
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
               
            ));
       
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
           
            $output = curl_exec($ch);
            if(curl_errno($ch)){
                echo 'error:' . curl_error($ch);
                $this->response([
                    'status' => FALSE,
                    'message'=>'Error',
                    'error' => curl_error($ch)
                ], REST_Controller::HTTP_OK);
            }
             curl_close($ch);
            
           $this->response([
                    'status' => TRUE,
                    'otp'=>$otp,
                    'message' => 'OTP code is send to your mobile number'
                ], REST_Controller::HTTP_OK);
           
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Provide Mobile number'
                ], REST_Controller::HTTP_OK);
        }
}

//=============================================================================//

    function reject_interest_post(){
    
         $reject_date = date('Y-m-d H:i:s');
         $logged_user_id =   $this->input->post('logged_user_id');
         $profile_id =   $this->input->post('another_user_id');
         
        if($logged_user_id!="" && $profile_id!=""){
    	 
    	 $data_pro = $this->User_model_api_3->get_user_all_data(array("id"=>$profile_id));
    	 
    	  $interest = $this->User_model_api_3->get_interest(array("logged_user_id"=>$logged_user_id,"profile_id"=>$profile_id));
    
        if(!empty($interest)){
         
                $subject = "Interest Rejected on Sundar Jodi";
                $msg ="Dear ".ucwords($data_pro[0]->first_name).",<br/><br/>
                        We wanted to let you know that you have chosen to reject the interest expressed by another member of Sundar Jodi. The journey to find the perfect match can sometimes involve making difficult choices. We appreciate your active participation and hope that your search leads you to a connection that feels right for you.<br/><br/>
                        
                        Please don't hesitate to explore more profiles and continue your quest for a beautiful relationship. We're here to support you every step of the way.<br/><br/>
                        
                        Warm regards,<br/>
                        Sundar Jodi Team";
                        
                        
                 $send_mail['form_name'] = 'SundarJodi';
                $send_mail['form'] = 'help@sundarjodi.com';
                $send_mail['message'] = $msg;
                $send_mail['to'] = $data_pro[0]->email;
                $send_mail['subject'] =$subject;
                $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                  "from" => ["email" => "help@sundarjodi.com"],
                  "subject" => $send_mail['subject'], 
                  "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                $this->send_mail_curl($emailArray);
                
           
         $interest_id = $interest[0]->id;
   
         $this->db->set('reject', 1);
         $this->db->set('accept', 0);
         $this->db->set('reject_date', $reject_date);
         $this->db->where('id', $interest_id);
         $reject = $this->db->update('interest'); 
         
         
          // .........................Notification............................. ...........   
   $data_logged = $this->User_model_api_3->get_user_all_data(array("id"=>$logged_user_id));
   $message_body = "Interest Rejected From: ".$data_logged[0]->profile;
   $message_title = "Interest Rejected";
    $this->db->where('profile', $data_pro[0]->profile);
    $query23 = $this->db->get('user_register');
    $t = $query23->row_array();
    if(!empty($query23->result())){
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        if(!empty($token)){   
            $notification = array();
            $arrNotification= array();			
            $arrData = array();		
            $arrNotification["profile_id"] = $data_logged[0]->profile;
            $arrNotification["row_id"] = $logged_user_id;
            $arrNotification["message"] = $message_body;
            $arrNotification["title"] = $message_title;
            $arrNotification["msg_type"] = 'interest_rejected';
            $arrNotification["sound"] = "default";
        
            $check = $this->user->fcm($token, $arrNotification, "Android"); 
            if($check){
             $data = array(
                            'logged_user' => $data_logged[0]->profile,
                            'second_user' => $data_pro[0]->profile,
                            'title' => $message_title,
                            'msg' => $message_body,
                            'action' => 'interest_rejected',
                     );
                    $partner_program = $this->db->insert('notifications', $data);
            }
           }
         }
         
    }
         
          if($reject){
                $this->response([
                    'status' => TRUE,
                    'message' => 'Request Rejected Successfull..'
                ], REST_Controller::HTTP_OK); 
          }else{
               $this->response([
                    'status' => FALSE,
                    'message' => 'Record is not updated'
                ], REST_Controller::HTTP_OK); 
          }
          
            }else{
                 $this->response([
                        'status' => FALSE,
                        'message' => 'No record found',
                    ], REST_Controller::HTTP_OK); 
            }
      
         }else{
               $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all post data'
                ], REST_Controller::HTTP_OK); 
         }
            
    }
//=============================================================================//


function notification_post(){
    
    
    $log_user_profile = $this->input->post("log_user_profile");
    
    if($log_user_profile!=""){
        
        $queryf = $this->db->query("SELECT * FROM notifications where logged_user = '$log_user_profile' OR logged_user = 'Admin' ORDER BY date_created DESC limit 50");
        $response = $queryf->result();
        
        if(!empty($response)){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'total'=>count($response),
                    'data'=>$response
                ], REST_Controller::HTTP_OK); 
        }else{
              $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK); 
        }
        
        
    }else{
          $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide log_user_profile'
                ], REST_Controller::HTTP_OK); 
    }
    
}

//=============================================================================//

    function accept_interest_post(){
        
        $accept_date = date('Y-m-d H:i:s');
        $logged_user_id  =  $this->input->post('logged_user_id');
        $profile_id  =  $this->input->post('profile_id');
        
        if($logged_user_id!="" && $profile_id){
          
          $interest = $this->User_model_api_3->get_interest(array("logged_user_id"=>$logged_user_id,"profile_id"=>$profile_id));
          
          if(!empty($interest)){
              $logedduser = $interest[0]->logged_user_id;
              $seconduser = $interest[0]->profile_id;
              $interest_id = $interest[0]->id;
              $data_pro = $this->User_model_api_3->get_user_all_data(array("id"=>$seconduser));
              $notify_seconduser = (!empty($data_pro)) ? $data_pro[0]->profile : "";
              $data_pro_log = $this->User_model_api_3->get_user_all_data(array("id"=>$logedduser));
              $notify_logeduser = (!empty($data_pro_log)) ? $data_pro_log[0]->profile : "";
              $this->db->set('accept', 1);
              $this->db->set('reject', 0);
              $this->db->set('accept_date', $accept_date);
              $this->db->where('id', $interest_id);
              $accept = $this->db->update('interest'); 
              
              if($accept){
                  
                  
                   $subject = "Interest Accepted on Sundar Jodi";
                   
                   $msg = "Dear ".ucwords($data_pro[0]->first_name)." (".ucwords($data_pro[0]->profile)."),

                            ".$data_pro_log[0]->profile." viewed your profile and shown interest to communicate with you further. Please view ".$data_pro_log[0]->profile." profile and confirm your decision of accept/decline.
                            
                            <br/><br/>
                            Best Wishes<br/>
                            Team sundarjodi
                   ";
                
                    $send_mail['form_name'] = 'SundarJodi';
                    $send_mail['form'] = 'help@sundarjodi.com';
                    $send_mail['message'] = $msg;
                    $send_mail['to'] = $data_pro[0]->email;
                    $send_mail['subject'] ="Interest Accepted on Sundar Jodi";
                    $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                      "from" => ["email" => "help@sundarjodi.com"],
                      "subject" => $send_mail['subject'], 
                      "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                    $this->send_mail_curl($emailArray);
                    
                    
                // .........................Notification............................. ...........   
                
                $message_body = "Interest Accepted from Profile id: ".$seconduser;
                $message_title = "Interest Accepted";
                $this->db->where('profile', $seconduser);
                $query23 = $this->db->get('user_register');
                $t = $query23->row_array();
                if(!empty($query23->result())){
                foreach ($query23->result() as $row3){
                    $tid =	$row3->id;
                    $token =	$row3->token;
                    if(!empty($token)){   
                        $notification = array();
                        $arrNotification= array();			
                        $arrData = array();		
                        $arrNotification["profile_id"] = $logprofile;
                        $arrNotification["row_id"] = $logged_user_id;
                        $arrNotification["message"] = $message_body;
                        $arrNotification["title"] = $message_title;
                        
                        $arrNotification["msg_type"] = 'interest_recive';
                        $arrNotification["sound"] = "default";
                    
                        $check = $this->user->fcm($token, $arrNotification, "Android"); 
                        if($check){
                         $data_noti = array(
                               
                                'logged_user' => $notify_logeduser,
                                'second_user' => $notify_seconduser,
                                'title' => $message_title,
                                'msg' => $message_body,
                                'action' => 'interest_accept',
                                 );
                                $partner_program = $this->db->insert('notifications', $data_noti);
                        }
                       }
                     }
                     
                }
                  
                    
              }
              
                $this->response([
                    'status' => TRUE,
                    'message' => 'Interest accepted Successfull..'
                ], REST_Controller::HTTP_OK); 
          }
          
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK); 
        }
        
    
            
    }
        

//=============================================================================//
    
    function verify_otp_post(){
        
        $data['mobile'] = $this->input->post("mobile");
        $data['otp'] = $this->input->post("otp");
        $user_api = $this->input->post("use_api");
        
        if($data['mobile']!="" && $data['otp']!=""){
            $con['conditions'] = array('mobile' => $data['mobile']);
            $user = $this->User_model_api_3->getusers_details($con);
            
            if(!empty($user)){
                
                $db_otp = $user[0]['otp'];
                
                
                 if($user_api!="" && $user_api=="registration"){
                     
                     $msg = "Dear ".$user[0]['first_name'].",<br/><br/>

                    Congratulations and a warm welcome to Sundar Jodi, your trusted partner in the search for love and companionship. Your journey to find the perfect match begins here.<br/><br/>
                    
                    Feel free to complete your profile, explore potential matches, and start your exciting search for a beautiful connection. We're here to support you every step of the way.<br/><br/>
                    
                    Thank you for choosing Sundar Jodi. We're thrilled to be part of your journey, and we wish you all the best in finding your perfect match.<br/><br/>
                    
                    Best regards,<br/>
                    Sundar Jodi Team";
                
                    $subject = "Welcome to Sundar Jodi - Your Matrimony Journey Begins!";
                    $send_mail['form_name'] = 'SundarJodi';
                    $send_mail['form'] = 'help@sundarjodi.com';
                    $send_mail['message'] = $msg;
                    $send_mail['to'] = $user[0]['email'];
                    $send_mail['subject'] ="Welcome to Sundar Jodi - Your Matrimony Journey Begins!";
                    $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                      "from" => ["email" => "help@sundarjodi.com"],
                      "subject" => $send_mail['subject'], 
                      "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                    $this->send_mail_curl($emailArray);
                        
                    }
                
                    if($db_otp==$data['otp']){
                         $this->response([
                        'status' => TRUE,
                        'message' => 'OTP is verified successfully'
                    ], REST_Controller::HTTP_OK);
                    
                    
                    
                   
                    
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Invalid OTP,Please enter valid OTP'
                    ], REST_Controller::HTTP_OK);  
                }
                
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'User is not available'
                ], REST_Controller::HTTP_OK);
            }
            
            
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Provide Mobile number & OTP'
                ], REST_Controller::HTTP_OK);
        }
        
    }


//========================================================//

 function new_password_post(){
     
        $data['mobile'] = $this->input->post("mobile");
        $data['password'] = $this->input->post("password");
        $data['confirm_password'] = $this->input->post("confirm_password");
        
        if($data['password']!="" && $data['confirm_password']!=""){
            
            $con['conditions'] = array('mobile' => $data['mobile']);
            
            if($data['password']==$data['confirm_password']){
            
            $user = $this->User_model_api_3->getusers_details($con);
            
            if(!empty($user)){
                
              $user_update = $this->User_model_api_3->update_user(array("mobile"=>$data['mobile'],"password"=>sha1($this->input->post("confirm_password"))));
              
              if($user_update){
                   $this->response([
                    'status' => TRUE,
                    'message' => 'Password updated successful'
                ], REST_Controller::HTTP_OK); 
              }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Not updated'
                ], REST_Controller::HTTP_OK);  
              }
              
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Mobile number is not available'
                ], REST_Controller::HTTP_OK);
            }
            
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Password & confirm password should not be match'
                ], REST_Controller::HTTP_OK);
            }
            
            
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide Mobile number & password,Confirm password'
                ], REST_Controller::HTTP_OK);
        }
     
 }
 
 //==============================================================================================//
 
function getRandomString($length = 9) {
    $characters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}
//========================================================================//
   
   function registration_post(){
       
        $data['first_name'] = $this->input->post("name");
        $data['caste'] = $this->input->post("caste");
        $data['mobile'] = $this->input->post("mobile");
        $data['email'] = $this->input->post("email");
        $data['gender'] = $gender = $this->input->post("gender");
        $data['martial_status'] = $this->input->post("marital_status");
        $data['signupBy'] = trim($this->post('signupBy'));
        $data['profile'] = $profile = $this->getRandomString();
        $data['password'] = sha1($this->input->post("password"));
        
        if(!empty($data)){
            
            $data['gender'] = ($data['gender']=="Male") ? "M" : "F";
            
            $con['conditions'] = array('mobile' => $data['mobile']);
            
            $user = $this->User_model_api_3->getusers_details($con);
            
            if(empty($user)){
                
                $otp = $data['otp'] = rand(10000,99999);
               
                $member = $this->db->insert('user_register', $data); 
                
             $data_cont = array(
            'reg_profil_id' => $profile,
             );

             $product_id = $this->db->insert('contact_info', $data_cont);
             $ph_id = $this->db->insert('personal_habits', $data_cont);
             $fm_id = $this->db->insert('family_information', $data_cont);
             $ew_id = $this->db->insert('education_work', $data_cont);
             $ew_id = $this->db->insert('partner_expection', $data_cont);
             $ip_id = $this->db->insert('personal_investment', $data_cont);
             $ho_id = $this->db->insert('horoscope_details', $data_cont);
            
             $data_member = array(
            'member_profile_id' => $profile,
            'total_profiles_alloted' => '10',
            'remaining_profiles' => '0',
            'package_validity' => date('Y-m-d H:i:s', strtotime(' + 10 days')),
            'payment_mode' => 'Free',
            'payment_status' => 'Success',
            'status' => '1',
            'gender' => $gender,
            'created_date' => date('Y-m-d H:i:s')
            
             );
            $member = $this->db->insert('memberships', $data_member);
            
                $con['conditions'] = array('mobile' => $data['mobile']);
                $user = $this->User_model_api_3->getusers_details($con);
            
                if($member){
                    
                  $user_update = $this->User_model_api_3->update_user(array("mobile"=>$data['mobile'],'otp'=>$otp));
              
                
              $authKey = "310291AGuGg48FZ2k5e060d12P1";
              $senderId = "SUNDJD";
              $route = "4";
            //Multiple mobiles numbers separated by comma
                $mobileNumber = '91'.$data['mobile'];
                //Your message to send, Add URL encoding here.
                $message = urlencode("Sundarjodi : Your verification code is ".$data['otp'].". PupH6VD96vr");
           
                //Prepare you post parameters
                $postData = array(
                    'authkey' => $authKey,
                    'mobiles' => $mobileNumber,
                    'message' => $message,
                    'sender' => $senderId,
                    'route' => $route
                );
                //API URL
                $url="http://api.msg91.com/api/sendhttp.php";
            // init the resource
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
                //,CURLOPT_FOLLOWLOCATION => true
            ));
            
          
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
         
            if(curl_errno($ch)){
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Error',
                    'rep'=>curl_error($ch)
                ], REST_Controller::HTTP_OK);
            }
           
            curl_close($ch);
               
                    
                 $this->response([
                        'status' => TRUE,
                        'message' => 'OTP is send your registered mobile number please verify mobile number',
                        'data'=>$user,
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Something went wrong',
                    ], REST_Controller::HTTP_OK);
                }
            
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Mobile number is already register please enter new mobile number',
                ], REST_Controller::HTTP_OK);
            }
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
       
   }
   
 //=================================================================//
 
     function add_basic_info_post(){
         
         $data['profile_created_for'] = $this->input->post("profile_created_for");
         $data['dob'] = $this->input->post("dob");
         $data['height'] = $this->input->post("height");
         $data['phy_disable'] = $this->input->post("phy_disable");
         $data['mother_tongue'] = $this->input->post("mother_tongue");
         $data['profile'] = $datas['reg_profil_id'] = $this->input->post("profile_id");
         $data['token'] = $this->input->post("token");
         
         $data['birth_time'] = $this->input->post("dob_time");
         $data['birth_city'] = $this->input->post("birth_city");
         $data['sub_caste'] = $this->input->post("sub_caste");
         $data['weight'] = $this->input->post("weight");
         $data['body_type'] = $this->input->post("body_type");
         $data['body_complexion'] = $this->input->post("body_complexion");
         $data['blood_group'] = $this->input->post("blood_group");
         $data['lens'] = $this->input->post("lens");
         $data['martial_status'] =  $this->input->post("martial_status");
        
         $data['marry_other_caste'] = $this->input->post("marry_other_caste");
         if($data['phy_disable']=="Yes"){
            $data['phy_disable_details'] = $this->input->post("phy_disable_details");
         }
         
          if(!empty($data) && $data!=""){
              
              
              if($data['profile_created_for']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'Create Profile For is required'
                ], REST_Controller::HTTP_OK);
            }
              
            if($data['height']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'Height is required'
                ], REST_Controller::HTTP_OK);
            }
            if($data['dob']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'DOB is required'
                ], REST_Controller::HTTP_OK);
            }
            
            // if($data['phy_disable']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Physical Disable is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            
            if($data['mother_tongue']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Mother tongue is required'
                ], REST_Controller::HTTP_OK);
            }
            if($data['martial_status']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Marital Status is required'
                ], REST_Controller::HTTP_OK);
            }
            
            //  if($data['birth_city']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Birth City is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            
            // if($data['weight']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Weight is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            // if($data['body_type']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Body Type is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            // if($data['body_complexion']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Body Complexion is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            // if($data['blood_group']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Blood Group is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            // if($data['lens']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Lens / Spectacles is required'
            //     ], REST_Controller::HTTP_OK);
            // }
         
         
            $user_update = $this->User_model_api_3->update_profile($data);
            $datas['diet'] = $this->input->post("diet");
            $datas['smooking'] = $this->input->post("smooking");
            $datas['drinking'] = $this->input->post("drinking");
            $datas['party_pub'] = $this->input->post("party_pub");
            $datas['hobbie'] = $this->input->post("hobbie");
            
            // if($datas['diet']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Diet is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            
            
            // if($datas['smooking']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Smooking is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            
            // if($datas['drinking']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Drinking is required'
            //     ], REST_Controller::HTTP_OK);
            // }
           
            $con['conditions'] = array('profile' => $data['profile']);
            $user = $this->User_model_api_3->getusers_details($con);
            if(empty($user)){
               $personal_habits = $this->db->insert('personal_habits', $datas);  
            }else{
               $personal_habits = $this->User_model_api_3->update_personal_habits($datas);
            }
             
             
             if($user_update && $personal_habits){
                  $this->response([
                    'status' => TRUE,
                    'message' => 'Basic information added successfully'
                ], REST_Controller::HTTP_OK);
             }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
             }
         
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
         
     }
     
//=====================================================================

    function add_contact_info_post(){
        
       
         $data['profile'] = $datas['reg_profil_id'] = $this->input->post("profile_id");
          
          $con_info['perm_country'] = $this->input->post("perm_country");
          $con_info['perm_state'] = $this->input->post("perm_state");
          $con_info['perm_city'] = $this->input->post("perm_district");
          $con_info['perm_address'] = $this->input->post("perm_address");
          $con_info['alter_mobile'] = $this->input->post("alter_mobile");
          $con_info['fblink'] = $this->input->post("fblink");
         
        
         
          if(!empty($con_info) && $con_info!=""){
              
              
             if($data['profile']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'Profile id is required'
                ], REST_Controller::HTTP_OK);
            }
            
            if($con_info['perm_country']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'Country is required'
                ], REST_Controller::HTTP_OK);
            }
           
            if($con_info['perm_state']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'State is required'
                ], REST_Controller::HTTP_OK);
            }
            if($con_info['perm_city']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'District is required'
                ], REST_Controller::HTTP_OK);
            }
             if($con_info['perm_address']==""){
                   $this->response([
                    'status' => FALSE,
                    'message' => 'Address is required'
                ], REST_Controller::HTTP_OK);
            }
              
            
            $con['conditions'] = array('reg_profil_id' => $datas['reg_profil_id']);
           
            $contact_detail = $this->User_model_api_3->getcontactinfo_details($con);
            
            $con_info['reg_profil_id'] = $datas['reg_profil_id'];
            // $con_info['perm_country'] = $datas['perm_country'];
            
            
            if(empty($contact_detail)){
               $conactinfo_update = $this->db->insert('contact_info', $con_info);  
            }else{
               $conactinfo_update = $this->User_model_api_3->update_contact_info($con_info);
            }
             
             
             if($conactinfo_update){
                  $this->response([
                    'status' => TRUE,
                    'message' => 'Contact information added successfully'
                ], REST_Controller::HTTP_OK);
             }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
             }
         
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
         
        
    }
     
     
     //==============================================================================================//
 
    //  function update_basic_info_post(){
         
    //      $data['profile_created_for'] = $this->input->post("profile_created_for");
    //      $data['dob'] = $this->input->post("dob");
    //      $data['birth_time'] = $this->input->post("birth_time");
    //      $data['birth_city'] = $this->input->post("birth_city");
    //      $data['mother_tongue'] = $this->input->post("mother_tongue");
    //      $data['sub_caste'] = $this->input->post("sub_caste");
    //      $data['weight'] = $this->input->post("weight");
    //      $data['height'] = $this->input->post("height");
    //      $data['body_type'] = $this->input->post("body_type");
    //      $data['body_complexion'] = $this->input->post("body_complexion");
    //      $data['blood_group'] = $this->input->post("blood_group");
    //      $data['lens'] = $this->input->post("lens");
    //      $data['phy_disable'] = $this->input->post("phy_disable");
    //      $data['marry_other_caste'] = $this->input->post("marry_other_caste");
    //      $data['profile'] = $datas['reg_profil_id'] = $this->input->post("profile_id");
    //      $data['phy_disable_details'] = ($data['phy_disable']=="Yes") ? $this->input->post("phy_disable_details") : "";
        
              
    //         if($data['profile_created_for']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Create Profile For is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['dob']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'DOB is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['birth_time']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Birth Time is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
            
    //         if($data['birth_city']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Birth Place is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
            
    //         if($data['mother_tongue']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Mother Tongue is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
            
    //         if($data['weight']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Weight is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //          if($data['height']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Height is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['body_type']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Body Type is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['body_complexion']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Body Complexion is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['blood_group']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Blood Group is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['lens']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Lens is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         if($data['phy_disable']==""){
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Physical Disabled is required'
    //             ], REST_Controller::HTTP_OK);
    //         }
            
              
         
    //         $user_update = $this->User_model_api_3->update_profile($data);
    //         $datas['diet'] = $this->input->post("diet");
           
    //         $con['conditions'] = array('profile' => $data['profile']);
    //         $user = $this->User_model_api_3->getusers_details($con);
    //         if(empty($user)){
    //           $personal_habits = $this->db->insert('personal_habits', $datas);  
    //         }else{
    //           $personal_habits = $this->User_model_api_3->update_personal_habits($datas);
    //         }
             
    //          if($user_update && $personal_habits){
    //               $this->response([
    //                 'status' => TRUE,
    //                 'message' => 'Basic information added successfully'
    //             ], REST_Controller::HTTP_OK);
    //          }else{
    //               $this->response([
    //                 'status' => FALSE,
    //                 'message' => 'Something went wrong'
    //             ], REST_Controller::HTTP_OK);
    //          }
         
        
         
    //  }
 
  //==============================================================================================//
 
     function add_family_info_post(){
         
         $data['fatherrname'] = $this->input->post("father_name");
         $data['father_presence'] = $this->input->post("father_presence");
         $data['father_occupation'] = $this->input->post("father_occupation");
        //  $data['father_desgintion'] = $this->input->post("father_desgintion");
         $data['father_native_place'] = $this->input->post("father_native_place");
         $data['motherrname'] = $this->input->post("mother_name");
         $data['family_finacial_backg'] = $this->input->post("family_finacial_backg");
         $data['mother_presence'] = $this->input->post("mother_presence");
         $data['mother_occupation'] = $this->input->post("mother_occupation");
         $data['mother_native_place'] = $this->input->post("mother_native_place");
        //  $data['mother_desgintion'] = $this->input->post("mother_desgintion");
         $data['family_values'] = $this->input->post("family_values");
         $data['family_annual_income'] = $this->input->post("family_annual_income");
         $data['about_family'] = $this->input->post("about_family");
         $data['loan_libilities'] = $this->input->post("loan_libilities");
         
         $datas['perm_country'] = $this->input->post("perm_country");
         $datas['perm_state'] = $this->input->post("perm_state");
         $datas['perm_city'] = $this->input->post("perm_city");
         $data['reg_profil_id'] = $datas['reg_profil_id'] = $this->input->post("profile_id");
         
         
         $data['no_of_brother'] = $this->input->post("no_of_brother");
         $data['no_of_brother_married'] = $this->input->post("no_of_brother_married");
         $data['no_of_sister'] = $this->input->post("no_of_sister");
         $data['no_of_sister_married'] = $this->input->post("no_of_sister_married");
         $data['intercaste_p'] = $this->input->post("intercaste_p");
         $data['separate_p'] = $this->input->post("separate_p");
         
         $data['relative_name'] = $this->input->post("relative_name");
         $data['relation_member'] = $this->input->post("relation_member");
         $data['relative_contact_no'] = $this->input->post("relative_contact_no");
         $data['relative_address'] = $this->input->post("relative_address");
         
         
         
           if($data['fatherrname']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Father Name is required'
                ], REST_Controller::HTTP_OK);
            }
             if($data['motherrname']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Mother Name is required'
                ], REST_Controller::HTTP_OK);
            }
            if($data['family_values']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Family Values is required'
                ], REST_Controller::HTTP_OK);
            }
             
            
            //  if($data['family_finacial_backg']==""){
            //       $this->response([
            //         'status' => FALSE,
            //         'message' => 'Family Financial Background is required'
            //     ], REST_Controller::HTTP_OK);
            // }
            
            
              if($this->input->post("perm_country")==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Country is required'
                ], REST_Controller::HTTP_OK);
            }
            if($this->input->post("perm_state")==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'State is required'
                ], REST_Controller::HTTP_OK);
            }
             
            
             if($this->input->post("perm_city")==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'District is required'
                ], REST_Controller::HTTP_OK);
            }
         
          if(!empty($data) && !empty($datas)){
            $cons['conditions'] = array('profile' =>$datas['reg_profil_id']);
            $users = $this->User_model_api_3->getusers_details($cons);
            $data['reg_id'] = $datas['reg_id'] = $users[0]['id'];
         
            // $family_update = $this->User_model_api_3->update_family_info($data);
           
            $con['conditions'] = array('reg_profil_id' => $data['reg_profil_id']);
            $user = $this->User_model_api_3->getfamily_details($con);
            
            if(empty($user)){
               $family_update = $this->db->insert('family_information', $data);  
            }else{
               $family_update = $this->User_model_api_3->update_family_info($data);
            }
            
            $contact_detail = $this->User_model_api_3->getcontactinfo_details($con);
            
            if(empty($contact_detail)){
               $conactinfo_update = $this->db->insert('contact_info', $datas);  
            }else{
               $conactinfo_update = $this->User_model_api_3->update_contact_info($datas);
            }
             
          
             if($conactinfo_update && $family_update){
                  $this->response([
                    'status' => TRUE,
                    'message' => 'Family information added successfully'
                ], REST_Controller::HTTP_OK);
             }else{
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
             }
         
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
         
     }
 
  //==============================================================================================//
  
  function add_education_post(){
      
         $data['college_univ'] = $this->input->post("college_univ");
         $data['education'] = $this->input->post("education");
         $data['occup'] = $this->input->post("occupation");
         $data['money'] = $this->input->post("income");
         $data['work_city'] = $this->input->post("work_city");
         $data['primary_edu'] = $this->input->post("primary_edu");
         $data['highest_education'] = $this->input->post("highest_education");
         $data['education_field'] = $this->input->post("education_field");
         $data['designation'] = $this->input->post("about_career");
         
         $data['reg_profil_id'] = $this->input->post("profile_id");
         
        if(!empty($data)){
            
            if($data['college_univ']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'University / College is required'
                ], REST_Controller::HTTP_OK);
            }
            if($data['education']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Education is required'
                ], REST_Controller::HTTP_OK);
            }
            
            if($data['occup']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Occupation is required'
                ], REST_Controller::HTTP_OK);
            }
             if($data['money']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Annual Income is required'
                ], REST_Controller::HTTP_OK);
            }
             if($data['work_city']==""){
                  $this->response([
                    'status' => FALSE,
                    'message' => 'Work city is required'
                ], REST_Controller::HTTP_OK);
            }
            
            $con['conditions'] = array('profile' => $data['reg_profil_id']);
            $users = $this->User_model_api_3->getusers_details($con);
            $data['reg_id'] = $users[0]['id'];
          
            $con['conditions'] = array('reg_profil_id' => $data['reg_profil_id']);
            $user = $this->User_model_api_3->geteducation_details($con);
            
            if(empty($user)){
               $education_update = $this->db->insert('education_work', $data);  
            }else{
               $education_update = $this->User_model_api_3->update_education_info($data);
            }
            
           
            if($education_update){
                 $this->response([
                    'status' => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
            }
            
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
      
  }
  
  //==========================================================================//
  
    function add_horoscope_post(){
        
         $data['rashi'] = $this->input->post("rashi"); 
         $data['gan'] = $this->input->post("gan");
         $data['charan'] = $this->input->post("charan");
         $data['nadi'] = $this->input->post("nadi");
         $data['nakshtra'] = $this->input->post("nakshtra");
         
         $data['gotra'] = $this->input->post("gotra");
         $data['devak'] = $this->input->post("devak");
         $data['mangal'] = $this->input->post("mangal");
         $data['reg_profil_id'] = $this->input->post("profile_id");
         
         if($this->input->post("rashi")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Rashi should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
        if(!empty($data)){
            
            $con['conditions'] = array('profile' => $data['reg_profil_id']);
            $users = $this->User_model_api_3->getusers_details($con);
            $data['reg_id'] = $users[0]['id'];
            $con['conditions'] = array('reg_profil_id' => $data['reg_profil_id']);
            $user = $this->User_model_api_3->gethoroscope_details($con);
            $horoscope_update = (empty($user)) ? $this->db->insert('horoscope_details', $data) : $this->User_model_api_3->update_horoscope_info($data);
           
            if($horoscope_update){
                 $this->response([
                    'status' => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
            }
            
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
        
    }
    
    
    
    //========================================================================//
  
  function add_partner_expectation_post(){
      
         $data['education_field'] = $this->input->post("education_field");  //required
         $data['working_partner'] = $this->input->post("working_partner");
         $data['marital_status'] = $this->input->post("marital_status");
         $data['liv_city'] = $this->input->post("living_city");
         $data['occup'] = $this->input->post("occup");
         $data['caste'] = $this->input->post("caste");
         $data['diet'] = $this->input->post("diet");
         $data['smooking'] = $this->input->post("smooking");
         $data['drinking'] = $this->input->post("drinking");
         $data['partner_pref'] = $this->input->post("partner_pref");
         $data['reg_profil_id'] = $this->input->post("profile_id");
         $data['age_from'] = $this->input->post("age_from");
         $data['age_to'] = $this->input->post("age_to");
         $data['height_from'] = $this->input->post("height_from");
         $data['height_to'] = $this->input->post("height_to");
         $data['primary_edu'] = $this->input->post("primary_edu");
         $data['highest_education'] = $this->input->post("highest_education");
         $data['liv_city'] = $this->input->post("liv_city");
        
         if($this->input->post("caste")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Caste should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         if($this->input->post("marital_status")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Marital Status should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
        
         
         if($this->input->post("occup")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'occupation should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("education_field")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Education should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("age_from")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Age From should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("age_to")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Age To should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("height_from")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Height from should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("height_to")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'height To should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("primary_edu")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Primary education should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         if($this->input->post("highest_education")==""){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Highest education should not be blank'
                ], REST_Controller::HTTP_OK);
         }
         
         
         
         
         
        if(!empty($data)){
            
            $con['conditions'] = array('profile' => $data['reg_profil_id']);
            $users = $this->User_model_api_3->getusers_details($con);
            $data['reg_id'] = $users[0]['id'];
            $con['conditions'] = array('reg_profil_id' => $data['reg_profil_id']);
            $user = $this->User_model_api_3->getpartner_details($con);
            $partner_update = (empty($user)) ? $this->db->insert('partner_expection', $data) : $this->User_model_api_3->update_partnerdata_info($data);
           
            if($partner_update){
                 $this->response([
                    'status' => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Something went wrong'
                ], REST_Controller::HTTP_OK);
            }
            
            
        }else{
             $this->response([
                    'status' => FALSE,
                    'message' => 'Please Provide all data'
                ], REST_Controller::HTTP_OK);
        }
  }
  
  //========================================================================//
  
  function add_profile_photo_post(){
      
      
     $data['reg_profil_id'] = $this->input->post("profile_id");
      
    $con['conditions'] = array('profile' => $data['reg_profil_id']);
    $users = $this->User_model_api_3->getusers_details($con);
    $user_id = $users[0]['id'];
    
    if($data['reg_profil_id']==""){
        $this->response([
                    'status' => FALSE,
                    'message' => 'Profile id should not be blank'
                ], REST_Controller::HTTP_OK);
    }
    
    
    if($_FILES["insert_image"]==""){
        $this->response([
                    'status' => FALSE,
                    'message' => 'Please upload image'
                ], REST_Controller::HTTP_OK);
    }
      
   $config['upload_path'] = '../../sundarjodi.com/uploads/';
   $config['allowed_types'] = 'jpg|jpeg|png';
   $config['max_size'] = '0';
    $imganame = $_FILES["insert_image"]["name"];

    $this->load->library('upload', $config);

    if(strlen($imganame)>0){

        if ( !$this->upload->do_upload("insert_image")){
            $error = array('error' => $this->upload->display_errors());
             $this->response([
                    'status' => FALSE,
                    'message' => 'Error',
                    'data'=>$error,
                ], REST_Controller::HTTP_OK);
        }else{
            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload->upload_path.$this->upload->file_name;
            $filename = $_FILES['insert_image']['tmp_name'];
            $imgdata=@exif_read_data($this->upload->upload_path.$this->upload->file_name, 'IFD0');
               
            $userData = array(
                'reg_id' => $user_id,
                'file_name' => $this->upload->file_name,
                'main_pic' => '1'
            );

             $upimage = $this->db->insert('profile_images', $userData);
             if($upimage){
                  $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
             }
            list($width, $height) = getimagesize($filename);
            if ($width >= $height){
                $config['width'] = 800;
            }
            else{
                $config['height'] = 800;
            }
            $config['master_dim'] = 'auto';


            $this->load->library('image_lib',$config); 

            if (!$this->image_lib->resize()){  
                $this->response([
                    'status' => FALSE,
                    'message' => 'Error',
                ], REST_Controller::HTTP_OK);
            }else{

                $this->image_lib->clear();
                $config=array();

                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload->upload_path.$this->upload->file_name;


                switch($imgdata['Orientation']) {
                    case 3:
                        $config['rotation_angle']='180';
                        break;
                    case 6:
                        $config['rotation_angle']='270';
                        break;
                    case 8:
                        $config['rotation_angle']='90';
                        break;
                }

                $this->image_lib->initialize($config); 
                $this->image_lib->rotate();
             
            }
           
       }      
   }  
   $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
      
  }
  
//==============================================================================================//


    function upload_document_post(){
        
        $data['reg_profil_id'] = $this->input->post("profile_id");
        $data['doc_type'] = $this->input->post('doc_type');
        $con['conditions'] = array('profile' => $data['reg_profil_id']);
        
        $users = $this->User_model_api_3->getusers_details($con);
        
        if(!empty($users)){
        $user_id = $users[0]['id'];
        
        if($data['reg_profil_id']==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Profile id should not be blank'
                    ], REST_Controller::HTTP_OK);
        }
        
        if($data['doc_type']==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Document type should not be blank'
                    ], REST_Controller::HTTP_OK);
        }
        
       
            if(!empty($_FILES['insert_image']['name'])){
                $config['upload_path'] = '../../sundarjodi.com/documents_user/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                $new_name = rand().$_FILES["insert_image"]['name'];
                $config['file_name'] = $new_name;
                
             
                $this->load->library('upload',$config);
                $this->upload->initialize($config);
                
                if($this->upload->do_upload('insert_image')){
                    $uploadData = $this->upload->data();
                    $picture = $uploadData['file_name'];
                }else{
                    $picture = '';
                }
            }
            //Prepare array of user data
            $userData = array(
               'reg_id' => $user_id,
               'file_name' => $picture,
               'doc_type' => $this->input->post('doc_type'),
               'identity_badge' => '1',
            );
           
            $insert_userdocs = $this->db->insert('user_documents', $userData);
            if($insert_userdocs){
                   $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
             }else{
                 $this->response([
                    'status' => FALSE,
                    'message' => 'Something wrong',
                ], REST_Controller::HTTP_OK);
             }
             
             
        }else{
               $this->response([
                    'status' => FALSE,
                    'message' => 'User not found',
                ], REST_Controller::HTTP_OK);
        }
          
        
    }

//==============================================================================================//
  
  function get_country_post(){
      
      $country = $this->User_model_api_3->get_country();
      
      if($country){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$country
                ], REST_Controller::HTTP_OK);
      }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No redcord found'
                ], REST_Controller::HTTP_OK);
      }
      
       
      
  }
  
  
  //==============================================================================================//
  
  function get_state_post(){
      
      $data['country_id'] = $this->input->post("country_id");
      
      $state = $this->User_model_api_3->get_state($data);
      
      if($state){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$state
                ], REST_Controller::HTTP_OK);
      }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No redcord found'
                ], REST_Controller::HTTP_OK);
      }
      
       
      
  }
   //=========================================================================//
  
  function get_occupation_post(){
      
      
      $get_occupation = $this->User_model_api_3->get_occupation();
      
      if($get_occupation){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$get_occupation
                ], REST_Controller::HTTP_OK);
      }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK);
      }
  }
  
  //=========================================================================//
  
  function get_city_post(){
      
      $data['state_id'] = $this->input->post("state_id");
    //   $data['country_id'] = $this->input->post("country_id");
      
      $state = $this->User_model_api_3->get_city($data);
      
      if($state){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$state
                ], REST_Controller::HTTP_OK);
      }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK);
      }
      
       
      
  }
  
  //==============================================================================================//
  
  function get_education_post(){
      
      $education = $this->User_model_api_3->get_education();
      
      if($education){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$education
                ], REST_Controller::HTTP_OK);
      }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No redcord found'
                ], REST_Controller::HTTP_OK);
      }
      
       
      
  }
  
  //==============================================================================================//
  
  function get_caste_post(){
      
        $get_caste = $this->User_model_api_3->get_caste();
      
        if($get_caste){
          $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'data'=>$get_caste
                ], REST_Controller::HTTP_OK);
        }else{
           $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK);
        }
      
    }
  
    //==============================================================================================//
    
    function new_matches_post(){
        
        $data['age_from'] = $this->input->post("age_from");
        $data['age_to'] = $this->input->post("age_to");
        $data['height_from'] = $this->input->post("height_from");
        $data['height_to'] = $this->input->post("height_to");
        
        $data['working_partner'] = $this->input->post("working_partner");
        $data['living_city'] = $this->input->post("living_city");
        // $data['looking_for'] = $this->input->post("looking_for");
        
        $data['primary_education'] = $this->input->post("primary_education");
        $data['highest_education'] = $this->input->post("highest_education");
        $data['education_field'] = $this->input->post("education_field");
        $data['occupation'] = $this->input->post("occupation");
        
        $valuesArray = explode(',', $this->input->post("caste"));
        $cleanedValues = array_map(function($value) {
        $value = trim($value);
        $value = stripslashes($value);
        return "'" . addslashes($value) . "'"; 
        }, $valuesArray);
                
        $finalString = implode(',', $cleanedValues);

        $data['caste'] = $finalString;
        $data['diet'] = $this->input->post("diet");
        $data['smooking'] = $this->input->post("smooking");
        $data['drinking'] = $this->input->post("drinking");
        $data['marital_status'] = $this->input->post("marital_status");
        $data['gender'] = ($this->input->post("gender")=="Male") ? "F" : "M";
        $data['limit'] = $this->input->post("limit");
        $data['offset'] = ($this->input->post("offset")!="") ? $this->input->post("offset") : 0;
   
        $user_id = $this->input->post("user_id");
        
        if($user_id==""){
             $this->response([
                    'status' => FALSE,
                    'message' => 'User is is required'
                ], REST_Controller::HTTP_OK);
        }
        if($data['offset'] ==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'offset is required',
                ], REST_Controller::HTTP_OK);
        }
        if($data['limit']==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'Limit is required',
                ], REST_Controller::HTTP_OK);
        }

  

        
        $matched_data = $this->User_model_api_3->get_new_macthed_data($data);
        
        $arr = array();
        if(!empty($matched_data)){
          
            foreach($matched_data AS $row1){
                
              $profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$row1->id));
              $city_get = $this->User_model_api_3->get_user_city(array("reg_profil_id"=>$row1->profile));
			  $city_name = (!empty($city_get)) ? $city_get[0]->perm_city : "";
			  
              $file_name  = (!empty($profile_img) && $profile_img[0]->file_name!="") ? "uploads/".$profile_img[0]->file_name : "image/user-img.jpg";
			
			  $today = date('Y-m-d');
			  $diff = date_diff(date_create($row1->dob), date_create($today));
	          $age = $diff->format('%y');
	          $string = "'"; 
              $position = '1'; 
              $height_cal = substr_replace( $row1->height, $string, $position, 0 )." ft";
              
                        $this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$this->input->post("user_id"));
                	    $this->db->where('profile_id',$row1->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$this->input->post("user_id"));
                	    $this->db->where('profile_id',$row1->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$this->db->select("occup");
                		$this->db->from('education_work');  
                	    $this->db->where('reg_profil_id',$row1->profile);
                	    $querys = $this->db->get()->result();

				$paid_members = $this->User_model_api_3->check_membership(array("member_profile_id"=>$row1->profile,"payment_mode"=>"Paid"));
                
                //calculate height
                    $inputs = $row1->height ;
                    if(isset($inputs[1]) && $inputs[1] === '0') {
                        $inputs[1] = "'";
                    }
                    $string = "'"; 
                    $position = '1'; 
                    $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                    $ht_cal = str_replace("''","'",$heights);
               //   ...................................
                
                $arr[] = array("id"=>$row1->id,
                "profile"=>$row1->profile,
                "first_name"=>$row1->first_name,
                "email"=>$row1->email,
                "mobile"=>$row1->mobile,
                "gender"=>$row1->gender,
                "martial_status"=>$row1->martial_status,
                "caste"=>$row1->caste,
                "religion"=>$row1->religion,
                "sub_caste"=>$row1->sub_caste,
                "dob"=>$row1->dob,
                "profile_image"=>$file_name,
                // "height"=>$height_cal,
                "age"=>$age." Yrs",
                "city"=>$city_name,
                'interest'=>$interest,
                'favourite'=>$favourite,
                'occupation'=>(!empty($querys)) ? $querys[0]->occup : "",
                "premium_member"=>(!empty($paid_members)) ? "Yes" : "No",
                "height"=>$ht_cal
              );
             
            }
            
            $this->response([
                    'status' => TRUE,
                    'data'=>$arr,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK);
        }
        
      
       
        
        
    }
    
    //==================================================================//
     
    function view_profile_post(){
        
        $data['profile_id'] = $this->input->post("profile");
        
        
        // if($this->input->post("view_contact")=="1"){
            $log_user_profile =  $this->input->post("current_login_profile");
        
        if($data['profile_id']!="" && $log_user_profile!=""){
            
         $matched_data = $this->User_model_api_3->get_new_macthed_data($data);
         $todaydate = date('Y-m-d H:i:s');
         
         $mergedArray = array();
           if(!empty($matched_data)){
               foreach($matched_data AS $row1){
                   
                   
                        $this->db->select("*");
                		$this->db->from('memberships');  
                		$this->db->where('member_profile_id',$log_user_profile ); 
                		$this->db->limit(1);
                		$querypmp = $this->db->get();
                		$res = $querypmp->result();
                		$log_user_mem_created_date = (!empty($res)) ?  $res[0]->created_date : "";
                		$log_user_mem_package_validity = (!empty($res)) ?  $res[0]->package_validity : "";
                		$log_user_mem_remaining_profiles = (!empty($res)) ?  $res[0]->remaining_profiles : "";
                		$log_user_mem_total_profiles_alloted = (!empty($res)) ?  $res[0]->total_profiles_alloted : "";
                		$log_user_mem_payment_mode = (!empty($res)) ?  $res[0]->payment_mode : "";
               
                  $profile_img = $this->User_model_api_3->get_profile_image(array("reg_id"=>$row1->id));
                  $city_get = $this->User_model_api_3->get_user_city(array("reg_profil_id"=>$row1->profile));
                  $personal_habits = $this->User_model_api_3->get_personal_habits(array("reg_id"=>$row1->id));
                  $get_family_info = $this->User_model_api_3->get_family_info(array("reg_id"=>$row1->id));
                  $get_education_details = $this->User_model_api_3->get_education_details(array("reg_id"=>$row1->id));
                  $horoscope = $this->User_model_api_3->get_horoscope_details(array("reg_id"=>$row1->id));
                  
                  //horoscope data
                  $rashi = (!empty($horoscope)) ? $horoscope[0]->rashi : "";
                  $charan = (!empty($horoscope)) ? $horoscope[0]->charan : "";
                  $nadi = (!empty($horoscope)) ? $horoscope[0]->nadi : "";
                  $mangal = (!empty($horoscope)) ? $horoscope[0]->mangal : "";
                  $nakshtra = (!empty($horoscope)) ? $horoscope[0]->nakshtra : "";
                  $gan = (!empty($horoscope)) ? $horoscope[0]->gan : "";
                  
                  //education details
                  $primary_edu = (!empty($get_education_details)) ? $get_education_details[0]->primary_edu : "";
                  $highest_education = (!empty($get_education_details)) ? $get_education_details[0]->highest_education : "";
                  $education_field = (!empty($get_education_details)) ? $get_education_details[0]->education_field : "";
                  $education = (!empty($get_education_details)) ? $get_education_details[0]->education : "";
                  $college_univ = (!empty($get_education_details)) ? $get_education_details[0]->college_univ : "";
                  $occup = (!empty($get_education_details)) ? $get_education_details[0]->occup : "";
                  $work_city = (!empty($get_education_details)) ? $get_education_details[0]->work_city : "";
                  $money = (!empty($get_education_details)) ? $get_education_details[0]->money : "";
                  
                  ///family info
                  $fatherrname = (!empty($get_family_info)) ? $get_family_info[0]->fatherrname : "";
                  $father_presence = (!empty($get_family_info)) ? $get_family_info[0]->father_presence : "";
                  $motherrname = (!empty($get_family_info)) ? $get_family_info[0]->motherrname : "";
                  $mother_presence = (!empty($get_family_info)) ? $get_family_info[0]->mother_presence : "";
                  $father_occupation = (!empty($get_family_info)) ? $get_family_info[0]->father_occupation : "";
                  $father_desgintion = (!empty($get_family_info)) ? $get_family_info[0]->father_desgintion : "";
                  $father_native_place = (!empty($get_family_info)) ? $get_family_info[0]->father_native_place : "";
                  $mother_desgintion = (!empty($get_family_info)) ? $get_family_info[0]->mother_desgintion : "";
                  $mother_occupation = (!empty($get_family_info)) ? $get_family_info[0]->mother_occupation : "";
                  $mother_native_place = (!empty($get_family_info)) ? $get_family_info[0]->mother_native_place : "";
                  $no_of_brother = (!empty($get_family_info)) ? $get_family_info[0]->no_of_brother : "";
                  $no_of_brother_married = (!empty($get_family_info)) ? $get_family_info[0]->no_of_brother_married : "";
                  $no_of_sister = (!empty($get_family_info)) ? $get_family_info[0]->no_of_sister : "";
                  $no_of_sister_married = (!empty($get_family_info)) ? $get_family_info[0]->no_of_sister_married : "";
                  $family_values = (!empty($get_family_info)) ? $get_family_info[0]->family_values : "";
                  $family_finacial_backg = (!empty($get_family_info)) ? $get_family_info[0]->family_finacial_backg : "";
                  $family_annual_income = (!empty($get_family_info)) ? $get_family_info[0]->family_annual_income : "";
                  $loan_libilities = (!empty($get_family_info)) ? $get_family_info[0]->loan_libilities : "";
                 
                  
                  //city data
    			  $city_name = (!empty($city_get)) ? $city_get[0]->perm_city : "";
    			  $perm_address = (!empty($city_get)) ? $city_get[0]->perm_address : "";
    			  $alt_mobile = (!empty($city_get)) ? $city_get[0]->alter_mobile : "";
    			  
    			  //personal habit
    			  $habits = (!empty($personal_habits)) ? $personal_habits[0]->hobbie : "";
    			  $diet = (!empty($personal_habits)) ? $personal_habits[0]->diet : "";
    			  $smooking = (!empty($personal_habits)) ? $personal_habits[0]->smooking : "";
    			  $drinking = (!empty($personal_habits)) ? $personal_habits[0]->drinking : "";
    			  $party_pub = (!empty($personal_habits)) ? $personal_habits[0]->party_pub : "";
    			  
    			  //profile img
    			  $file_path1 = FCPATH . 'uploads/'.$profile_img[0]->file_name;
    			  $file_name = (!empty($profile_img) && file_exists($file_path1)) ? $profile_img[0]->file_name : "";
    			  
    			  //basic details
    			  $today = date('Y-m-d');
    			  $diff = date_diff(date_create($row1->dob), date_create($today));
    	          $age = $diff->format('%y');
    	          $string = "'"; 
                  $position = '1'; 
                  $height_cal = substr_replace( $row1->height, $string, $position, 0 )." ft";  
                  
                  //calculate height
                    $inputs = $row1->height ;
                    if(isset($inputs[1]) && $inputs[1] === '0') {
                        $inputs[1] = "'";
                    }
                    $string = "'"; 
                    $position = '1'; 
                    $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                    $ht_cal = str_replace("''","'",$heights);
               //   ...................................
                  
                  
                  if($this->input->post("view_contact")=="0"){
                    
                        $arr1 = array("id"=>$row1->id,
                        "profile"=>$row1->profile,
                        'profile_created_for'=>$row1->profile_created_for,
                        "first_name"=>$row1->first_name,
                        "email"=>$row1->email,
                        "mobile"=>$row1->mobile,
                        "city"=>$city_name,
                        "address"=> $perm_address,
                        "alt_mobile"=> $alt_mobile,
                        "gender"=>$row1->gender,
                        "martial_status"=>$row1->martial_status,
                        "caste"=>$row1->caste,
                        "religion"=>$row1->religion,
                        "sub_caste"=>$row1->sub_caste,
                        "about_me"=>$row1->about_me,
                        "mother_tongue"=>$row1->mother_tongue,
                        "body_complexion"=>$row1->body_complexion,
                        "body_type"=>$row1->body_type,
                        "lens"=>$row1->lens,
                        "blood_group"=>$row1->blood_group,
                        "phy_disable"=>$row1->phy_disable,
                        "phy_disable_details"=>$row1->phy_disable_details,
                        "hobbies"=>$habits,
                        'diet'=>$diet,
                        'smooking'=>$smooking,
                        'drinking'=>$drinking,
                        'party_pub'=>$party_pub,
                        "marry_other_caste"=>$row1->marry_other_caste,
                        "dob"=>$row1->dob,
                        "profile_image"=>$file_name,
                        "height"=>$ht_cal,
                        "age"=>$age." Yrs",
                        "weight"=>$row1->weight." Kg",
                    
                  );
                  
                
                  
                 	$this->db->select("*");
            		$this->db->from('interest');  
            		$this->db->where('profile_id',$row1->id ); 
            		$this->db->where('logged_user_id',$log_user_profile );
            		$querypin = $this->db->get();
            		$interest = $querypin->result();
            		
            		$accept = (!empty($interest)) ? $interest[0]->accept : "";
                  
                  
                  if($accept == '1' || ($log_user_mem_payment_mode == 'Paid' &&  $log_user_mem_remaining_profiles != $log_user_mem_total_profiles_alloted && $log_user_mem_package_validity >= $todaydate)){
                  
                       $arr2 = array(
                           "father_name"=>$fatherrname,
                           "father"=>$father_presence,
                           "mother_name"=>$motherrname,
                           "mother_presence"=>$mother_presence,
                       );
                  
                  }else{
                       $arr2 = array();
                  }
                  
                  
                  $arr3 = array(
                           "father_occupation"=>$father_occupation,
                           "father_desgintion"=>$father_desgintion,
                           "father_native_place"=>$father_native_place,
                           "mother_desgintion"=>$mother_desgintion,
                           "mother_occupation"=>$mother_occupation,
                           "mother_native_place"=>$mother_native_place,
                           "no_of_brother"=>$no_of_brother,
                           "no_of_brother_married"=>$no_of_brother_married,
                           "no_of_sister"=>$no_of_sister,
                           "no_of_sister_married"=>$no_of_sister_married,
                            "family_values"=>$family_values,
                           "family_finacial_backg"=>$family_finacial_backg,
                           "family_annual_income"=>$family_annual_income,
                           "loan_libilities"=>$loan_libilities,
                           'primary_edu'=>$primary_edu,
                           'highest_education'=>$highest_education,
                           'education_field'=>$education_field,
                           'education'=>$education,
                           'college_univ'=>$college_univ,
                           'occup'=>$occup,
                           'money'=>$money." Lakh",
                           'work_city'=>$work_city,
                           'rashi'=>$rashi,
                           'charan'=>$charan,
                           'nadi'=>$nadi,
                           'mangal'=>$mangal,
                           'nakshtra'=>$nakshtra,
                           'gan'=>$gan,
                           
                       );
                    
                  
                  $mergedArray = array_merge($arr1, $arr2,$arr3);
                 
                  
               }else{
                   
                      $this->db->select("*");
                      $this->db->from('recently_viewed_profiles');  
                      $this->db->where('logged_profile_id',$log_user_profile );
                      $this->db->where('viewed_profile_id',$row1->profile );
                      $this->db->group_by('viewed_profile_id');
                      $queryr = $this->db->get(); 
                      
                       if ($queryr->num_rows() > 0){
                           
                         $recm_id =  $queryr->result()[0]->id;  
                         $this->db->set('contact_viewed', 'Yes');
                         $this->db->where('id', $recm_id);
                         $this->db->update('recently_viewed_profiles');
                		
                         
                          $queryccc = $this->db->query("SELECT *  FROM recently_viewed_profiles where logged_profile_id = '$log_user_profile' AND contact_viewed = 'Yes' AND created_date >= '$log_user_mem_created_date' AND created_date <= '$log_user_mem_package_validity' ");
                          $count_profiles =  $queryccc->num_rows();
                           if($count_profiles != $log_user_mem_remaining_profiles){
                             $remprofl =  $count_profiles;
                             $this->db->set('remaining_profiles', $remprofl);
                            //  $this->db->where('id', $log_user_membership_id);
                            $this->db->where('id', $log_user_profile);
                             $this->db->update('memberships');
                           }
                       }
                      
                   
                    $mergedArray = array("id"=>$row1->id,
                        "email"=>$row1->email,
                        "mobile"=>$row1->mobile,
                        "city"=>$city_name,
                        "address"=> $perm_address,
                        "alt_mobile"=> $alt_mobile,
                    
                    );
                    
                    // .........................................................................
                    
                            $msg = "Dear ".$log_user_profile.",<br/><br/>

                            We're reaching out to inform you that ".$data['profile_id']." has recently viewed your profile on Sundar Jodi. This could be the beginning of a meaningful connection. If you're interested, feel free to log in to your account to explore and engage.<br/><br/>
                            
                            Remember, great relationships often start with a simple connection. Wishing you the best in your journey on Sundar Jodi.<br/><br/>
                            
                            Warm regards,<br/>
                            Sundar Jodi Team";
                          
                          
                            $send_mail['form_name'] = 'SundarJodi';
                            $send_mail['form'] = 'help@sundarjodi.com';
                            $send_mail['message'] = $msg;
                            $send_mail['to'] = $row1->email;
                            $send_mail['subject'] = "Your Profile Viewed On Sundar Jodi";
                            $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
                              "from" => ["email" => "help@sundarjodi.com"],
                              "subject" => $send_mail['subject'], 
                              "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
                            $this->send_mail_curl($emailArray);
                    
                    // .........................Notification............................. ...........   
          
                   $message_body = "Contact viewed from Profile id: ".$data['profile_id'];
                   $message_title = "Viewed Contact";
                  
                    $this->db->where('profile', $data['profile_id']);
                    $query23 = $this->db->get('user_register');
                    $t = $query23->row_array();
                    if(!empty($query23->result())){
                    foreach ($query23->result() as $row3){
                        $tid =	$row3->id;
                        $token =	$row3->token;
                        if(!empty($token)){   
                            $notification = array();
                            $arrNotification= array();			
                            $arrData = array();		
                            $arrNotification["profile_id"] = $tid;
                            $arrNotification["row_id"] = $row1->id;
                            $arrNotification["message"] = $message_body;
                            $arrNotification["title"] = $message_title;
                            
                            $arrNotification["msg_type"] = 'viewed_contact';
                            $arrNotification["sound"] = "default";
                        
                            $check = $this->user->fcm($token, $arrNotification, "Android"); 
                            if($check){
                             $data = array(
                                    'logged_user' => $log_user_profile,
                                    'second_user' => $data['profile_id'],
                                    'title' => $message_title,
                                    'msg' => $message_body,
                                    'action' => 'viewed_contact',
                                     );
                                    $partner_program = $this->db->insert('notifications', $data);
                            }
                           }
                         }
                         
                    }
                    
                    // ........................
                    
               }
               
               
               }
       
               $this->response([
                    'status' => TRUE,
                    'data'=>$mergedArray,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
               
           }else{
               $this->response([
                    'status' => FALSE,
                    'message' => 'No record found'
                ], REST_Controller::HTTP_OK);
           }
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id & current login profile id'
                ], REST_Controller::HTTP_OK);
        }
        
    }
    
    //====================================================================//
    
    function profile_count_post(){
        
        $userlogin_id = $this->input->post("userlogin_id");
        $profile_id= $this->input->post("profile_id");
        
        if($userlogin_id!="" && $profile_id!=""){
            
          $query = $this->db->query("SELECT *  FROM interest where profile_id = '$userlogin_id' AND reject = 0 AND accept = 0 group by logged_user_id");
          $query_accept_interest_count = $this->db->query("SELECT *  FROM interest where profile_id = '$userlogin_id' AND reject = 0 AND accept = 1 group by logged_user_id");
          $viwed_your_profile_count = $this->db->query("SELECT viewed_profile_id  FROM recently_viewed_profiles where viewed_profile_id = '$profile_id' GROUP BY logged_profile_id ");
         
         
               $this->response([
                    'status' => TRUE,
                    'message' => 'Success',
                    'interest_received_count'=>$query->num_rows(),
                    'accept_interest_count'=>$query_accept_interest_count->num_rows(),
                    'viwed_your_profile_count'=>$viwed_your_profile_count->num_rows(),
                    'telephone'=>"+918421792179",
                    'mobile'=>"8421792179",
                    "whatsup_chat"=>"https://api.whatsapp.com/send?phone=918421792179",
                ], REST_Controller::HTTP_OK);
              
          
        
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide userlogin_id & profile_id'
                ], REST_Controller::HTTP_OK);
        }
    }
    
  
   //========================================================================//
   
   function get_user_data_post(){
       
       
       if($this->input->post("profile")){
           
          $data = $this->User_model_api_3->get_user_all_data(array("profile_id"=>$this->input->post("profile")));
          
          $user_id = $this->input->post("user_id");
          $logged_user_id = $this->input->post("logged_user_id");
          
          if($user_id==""){
              $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide user id'
                ], REST_Controller::HTTP_OK);
          }
          if($logged_user_id==""){
              $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide logged user id'
                ], REST_Controller::HTTP_OK);
          }
          
          $val = array();
          if(!empty($data)){
              foreach($data AS $vals){
                  
                        $this->db->select("*");
                		$this->db->from('user_register');  
                	    $this->db->where('profile',$vals->profile); 
                		$this->db->limit(1);
                		$query_user = $this->db->get();
                		
                		$userss_id  = (!empty($query_user->result())) ? $query_user->result()[0]->id : "";
                  
                        $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$userss_id);
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id','DESC'); 
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
                		$file  = (!empty($query3->result()) && $query3->result()[0]->file_name!="") ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                  
                          $string = "'"; 
                          $today = date('Y-m-d');
                                $diff = date_diff(date_create($vals->dob), date_create($today));
        	                    $age = $diff->format('%y');
        	              $position = '1'; 
                          $height_cal = substr_replace($vals->height, $string, $position, 0 )." ft";  
                          
                          //calculate height
                            $inputs = $vals->height ;
                            if(isset($inputs[1]) && $inputs[1] === '0') {
                                $inputs[1] = "'";
                            }
                            $string = "'"; 
                            $position = '1'; 
                            $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                            $ht_cal = str_replace("''","'",$heights);
                            $heightss= substr_replace($inputs, $string, $position, 0 );
                            $ht_cal_pt = str_replace("''",".",$heightss);
                       //   ...................................
                          
                          $city_get = $this->User_model_api_3->get_user_city(array("reg_profil_id"=>$vals->profile));
            			  $city_name = (!empty($city_get)) ? $city_get[0]->perm_city : "";
    			  
    			  		$this->db->select("*");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$logged_user_id);
                	    $this->db->where('profile_id',$user_id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$logged_user_id);
                	    $this->db->where('profile_id',$user_id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
    			       
                  
                  $val[] = array(
                      'id'=>$userss_id,
                      'profile'=>$vals->profile,
                      'first_name'=>$vals->first_name,
                      'mobile'=>$vals->mobile,
                      'email'=>$vals->email,
                      'watsapp_num'=>$vals->watsapp_num,
                      'gender'=>$vals->gender,
                      'martial_status'=>$vals->martial_status,
                      'child_frm_marriage'=>$vals->child_frm_marriage,
                      'childstaying'=>$vals->childstaying,
                      'caste'=>$vals->caste,
                      'religion'=>$vals->religion,
                      'sub_caste'=>$vals->sub_caste,
                       'marry_other_caste'=>$vals->marry_other_caste,
                      'profile_created_for'=>$vals->profile_created_for,
                      'dob'=>$vals->dob,
                      'age'=>$age." Yrs",
                      'blood_group'=>$vals->blood_group,
                      'mother_tongue'=>$vals->mother_tongue,
                      'body_type'=>$vals->body_type,
                      'body_complexion'=>$vals->body_complexion,
                      'weight'=>$vals->weight,
                      'height'=>$ht_cal,
                      'height_point'=>$ht_cal_pt,
                      'phy_disable'=>$vals->phy_disable,
                      'phy_disable_details'=>$vals->phy_disable_details,
                      'lens'=>$vals->lens,
                      'birth_time'=>$vals->birth_time,
                      'birth_city'=>$city_name,
                      'perm_address'=>$vals->perm_address,
                      'primary_edu'=>$vals->primary_edu,
                       'highest_education'=>$vals->highest_education,
                      'education_field'=>$vals->education_field,
                      'education'=>$vals->education,
                      'other_degree'=>$vals->other_degree,
                      'college_univ'=>$vals->college_univ,
                      'add_edu'=>$vals->add_edu,
                      'occup'=>$vals->occup,
                      'profession_details'=>$vals->profession_details,
                      'designation'=>$vals->designation,
                      'company_name'=>$vals->company_name,
                      'currency'=>$vals->currency,
                      'money'=>$vals->money,
                      'work_city'=>$vals->work_city,
                      'about_career'=>$vals->about_career,
                       'about_family'=>$vals->about_family,
                      'no_of_sister_married'=>$vals->no_of_sister_married,
                      'no_of_sister'=>$vals->no_of_sister,
                       'no_of_brother_married'=>$vals->no_of_brother_married,
                      'no_of_brother'=>$vals->education_field,
                      'mother_native_place'=>$vals->mother_native_place,
                      'mother_desgintion'=>$vals->mother_desgintion,
                      'mother_desgintion'=>$vals->mother_desgintion,
                      'mother_occupation'=>$vals->mother_occupation,
                      'mother_maternalrname'=>$vals->mother_maternalrname,
                      'mother_presence'=>$vals->mother_presence,
                      'motherrname'=>$vals->motherrname,
                      'father_native_place'=>$vals->father_native_place,
                      'father_desgintion'=>$vals->currency,
                      'father_occupation'=>$vals->father_occupation,
                      'father_presence'=>$vals->father_presence,
                      'fatherrname'=>$vals->fatherrname,
                       'relative_contact_no'=>$vals->relative_contact_no,
                      'relation_member'=>$vals->relation_member,
                      'relative_name'=>$vals->relative_name,
                      'separate_p'=>$vals->separate_p,
                      'intercaste_p'=>$vals->intercaste_p,
                      'family_current_location'=>$vals->family_current_location,
                       'relative_address'=>$vals->relative_address,
                      'family_values'=>$vals->family_values,
                      'family_finacial_backg'=>$vals->family_finacial_backg,
                      'family_annual_income'=>$vals->family_annual_income,
                      'loan_libilities'=>$vals->loan_libilities,
                      'other_libilities'=>$vals->other_libilities,
                      
                      'smooking'=>$vals->smooking,
                      'drinking'=>$vals->drinking,
                      'party_pub'=>$vals->party_pub,
                       'diet'=>$vals->diet,
                      'hobbie'=>$vals->hobbie,
                      'file_name'=>$file,
                      
                       'rashi'=>$vals->rashi,
                      'charan'=>$vals->charan,
                      'nadi'=>$vals->nadi,
                      'mangal'=>$vals->mangal,
                      'nakshtra'=>$vals->nakshtra,
                      'interest'=>$interest,
                      'favourite'=>$favourite,
                     'about_me'=>$vals->about_me,
                     'copy_link'=>"view_biodata/".$vals->profile
                      );
                  
              }
          }
          
          if(!empty($val)){
              
              $this->response([
                    'status' => TRUE,
                    'total' => count($val),
                    'data' => $val,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
          }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
          }
          
          
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id'
                ], REST_Controller::HTTP_OK);
        }
       
   }
   
   //==============================================================================================//
   
   function get_partner_data_post(){
       
       
       if($this->input->post("profile")){
           
          $data = $this->User_model_api_3->get_partner_data(array("profile"=>$this->input->post("profile")));
          
          if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
          }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
          }
          
          
        }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id & current login profile id'
                ], REST_Controller::HTTP_OK);
        }
       
   }
   
   //==============================================================================================//
   
   function recent_visitor_post(){
       
       $profile_id = $this->input->post("profile");
       
       
       if($profile_id!=""){
           
         
           $data = $this->User_model_api_3->get_recently_visitor(array("profile"=>$this->input->post("profile")));
       
           
           $arr = array();
            if(!empty($data)){
                foreach($data AS $val){
                    
                    $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$val->logged_profile_id,"payment_mode"=>"Paid"));
		            $current_logged_data = $this->User_model_api_3->get_user_profil_data(array("profile_id"=>$val->logged_profile_id));
          
         
          	            $this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$val->viewed_profile_id);
                	    $this->db->where('profile_id',$val->logged_profile_id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$val->viewed_profile_id);
                	    $this->db->where('profile_id',$val->logged_profile_id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$this->db->select("*");
                		$this->db->from('user_register');  
                	    $this->db->where('profile',$val->logged_profile_id); 
                		$this->db->limit(1);
                		$query4 = $this->db->get();
                		$partne_id  = (!empty($query4->result())) ? $query4->result()[0]->id: "";
                		
                		
                		if(!empty($partne_id)){
                		    $this->db->select("*");
                    		$this->db->from('profile_images');  
                    	    $this->db->where('reg_id',$partne_id); 
                    	    $this->db->where('main_pic','1');
                    	    $this->db->order_by('id','DESC'); 
                    		$this->db->limit(1);
                    		$query3 = $this->db->get();
                		
                		  $file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                		}else{
                		    $file =  "image/user-img.jpg";
                		}
                		
                        
                		
                		
                		
                		
		if(!empty($current_logged_data)){
		    
		                $today = date('Y-m-d');
                        $diff = date_diff(date_create($current_logged_data[0]->dob), date_create($today));
	                    $age = $diff->format('%y');
	                    
	                    
                    $arr[] = array(
                        "another_user_id"=>$val->user_id,
                            "id"=>$val->id,
                            "logged_profile_id"=>$val->logged_profile_id,
                            "viewed_profile_id"=>$val->viewed_profile_id,
                            "created_date"=>$val->created_date,
                            "contact_viewed"=>$val->contact_viewed,
                            "contact_viewed_date"=>$val->contact_viewed_date,
                            "last_view_profile"=>$val->last_view_profile,
                            "first_name"=>$current_logged_data[0]->first_name,
                            "email"=>$current_logged_data[0]->email,
                            "mobile"=>$current_logged_data[0]->mobile,
                            "gender"=>$current_logged_data[0]->gender,
                            "martial_status"=>$current_logged_data[0]->martial_status,
                            "caste"=>$current_logged_data[0]->caste,
                            "height"=>$current_logged_data[0]->height,
                            "dob"=>$current_logged_data[0]->dob,
                            
                             'file_name'=>$file,
                             "occup"=>$current_logged_data[0]->occup,
                             "perm_city"=>$current_logged_data[0]->perm_city,
                            //"profile_image"=>$file,
                            "age"=>$age." Yrs",
                            'interest'=>$interest,
                            'favourite'=>$favourite,
                            "premium_member"=>(!empty($paid_members)) ? "Yes" : "No",
                        );
                        
		}
                }
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $arr,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
           
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id & gender'
                ], REST_Controller::HTTP_OK);
        }
       
       
   }
   
   //==============================================================================================//
   
   function recently_viewed_profiles_post(){
       
       $profile_id = $this->input->post("profile");
       $user_id = $this->input->post("login_user_id");
       $gender = $this->input->post("gender");
       $data['limit'] = $this->input->post("limit");
       $data['offset'] = ($this->input->post("offset")!="") ? $this->input->post("offset") : 0;
       
       
        if($data['offset'] ==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'offset is required',
                ], REST_Controller::HTTP_OK);
        }
        if($data['limit']==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'Limit is required',
                ], REST_Controller::HTTP_OK);
        }


       
       if($user_id==""){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Login user id is required'
                ], REST_Controller::HTTP_OK);
       }
       
       if($profile_id!=""){
           
           
       $datas = $this->User_model_api_3->get_recently_profile(array("profile"=>$profile_id,'user_id'=>$user_id,'gender'=>$gender,'limit'=>$data['limit'],'offset'=>$data['offset']));
       
           $arr = array();
           
            if(!empty($datas)){
                
                foreach($datas AS $key){
                    
                    	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key['id']);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key['id']);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		
                    $paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key['profile'],"payment_mode"=>"Paid"));


                        $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace($key['height'], $string, $position, 0 );
	                    
	                    //calculate height
                        $inputs =$key['height'] ;
                        if(isset($inputs[1]) && $inputs[1] === '0') {
                            $inputs[1] = "'";
                        }
                        $string = "'"; 
                        $position = '1'; 
                        $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                        $ht_cal = str_replace("''","'",$heights);
                   //   ...................................
               
               
                    $arr[]= array(
                                "id"=>$key['id'],
                                "verified"=>$key['verified'],
                                "created_user"=>$key['verified'],
                                "first_name"=>$key['first_name'],
                                "profile"=>$key['profile'],
                                "caste"=>$key['caste'],
                                "dob"=>$key['dob'],
                                "height"=>$ht_cal,
                                "martial_status"=>$key['martial_status'],
                                "status"=>$key['status'],
                                "perm_city"=>$key['perm_city'],
                                "file_name"=>'uploads/'.$key['file_name'],
                                "occup"=>$key['occup'],
                                "education_field"=>$key['education_field'],
                                "payment_mode"=>$key['payment_mode'],
                                "sent"=>$key['sent'],
                                "favourite"=>$favourite,
                                "interest"=>$interest,
                                "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                        );
                }
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($datas),
                    'data' => $arr,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
           
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id'
                ], REST_Controller::HTTP_OK);
        }
       
       
   }
   
   //==============================================================================================//
   
   function match_of_the_day_post(){
       
       $cast =  $this->input->post("caste");
       $user_id =  $this->input->post("row_id");
       $data['limit'] = $this->input->post("limit");
       $data['offset'] = $this->input->post("offset");
       $gender = $data['gender'] = $this->input->post("gender");
       $user_marital_status = $data['user_marital_status'] = $this->input->post("user_marital_status");
       
       
       if($cast!="" && $gender!="" && $user_marital_status!=""){
           
            $valuesArray = explode(',', $cast);
            $cleanedValues = array_map(function($value) {
            $value = trim($value);
            $value = stripslashes($value);
            return "'" . addslashes($value) . "'"; 
            }, $valuesArray);
                    
            $data['caste'] = implode(',', $cleanedValues);
       
          $datas = $this->User_model_api_3->get_match_of_the_day_data($data);
       
       //$datas = $this->User_model_api_3->get_view_profiles_by($data);
             
             $key = array();
             if(!empty($datas)){
                 foreach($datas AS $val){
                     
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($val->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $val->height, $string, $position, 0 );
	                    //calculate height
                        $inputs = $val->height ;
                        if(isset($inputs[1]) && $inputs[1] === '0') {
                            $inputs[1] = "'";
                        }
                        $string = "'"; 
                        $position = '1'; 
                        $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                        $ht_cal = str_replace("''","'",$heights);
                   //   ...................................
	                    
	                    
	                    $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$val->user_id); 
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id', 'desc');
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
                // 		echo $this->db->last_query();
                		
                		$file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                     
                     
                     	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$val->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$val->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$val->profile,"payment_mode"=>"Paid"));
                     
                        $key[] = array(
                             "id"=>$val->user_id,
                             "profile"=>$val->profile,
                             "first_name"=>$val->first_name,
                             "email"=>$val->email,
                             "mobile"=>$val->mobile,
                             "gender"=>$val->gender,
                             "martial_status"=>$val->martial_status,
                             "child_frm_marriage"=>$val->child_frm_marriage,
                             "childstaying"=>$val->childstaying,
                             "caste"=>$val->caste,
                             "religion"=>$val->religion,
                             "sub_caste"=>$val->sub_caste,
                            "perm_city"=>$val->perm_city,
                             "occup"=>$val->occup,
                             "highest_education"=>$val->highest_education,
                             "body_type"=>$val->body_type,
                             "mother_tongue"=>$val->mother_tongue,
                             "dob"=>$val->dob,
                             "profile_created_for"=>$val->profile_created_for,
                             "marry_other_caste"=>$val->marry_other_caste,
                             "status"=>$val->status,
                             'age'=>$age." Yrs",
                             'height'=>$ht_cal,
                             'file_name'=>$file,
                             'interest'=>$interest,
                             'favourite'=>$favourite,
                             "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                             
                         );
                 }
             }
      
             if(!empty($key)){
              
                $this->response([
                    'status' => TRUE,
                    'total' => count($key),
                    'data' => $key,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
              }else{
                  
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No data found'
                    ], REST_Controller::HTTP_OK);
              }
           
       
       }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Please Provide all data'
                    ], REST_Controller::HTTP_OK);
       }
           
   }
   
   function view_profiles_by_post(){
            
            $data['gender'] = ($this->input->post("gender")=="F") ? "M" : "F";
            $data['profile_id'] = $this->input->post("profile");
            $user_id = $this->input->post("user_id");
            $data['profile_type'] = $this->input->post("profile_type");
            
            if($data['profile_id']==""){
                  $this->response([
                        'status' => FALSE,
                        'message' => 'Please Provide profile'
                    ], REST_Controller::HTTP_OK);
            }
             if($user_id==""){
                  $this->response([
                        'status' => FALSE,
                        'message' => 'Please current logged user id'
                    ], REST_Controller::HTTP_OK);
            }
            if($data['profile_type']==""){
                  $this->response([
                        'status' => FALSE,
                        'message' => 'Please Provide profile type'
                    ], REST_Controller::HTTP_OK);
            }
            
             if($data['gender']==""){
                  $this->response([
                        'status' => FALSE,
                        'message' => 'Please Provide gender'
                    ], REST_Controller::HTTP_OK);
            }
           
            
            if($this->input->post("profile_type")=="Never Married"){
               $data['marital_status'] = "Never Married"; 
            }else if($this->input->post("profile_type")=="Divorced"){
                $data['marital_status'] = "Divorced"; 
            }else if($this->input->post("profile_type")=="Awaiting Divorced"){
                $data['marital_status'] = "Awaiting Divorced"; 
            }
            else if($this->input->post("profile_type")=="Widowed"){
                $data['marital_status'] = "Widowed"; 
            }
            
            
             $datas = $this->User_model_api_3->get_view_profiles_by($data);
             
             $key = array();
             if(!empty($datas)){
                 foreach($datas AS $val){
                     
                        $today = date('Y-m-d');
                        $diff = date_diff(date_create($val->dob), date_create($today));
	                    $age = $diff->format('%y'); 
	                    $string = "'"; 
                        $position = '1'; 
	                    $height = substr_replace( $val->height, $string, $position, 0 );
	                    //calculate height
                        $inputs = $val->height ;
                        if(isset($inputs[1]) && $inputs[1] === '0') {
                            $inputs[1] = "'";
                        }
                        // $string = "'"; 
                        // $position = '1'; 
                        $heights= substr_replace($inputs, $string, $position, 0 );
                        $ht_cal = str_replace("''","'",$heights);
                   //   ...................................
	                    
	                     $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$val->id); 
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id', 'desc');
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
                		$file  = (!empty($query3->result())) ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                     
                     
                     	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$val->id);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$val->id);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                		
                		$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$val->profile,"payment_mode"=>"Paid"));
                     
                        $key[] = array(
                             "id"=>$val->id,
                             "profile"=>$val->profile,
                             "first_name"=>$val->first_name,
                             "email"=>$val->email,
                             "mobile"=>$val->mobile,
                             "gender"=>$val->gender,
                             "martial_status"=>$val->martial_status,
                             "child_frm_marriage"=>$val->child_frm_marriage,
                             "childstaying"=>$val->childstaying,
                             "caste"=>$val->caste,
                             "religion"=>$val->religion,
                             "sub_caste"=>$val->sub_caste,
                             "perm_city"=>$val->perm_city,
                             "occup"=>$val->occup,
                             "highest_education"=>$val->highest_education,
                             "body_type"=>$val->body_type,
                             "mother_tongue"=>$val->mother_tongue,
                             "dob"=>$val->dob,
                             "profile_created_for"=>$val->profile_created_for,
                             "marry_other_caste"=>$val->marry_other_caste,
                             "status"=>$val->status,
                             'age'=>$age." Yrs",
                             'height'=>$ht_cal,
                             'file_name'=>$file,
                             'interest'=>$interest,
                             'favourite'=>$favourite,
                             "premium_member"=>(!empty($paid_member)) ? "Yes" : "No",
                         );
                 }
             }
            
             if(!empty($key)){
              
                $this->response([
                    'status' => TRUE,
                    'total' => count($key),
                    'data' => $key,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
              }else{
                  
                  $this->response([
                        'status' => FALSE,
                        'message' => 'No data found'
                    ], REST_Controller::HTTP_OK);
              }
       
     
       
   }
   
   //=======================================================================//
   
   function premium_profiles_post(){
       
       
        $caste = $this->input->post("caste");
        $gender = $this->input->post("gender");
        $user_id = $this->input->post("login_user_id");
        $limit = $this->input->post("limit");
        $offset = ($this->input->post("offset")!="") ? $this->input->post("offset") : 0;
        
        if($limit==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Limit is required'
                    ], REST_Controller::HTTP_OK);
        }
        if($offset==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Offset is required'
                    ], REST_Controller::HTTP_OK);
        }

        //$data = $this->User_model_api_3->preminum_matched(array("payment_mode"=>"Paid","caste"=>$caste,"gender"=>$gender));
        
        if($user_id==""){
            $this->response([
                        'status' => FALSE,
                        'message' => 'Login user id is required'
                    ], REST_Controller::HTTP_OK);
        }

$this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite');
$this->db->from('memberships');
$this->db->join('user_register', 'user_register.profile = memberships.member_profile_id','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');

$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');


$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
// $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('memberships.payment_mode', 'Paid'); 
$this->db->where('user_register.gender', $gender); 
$this->db->where_in('user_register.caste', $caste);
$this->db->where('user_register.status', 1); 
$this->db->limit($limit, $offset);
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by('user_register.profile'); 
$query = $this->db->get();
  $prime = $query->result_array();
   $premium_tcount = $query->num_rows() ;
  
  
  $arr = array();
            if(!empty($prime)){
                
                
                foreach($prime AS $key){
                    
                    
                    	$this->db->select("profile_id,logged_user_id,sent_date");
                		$this->db->from('interest');  
                	    $this->db->where('logged_user_id',$user_id);
                	    $this->db->where('profile_id',$key['id']);
                	    $this->db->where('sent','1'); 
                	    $this->db->where('accept','0'); 
                	    $this->db->where('reject','0'); 
                		$query3 = $this->db->get()->result();
                		$interest = (!empty($query3)) ? "Yes" : "No";
                		
                		$this->db->select("profile_id,user_logged_id");
                		$this->db->from('favourites');  
                	    $this->db->where('user_logged_id',$user_id);
                	    $this->db->where('profile_id',$key['id']);
                		$query4 = $this->db->get()->result();
                		$favourite = (!empty($query4)) ? "Yes" : "No";
                    
                   		$paid_member = $this->User_model_api_3->check_membership(array("member_profile_id"=>$key['profile'],"payment_mode"=>"Paid"));
                      
                        ///height 
                        
                          $string = "'"; 
                          $position = '1'; 
                          $height_cal = substr_replace($key['height'], $string, $position, 0 )." ft";
                          
                          //calculate height
                        $inputs = $key['height'] ;
                        if(isset($inputs[1]) && $inputs[1] === '0') {
                            $inputs[1] = "'";
                        }
                        $string = "'"; 
                        $position = '1'; 
                        $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                        $ht_cal = str_replace("''","'",$heights);
                   //   ...................................
                        
                        
                        $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$key['id']);
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id ','desc');
                	    $this->db->limit(1);
                		$query_prof = $this->db->get()->result();
                        
                
					$file_name  = (!empty($query_prof)) ? "uploads/".$query_prof[0]->file_name : "image/user-img.jpg";
                     
                    $arr[] = array(
                        "id"=>$key['id'],
                        "verified"=>$key['verified'],
                        "created_user"=>$key['created_user'],
                        "first_name"=>$key['first_name'],
                        "profile"=>$key['profile'],
                        "caste"=>$key['caste'],
                        "dob"=>$key['dob'],
                        "height"=>$ht_cal,
                        "martial_status"=>$key['martial_status'],
                        "status"=>$key['status'],
                        "perm_city"=>$key['perm_city'],
                        "file_name"=>$file_name,
                        // "filez_name"=>$key['file_name'],
                        "occup"=>$key['occup'],
                        "education_field"=>$key['education_field'],
                        "payment_mode"=>$key['payment_mode'],
                        "sent"=>$key['sent'],
                        "favourite"=>$favourite,
                        'interest'=>$interest,
                        "premium_member"=>(!empty($paid_member)) ? "Yes" : "No", 
                        
                        );
                }
                
                
              
                $this->response([
                    'status' => TRUE,
                    'total_count' => $premium_tcount,
                    'data' => $arr,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
           
      
   }
 //=======================================================================//
   
   function get_data_for_edit_post(){
       
        $data['profile']  = $this->input->post("profile_id");
        $data['row_id']  = $this->input->post("row_id");
        $data['edit_form_name']  = trim($this->input->post("edit_form_name"));
           
           $data = $this->User_model_api_3->get_edit_info_data($data);
           
            if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
  //=======================================================================//
   
   function get_basic_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_basic_info_data($data);
           $array = array();
            if(!empty($data)){
                
                 $string = "'"; 
                 $position = '1'; 
               
                foreach($data AS $val){
                    
                    $city_get = $this->User_model_api_3->get_user_city(array("reg_profil_id"=>$val->profile));
    			    $city_name = (!empty($city_get)) ? $city_get[0]->perm_city : "";
    			  
                   $array[] = array(
                        "id"=>$val->id,
                        "profile"=>$val->profile,
                        "first_name"=>$val->first_name,
                        "email"=>$val->email,
                        "mobile"=>$val->mobile,
                        "watsapp_num"=>$val->watsapp_num,
                        "mobile_verified"=>$val->mobile_verified,
                        "gender"=>$val->gender,
                        "martial_status"=>$val->martial_status,
                        "child_frm_marriage"=>$val->child_frm_marriage,
                        "childstaying"=>$val->childstaying,
                        "caste"=>$val->caste,
                        "religion"=>$val->religion,
                        "sub_caste"=>$val->sub_caste,
                        "marry_other_caste"=>$val->marry_other_caste,
                        "profile_created_for"=>$val->profile_created_for,
                        "dob"=>$val->dob,
                        "blood_group"=>$val->blood_group,
                        "mother_tongue"=>$val->mother_tongue,
                        "body_type"=>$val->body_type,
                        "body_complexion"=>$val->body_complexion,
                        "weight"=>$val->weight,
                        "height"=>$val->height,
                        "phy_disable"=>$val->phy_disable,
                        "phy_disable_details"=>$val->phy_disable_details,
                        "birth_city"=>$city_name,
                         "birth_time"=>$val->birth_time,
                         "lens"=>$val->lens,
                         "about_me"=>$val->about_me,
                        );
                }
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $array,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
   //=======================================================================//
   
   function get_family_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_family_info_data($data);
           $array = array();
            if(!empty($data)){
                
                
                foreach($data AS $val){
                    
                    $query = $this->db->get('cities');
                    $this->db->where('id',$val->father_native_place); 
                    $father_native = $query->result();
                    $father_native = (!empty($father_native)) ? $father_native[0]->name : "";
                    
                    $query = $this->db->get('cities');
                    $this->db->where('id',$val->mother_native_place); 
                    $mother_native = $query->result();
                    $mother_native = (!empty($mother_native)) ? $mother_native[0]->name : "";
                    
                    $array[] = array(
                        "id"=>$val->id,
                        "reg_profil_id"=>$val->reg_profil_id,
                        "reg_id"=>$val->reg_id,
                        "fatherrname"=>$val->fatherrname,
                        "father_presence"=>$val->father_presence,
                        "father_occupation"=>$val->father_occupation,
                        "father_desgintion"=>$val->father_desgintion,
                        "father_native_place"=>$father_native,
                        "motherrname"=>$val->motherrname,
                        "mother_presence"=>$val->mother_presence,
                        "mother_maternalrname"=>$val->mother_maternalrname,
                        "mother_occupation"=>$val->mother_occupation,
                        "mother_desgintion"=>$val->mother_desgintion,
                        "mother_native_place"=>$mother_native,
                        "no_of_brother"=>$val->no_of_brother,
                        "no_of_brother_married"=>$val->no_of_brother_married,
                        "no_of_sister"=>$val->no_of_sister,
                        "no_of_sister_married"=>$val->no_of_sister_married,
                        "about_family"=>$val->about_family,
                        "family_current_location"=>$val->family_current_location,
                        "intercaste_p"=>$val->intercaste_p,
                        "separate_p"=>$val->separate_p,
                        "relative_name"=>$val->relative_name,
                        "relation_member"=>$val->relation_member,
                        "relative_contact_no"=>$val->relative_contact_no,
                        "relative_address"=>$val->relative_address,
                        "family_values"=>$val->family_values,
                        "family_finacial_backg"=>$val->family_finacial_backg,
                        "family_annual_income"=>$val->family_annual_income,
                        "loan_libilities"=>$val->loan_libilities,
                        "other_libilities"=>$val->other_libilities,
                        "created_date"=>$val->created_date,
                        );
                }
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $array,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
    //=======================================================================//
   
   function get_contact_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_contact_info_data($data);
           
            if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
    //=======================================================================//
   
   function get_habbit_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_habbit_info_data($data);
           
            if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
     //=======================================================================//
   
   function get_education_work_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_education_work_info_data($data);
           
            if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
   
      //=============================================================//
   
   function get_horoscope_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
           
           $data = $this->User_model_api_3->get_horoscope_info_data($data);
           
            if(!empty($data)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
    //=============================================================//
   
   function get_partner_expectation_info_post(){
       
        $data['profile']  = $this->input->post("profile_id");
        $data['limit'] = $this->input->post("limit");
        $data['offset'] = ($this->input->post("offset")!="") ? $this->input->post("offset") : 0;
        
        if($data['offset'] ==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'offset is required',
                ], REST_Controller::HTTP_OK);
        }
        if($data['limit']==""){
            $this->response([
                    'status' => FALSE,
                    'message' => 'Limit is required',
                ], REST_Controller::HTTP_OK);
        }
           
        $data = $this->User_model_api_3->get_partner_expectation_info_data($data);
           $array = array();
            if(!empty($data)){
                
                foreach($data as $val){
                   
                  //$height_from =   $this->replaceZeroWithDot($val->height_from);
                     $string = "."; 
                        $position = '1'; 
                        
                        
                    $array[] = array(
                        
                        "id"=> $val->id,
                        "reg_profil_id"=>$val->reg_profil_id,
                        "reg_id"=> $val->reg_id,
                        "marital_status"=> $val->marital_status,
                        "caste"=>$val->caste,
                        "age_from"=> $val->age_from,
                        "age_to"=> $val->age_to,
                        
                        "height_from"=>$this->replaceZeroWithDot($val->height_from)." ft",
                        "height_to"=> substr_replace( $val->height_to, $string, $position, 0 )." ft",
                        "highest_education"=> $val->highest_education,
                        "education_field"=>$val->education_field,
                        "primary_edu"=> $val->primary_edu,
                        "working_partner"=> $val->working_partner,
                        "occup"=> $val->occup,
                        "liv_city"=> $val->liv_city,
                        "liv_state"=> $val->liv_state,
                        "state_name"=> $val->state_name,
                        "liv_country"=> $val->liv_country,
                        "diet"=> $val->diet,
                        "smooking"=> $val->smooking,
                        "drinking"=> $val->drinking,
                        "show_profile_to"=>$val->show_profile_to,
                        "partner_pref"=> $val->partner_pref,
                        "created_date"=> $val->created_date,
                        );
                }
                
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($data),
                    'data' => $data,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
   }
     //=============================================================//
   
   function get_user_profile_photo_info_post(){
       
    $data['row_id']  = $this->input->post("row_id");
    
    
    if($data['row_id']!=""){
            $this->db->from('profile_images');  
    		$this->db->where('reg_id ',$data['row_id'] );
    		$this->db->order_by('id ','desc');
    		$query1 = $this->db->get();
    		$response =  $query1->result();
    
       
            if(!empty($response)){
              
              $this->response([
                    'status' => TRUE,
                    'total_count' => count($response),
                    'data' => $response,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
                
            }else{
              
              $this->response([
                    'status' => FALSE,
                    'message' => 'No data found'
                ], REST_Controller::HTTP_OK);
            }
            
        }else{
             $this->response([
                        'status' => FALSE,
                        'message' => 'Please provide row id'
                    ], REST_Controller::HTTP_OK);
              
        }
   }
  //==================================================== ============//
 
    public function edit_basic_info_post() {
        
        $profile_id = trim($this->post('profile_id'));
        $profile_created_for = trim($this->post('profile_created_for'));
        $dob = trim($this->post('dob'));
        $birth_time = trim($this->post('dob_time'));
        $birth_city = trim($this->post('birth_city'));
        $mother_tongue = trim($this->post('mother_tongue'));
        $weight = trim($this->post('weight'));
        $height = trim($this->post('height'));      
        $body_type = trim($this->post('body_type'));
        $body_complexion = trim($this->post('body_complexion'));
        $lens = trim($this->post('lens'));
        $marry_other_caste = trim($this->post('marry_other_caste'));
        $phy_disable = trim($this->post('phy_disable'));
        $phy_disable_details = trim($this->post('phy_disable_details'));
        $blood_group = trim($this->post('blood_group'));
        $sub_caste = trim($this->post('sub_caste'));
        $childstaying = trim($this->post('childstaying'));
        $child_frm_marriage = trim($this->post('child_frm_marriage'));
        $perm_country = trim($this->post('perm_country'));
        $perm_state = trim($this->post('perm_state'));
        $perm_city = trim($this->post('perm_city'));
        $perm_address = trim($this->post('perm_address'));
        $alter_mobile = trim($this->post('alter_mobile'));
        $fblink = trim($this->post('fblink'));
        $about_me = trim($this->post('about_me'));
        
        $diet = trim($this->post('diet'));
        $smooking = trim($this->post('smooking'));
        $drinking = trim($this->post('drinking'));
        $party_pub = trim($this->post('party_pub'));
        $hobbie = trim($this->post('hobbie'));
        
       $this->db->where('profile',$profile_id);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
 foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }}
        
    $this->db->where('reg_profil_id',$profile_id);
    $queryc = $this->db->get('contact_info');
    if($queryc->num_rows()>0){
 foreach ($queryc->result() as $rowc){
        $contactinfoid =	$rowc->id;
        }}
        
   $this->db->where('reg_profil_id',$profile_id);
    $queryha = $this->db->get('personal_habits');
    if($queryha->num_rows()>0){
 foreach ($queryha->result() as $rowha){
        $habbitinfoid =	$rowha->id;
        }}      
        
      
             $personalData = array();
          
                $personalData['profile_created_for'] = $profile_created_for;
                $personalData['dob'] = $dob;
                $personalData['birth_time'] = $birth_time;
                $personalData['birth_city'] = $birth_city;
                $personalData['mother_tongue'] = $mother_tongue;
                $personalData['weight'] = $weight;
                $personalData['height'] = $height;
                $personalData['body_type'] = $body_type;
                $personalData['body_complexion'] = $body_complexion;
                $personalData['lens'] = $lens;
                $personalData['marry_other_caste'] = $marry_other_caste;
                $personalData['phy_disable'] = $phy_disable;
                $personalData['phy_disable_details'] = $phy_disable_details;
                $personalData['blood_group'] = $blood_group;
                $personalData['child_frm_marriage'] = $child_frm_marriage;
                $personalData['childstaying'] = $childstaying;
                $personalData['about_me'] = $about_me;
                $personalData['sub_caste'] = $sub_caste;
             $personalDataupdate = $this->user->personal_data_update($personalData, $userid);
          
          
           $habbitData = array();
                $habbitData['diet'] = $diet;
                $habbitData['smooking'] = $smooking;
                $habbitData['drinking'] = $drinking;
                $habbitData['party_pub'] = $party_pub;
                $habbitData['hobbie'] = $hobbie;
                $habbitData['created_date'] = date('Y-m-d H:i:s');
                
          $habbitDataupdate = $this->user->habbit_data_update($habbitData, $habbitinfoid);
          
          if($personalDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data Updated successfully',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
    }
  /*******************************************************/  
    public function edit_contact_info_post() {
        
        $profile_id = trim($this->post('profile_id'));
       
        $perm_country = trim($this->post('perm_country'));
        $perm_state = trim($this->post('perm_state'));
        $perm_city = trim($this->post('perm_district'));
        $perm_address = trim($this->post('perm_address'));
        $alter_mobile = trim($this->post('alter_mobile'));
        $fblink = trim($this->post('fblink'));
  
    $this->db->where('reg_profil_id',$profile_id);
    $queryc = $this->db->get('contact_info');
    if($queryc->num_rows()>0){
 foreach ($queryc->result() as $rowc){
        $contactinfoid =	$rowc->id;
        }}
        
            $contactData = array();
                $contactData['perm_country'] = $perm_country;
                $contactData['perm_state'] = $perm_state;
                $contactData['perm_city'] = $perm_city;
                $contactData['perm_address'] = $perm_address;
                $contactData['alter_mobile'] = $alter_mobile;
                $contactData['fblink'] = $fblink;
                $contactData['created_date'] = date('Y-m-d H:i:s');
                
          $contactDataupdate = $this->user->contact_data_update($contactData, $contactinfoid);
          
          if($contactDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data Updated successfully',
                    
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
    }
     //========================================================================// 

   
    public function edit_family_info_post() {
        
        $profile_id = trim($this->post('profile_id'));
        $fatherrname = trim($this->post('father_name'));
        $father_presence = trim($this->post('father_presence'));
        $father_occupation = trim($this->post('father_occupation'));
        $father_native_place = trim($this->post('father_native_place'));
        $motherrname = trim($this->post('mother_name'));
        $mother_presence = trim($this->post('mother_presence'));  
        $mother_occupation = trim($this->post('mother_occupation'));
        $mother_native_place = trim($this->post('mother_native_place'));
        $no_of_brother = trim($this->post('no_of_brother'));
        $no_of_brother_married = trim($this->post('no_of_brother_married'));
        $no_of_sister = trim($this->post('no_of_sister'));
        $no_of_sister_married = trim($this->post('no_of_sister_married'));
        $intercaste_p = trim($this->post('intercaste_p'));
        $separate_p = trim($this->post('separate_p'));
        
        
        $family_values = trim($this->post('family_values'));
        $family_finacial_backg = trim($this->post('family_finacial_backg'));
        $family_annual_income = trim($this->post('family_annual_income'));
        $loan_libilities = trim($this->post('loan_libilities'));
        $about_family = trim($this->post('about_family'));
        
        $relative_name = trim($this->post('relative_name'));
        $relative_contact_no = trim($this->post('relative_contact_no'));
        $relation_member = trim($this->post('relation_member'));
         $relative_address = $this->input->post("relative_address");
       
       $this->db->where('reg_profil_id',$profile_id);
    $query12 = $this->db->get('family_information');
    if($query12->num_rows()>0){
 foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }}
       
       if(!empty($profile_id)  ){
       
        $personalData = array();
          
                $personalData['fatherrname'] = $fatherrname;
                $personalData['father_presence'] = $father_presence;
                $personalData['father_occupation'] = $father_occupation;
                $personalData['father_native_place'] = $father_native_place;
                $personalData['motherrname'] = $motherrname;
                $personalData['mother_presence'] = $mother_presence;
                $personalData['mother_occupation'] = $mother_occupation;
                $personalData['mother_native_place'] = $mother_native_place;
                $personalData['no_of_brother'] = $no_of_brother;
                $personalData['no_of_brother_married'] = $no_of_brother_married;
                $personalData['no_of_sister'] = $no_of_sister;
                $personalData['no_of_sister_married'] = $no_of_sister_married;
                $personalData['intercaste_p'] = $intercaste_p;
                $personalData['separate_p'] = $separate_p;
                $personalData['family_values'] = $family_values;
                $personalData['family_finacial_backg'] = $family_finacial_backg;
                $personalData['family_annual_income'] = $family_annual_income;
                $personalData['loan_libilities'] = $loan_libilities;
                $personalData['about_family'] = $about_family;
                $personalData['relative_name'] = $relative_name;
                $personalData['relative_contact_no'] = $relative_contact_no;
                $personalData['relation_member'] = $relation_member;
                $personalData['relative_address'] = $relative_address;
                
             $personalDataupdate = $this->user->family_data_update($personalData, $userid);
          
          if($personalDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data Updated successfully',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
       }else{
           $this->response([
                    'status' => false,
                    'message' => 'Provide complete information',
                    
                    ], REST_Controller::HTTP_OK);
       }
        
    } 
    
    //=====================================================================//
public function edit_education_work_post() {
        
        $profile_id = trim($this->post('profile_id'));
        $primary_edu = trim($this->post('primary_edu'));
        $highest_education = trim($this->post('highest_education'));
        $education_field = trim($this->post('education_field'));
        $education = trim($this->post('education'));
        $college_univ = trim($this->post('college_univ'));
        $occup = trim($this->post('occupation'));
        $designation = trim($this->post('designation'));
        $currency = trim($this->post('currency'));
        $money = trim($this->post('income'));
        $work_city= trim($this->post('work_city'));
        $company_name = $this->input->post("company_name");
        $loan = $this->input->post("loan");
        $additional_education = $this->input->post("additional_education");
        
        $this->db->where('reg_profil_id',$profile_id);
        $query12 = $this->db->get('education_work');
        if($query12->num_rows()>0){
        foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }}
        
       
       if(!empty($profile_id)){
       
                $personalData = array();
                $personalData['primary_edu'] = $primary_edu;
                $personalData['highest_education'] = $highest_education;
                $personalData['education_field'] = $education_field;
                $personalData['education'] = $education;
                $personalData['college_univ'] = $college_univ;
                $personalData['occup'] = $occup;
                $personalData['designation'] = $designation;
                $personalData['currency'] = $currency;
                $personalData['money'] = $money;
                $personalData['work_city'] = $work_city;
                $personalData['additional_education'] = $this->input->post("additional_education");
                $personalData['working_fields'] = $this->input->post("working_fields");
                $personalData['length_of_emp'] = $this->input->post("length_of_emp");
                $personalData['company_name'] = $company_name;
                $personalData['loan'] = $loan;
                $personalData['additional_education'] =   $additional_education;
                $personalDataupdate = $this->user->education_work_data_update($personalData, $userid);
          
          if($personalDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data Inserted successfully',
                    'data' => array(
                    'profile' => $profile_id,
                    )
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
       }else{
           $this->response([
                    'status' => false,
                    'message' => 'Provide complete information',
                    
                    ], REST_Controller::HTTP_BAD_REQUEST);
       }
        
    } 
    
    //=====================================================================//
public function edit_horoscope_post() {

        $profile_id = trim($this->post('profile_id'));
        $rashi = trim($this->post('rashi'));
        $charan = trim($this->post('charan'));
        $nadi = trim($this->post('nadi'));
        $nakshtra = trim($this->post('nakshtra'));
        $gan = trim($this->post('gan'));
        $devak = trim($this->post('devak'));
        $mangal = trim($this->post('mangal'));
        $gotra = trim($this->post('gotra'));
        
       $this->db->where('reg_profil_id',$profile_id);
    $query12 = $this->db->get('horoscope_details');
    if($query12->num_rows()>0){
 foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }}
       
       if(!empty($profile_id) ){
       
        $horoscopeData = array();
          
                $horoscopeData['rashi'] = $rashi;
                $horoscopeData['charan'] = $charan;
                $horoscopeData['nadi'] = $nadi;
                $horoscopeData['nakshtra'] = $nakshtra;
                $horoscopeData['gan'] = $gan;
                $horoscopeData['devak'] = $devak;
                $horoscopeData['mangal'] = $mangal;
                $horoscopeData['gotra'] = $gotra;
               
                
 $personalDataupdate = $this->user->horoscope_data_update($horoscopeData, $userid);
    
          
          if($personalDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data Updated successfully',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
       }else{
           $this->response([
                    'status' => false,
                    'message' => 'Provide complete information',
                    
                    ], REST_Controller::HTTP_BAD_REQUEST);
       }
}
//**************************************************************//
public function edit_partner_expectation_post() {
        
        $profile_id = trim($this->post('profile_id'));
        $marital_status = trim($this->post('marital_status'));
        $caste = trim($this->post('caste'));
        $age_from = trim($this->post('age_from'));
        $age_to = trim($this->post('age_to'));
        $height_from = trim($this->post('height_from'));
        $height_to = trim($this->post('height_to'));
        $highest_education = trim($this->post('highest_education'));
        $primary_edu = trim($this->post('primary_edu'));
        $education_field = trim($this->post('education_field'));
        $working_partner = trim($this->post('working_partner'));
        $liv_city = trim($this->post('liv_city'));
        $liv_state = trim($this->post('liv_state'));
        $state_name = trim($this->post('state_name'));
        $occup = trim($this->post('occup'));
        $diet = trim($this->post('diet'));
        $smooking = trim($this->post('smooking'));
        $drinking = trim($this->post('drinking'));
        
         
       $this->db->where('reg_profil_id',$profile_id);
    $query12 = $this->db->get('partner_expection');
    if($query12->num_rows()>0){
 foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }}
       
       if(!empty($profile_id) ){
       
        $partnerexpData = array();
          
                $partnerexpData['marital_status'] = $marital_status;
                $partnerexpData['caste'] = $caste;
                $partnerexpData['age_from'] = $age_from;
                $partnerexpData['age_to'] = $age_to;
                $partnerexpData['height_from'] = $height_from;
                $partnerexpData['height_to'] = $height_to;
                $partnerexpData['highest_education'] = $highest_education;
                $partnerexpData['primary_edu'] = $primary_edu;
                $partnerexpData['education_field'] = $education_field;
                $partnerexpData['working_partner'] = $working_partner;
                $partnerexpData['liv_city'] = $liv_city;
                $partnerexpData['liv_state'] = $liv_state;
                $partnerexpData['state_name'] = $state_name;
                $partnerexpData['occup'] = $occup;
                $partnerexpData['diet'] = $diet;
                $partnerexpData['smooking'] = $smooking;
                $partnerexpData['drinking'] = $drinking;
                
                
 $partnerexpDataupdate = $this->user->partnerexp_data_update($partnerexpData, $userid);
   
          if($partnerexpDataupdate){
              
              $this->response([
                    'status' => TRUE,
                    'message' => 'Data updated successfully',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
          }else{
              
              $this->response([
                    'status' => false,
                    'message' => 'Data Not Inserted',
                    'data' => array(
                    'profile' => $profile_id,
                    
                    )
                    ], REST_Controller::HTTP_OK);
              
          }
             
       }else{
           $this->response([
                    'status' => false,
                    'message' => 'Provide complete information',
                    
                    ], REST_Controller::HTTP_BAD_REQUEST);
       }
        
    } 
//===================================================================//
     function chat_save_post()
    {   
       
        $log_user_profile =   $this->input->post('logged_user_profile_id');   
        $user_profile =   $this->input->post('user_profile_id');  
        //$user_membershipid =   $this->input->post('user_membershipid'); 
        $message =  $this->input->post('message');
        
    $queryw1 = $this->db->query("SELECT *  FROM chat where chat_to = '$user_profile' AND chat_from = '$log_user_profile'");
 $chat_count1 = $queryw1->num_rows();

  $queryw2 = $this->db->query("SELECT *  FROM chat where chat_to = '$log_user_profile' AND chat_from = '$user_profile'");
 $chat_count2 = $queryw2->num_rows();
 $chat_count1;
 $chat_count2;    
     
        $insert_contact = array(  
                'chat_from'  => $log_user_profile,
                'chat_to'  => $user_profile, 
                'created_date'  =>  date('Y-m-d H:i:s'), 
                //'membership_id' => $user_membershipid, 
                'message'  => $message, 
                'is_sent'  => 1,
                ); 
    
if($chat_count2 == 0){
    
    if($chat_count1 <= 1){
        
    $insert_chat =  $this->db->insert('chat', $insert_contact);
     $this->response([
                    'status' => TRUE,
                    'message' => 'chat send',
                    'chat_count_from' => $chat_count1,
                    'chat_count_to' => $chat_count2,
                    
                ], REST_Controller::HTTP_OK);
    }else{
          $this->response([
                    'status' => TRUE,
                    'message' => 'wait for response. ',
                    
                ], REST_Controller::HTTP_OK);
    
    }
} else if($chat_count2 >= 0){
    
    $insert_chat =  $this->db->insert('chat', $insert_contact);
     $this->response([
                    'status' => TRUE,
                    'message' => 'chat send',
                    'chat_count_from' => $chat_count1,
                    'chat_count_to' => $chat_count2,
                    
                ], REST_Controller::HTTP_OK);
}   
         
}
 //=============================================================//
 function chat_list_post()
    { 
          $log_user_profile =   $this->input->post('logged_user_profile_id'); 
          
        $this->db->select('user_register.id,user_register.mobile,user_register.first_name,user_register.profile,user_register.status,profile_images.file_name');
$this->db->from('chat');

$this->db->join('user_register', 'user_register.profile = chat.chat_from','left');

$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
 
$this->db->where('chat.chat_to',$log_user_profile );
$this->db->order_by('chat.id', 'desc');
$this->db->group_by("chat.chat_from");

$query1 = $this->db->get();
  $chatlist = $query1->result_array();
  
	    
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'chatlist' => $chatlist,
                    
                ], REST_Controller::HTTP_OK);
    }
//=============================================================//
 function get_chat_msg_post()
    {
         $log_user_profile =   $this->input->post('logged_user_profile_id'); 
         $chat_userprofile =   $this->input->post('user_profile_id');
         
        $this->db->select('chat.message,chat.chat_from,chat.chat_to');
$this->db->from('chat')->group_start()
                                    ->where('chat_from', $log_user_profile)
                                    ->where('chat_to', $chat_userprofile)
                                    
                                    ->or_group_start()
                                            ->where('chat_from', $chat_userprofile)
                                    ->where('chat_to',$log_user_profile )
                                    ->order_by('id', 'asc')
                                    ->group_end()
                                    
                            ->group_end();



//$this->db->where('chat.chat_to',$log_user_profile );
$this->db->order_by('chat.id', 'desc');
//$this->db->group_by("chat.chat_from");

  $query1 = $this->db->get();
  $chatlist = $query1->result_array();
  if($chatlist){
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'chatlist' => $chatlist,
                    
                ], REST_Controller::HTTP_OK);
        
    }
          $this->response([
                    'status' => false,
                    'message' => 'No record found',
                    //'chatlist' => $chatlist,
                    
                ], REST_Controller::HTTP_OK);
    }
    
    
    
 
//=============================================================//
function send_interest_post(){
    
     $logged_user_id = $this->input->post('logged_user_id');
     $another_user_id = $this->input->post('another_user_id');
     $anotherprofile = "";
     
     
      $this->db->where('id',$logged_user_id);
     $query_logged_id = $this->db->get('user_register');
     $data_logged_id = $query_logged_id->result();
    
     
     $this->db->where('id',$another_user_id);
     $query2 = $this->db->get('user_register');
     
     
         foreach ($query2->result() as $row){
          $anotherprofile   =	$row->profile;
          $anotheremail   =	$row->email;
          $anotherfirst_name   =	$row->first_name;
          $anothermartial_status   =	$row->martial_status;
          $anothercaste = $row->caste;
        }
       
        if($anotherprofile==""){
            $this->response([
                    'status' => false,
                    'message' => 'Another profile id is not found',
                    //'chatlist' => $chatlist,
                    
                ], REST_Controller::HTTP_OK);
        }
        
        $this->db->where('id',$logged_user_id);
    $query22 = $this->db->get('user_register');
      $another_profile = $query22->result();
      
      foreach ($query22->result() as $row1){
         $logprofile   =	$row1->profile;
        }
        
           $data_cont = array(
                'profile_id' => $another_user_id,
                'logged_user_id' => $logged_user_id,
                'sent' => 1,
                'sent_date' => date('Y-m-d H:i:s'),
             );
             
            $this->db->select('*');
            $this->db->from('interest');
            $this->db->where('profile_id', $another_user_id);
            $this->db->where('logged_user_id', $logged_user_id);
            $this->db->where('sent', 1);
            $query1 = $this->db->get();
            
        if($query1->num_rows() > 0){
          foreach ($query1->result() as $row4){
            $ints_id   =	$row4->id;
        }
         $delete = $this->db->delete('interest',array('id'=>$ints_id));  
        }
                
    //        
    //         $this->response([
    //                 'status' => TRUE,
    //                 'message' => 'Profile remove from interest',
                 
    //             ], REST_Controller::HTTP_OK);
                
                
         
    //     }else{
        $intr =     $this->db->insert('interest', $data_cont); 
        
    //  ............................................ ...............
    
    $this->db->select("*");
    $this->db->from('education_work');  
    $this->db->where('reg_id',$another_user_id ); 
    $this->db->limit(1);
    $education_detals = $this->db->get();
    $edu_details = $education_detals->result();
    
    if(!empty($edu_details)){
        $highest_education =$edu_details[0]->highest_education;
        $education_field =$edu_details[0]->education_field;
        $education =$edu_details[0]->education;
        $occup =$edu_details[0]->occup;
        $profession_details =$edu_details[0]->profession_details;
        $work_city =$edu_details[0]->work_city;
    }else{
        $highest_education = $education_field = $education = $occup = "";
    }
    
    $message =   '
    <html >
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
<link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
     <style>
  body{font-family: "Open Sans", sans-serif ! important;} 
  p,h1,h2,h3,h4,h5,h6,a,span{font-family: "Open Sans", sans-serif ! important;}
  @media only screen and (max-device-width: 479px) {
 .main_div{ 
  margin: auto;width: 100% ! important; background-color:#fff;padding: 10px;
}
}
     </style>
</head>

<body style="padding:0; margin:0">
    <table class="main_div" style=" margin: auto;width: 60%; background-color:#fff;padding: 10px;">
    <tr>
        <td>
        <table style="border:0;width: 100%">
        
        <tr>
            <td style="border:0;width: 100%;">
                <h3 style="text-align:center;color: #ff004d;font-size:25px;width: 100%;">'.$anotherprofile.' likes your profile <br>
                <span style="font-size:18px;">( '.date("Y-m-d H:i:s").' )</span>
                </h3>
            </td>
        </tr>
        
    </table>
    
    <div style="background-color:#ECF0F1;padding: 10px;">
    <p style="color:#34495E;font-size:14px;"> Dear '.$anotherfirst_name.' ('.$anotherprofile.'), </p>
     <p style="color:#34495E;font-size:14px;">'.$data_logged_id[0]->profile.' viewed your profile and shown interest 
                to communicate with you further. Please view "'.$data_logged_id[0]->profile.'" 
                profile and confirm your decision of accept/decline.</p>
    </div>
   
    <table style="border:0;width: 100%;">
        <tr>
          
             <td style="border:0;width:70%;">
              <span style="font-size:14px;color:#34495E;"> Caste: '.$anothercaste.'</span><br>
                <span style="font-size:14px;color:#34495E;"> Martial status: '.$anothermartial_status.'</span><br>
                 <span style="font-size:14px;color:#34495E;"> Educataion: '.$highest_education.'('.$education_field.')</span><br>
                  <span style="font-size:14px;color:#34495E;"> Occupation: '.$occup.'</span><br>
            </td>
            
        </tr>
        
        
    </table>
    </a>
    <br/>
    <p style="font-size:16px ! important;color:#34495E;">Best Wishes <br>
    Team sundarjodi</p><br/>
     <table style="border:0;width: 100%;background-color:#ff004d;padding:10px">
        <tr style=">
           
            <td style="border:0;width:33%;text-align:center;">
                <a href="mailto: help@sundarjodi.com" style="color:#fff;text-decoration:none;">help@sundarjodi.com</a>
            </td>
            <td style="border:0;width:33%;text-align:center;">
                <a href="tel: +918421792179" style="color:#fff;text-decoration:none;">+91 8421792179</a>
            </td>
            
        </tr>
       
    </table>
    
        </td>
    </tr>
    
    </table>
  </body></html> 
    
    ';
    
        
        $send_mail['form_name'] = 'SundarJodi';
        $send_mail['form'] = 'help@sundarjodi.com';
        $send_mail['message'] = $message;
        $send_mail['to'] = $anotheremail;
        $send_mail['subject'] ="New Interest Received";
        $emailArray = ["personalizations" => [["to" => [["email" => $send_mail['to']]]]], 
          "from" => ["email" => "help@sundarjodi.com"],
          "subject" => $send_mail['subject'], 
          "content" => [["type" => "text/html", "value" => $send_mail['message']]]]; 
        $this->send_mail_curl($emailArray);
    
        
        
    // .........................Notification............................. ...........   
          
   $message_body = "New Interest Received from Profile id: ".$logprofile;
   $message_title = "Interest Received";
  // $message_photo = "https://sundarjodi.com/graphic_app/designs_uploads/30faa7732dd59463cab93479d537f627.png";
  
    $this->db->where('profile', $anotherprofile);
    $query23 = $this->db->get('user_register');
    $t = $query23->row_array();
    if(!empty($query23->result())){
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        if(!empty($token)){   
            $notification = array();
            $arrNotification= array();			
            $arrData = array();		
            $arrNotification["profile_id"] = $logprofile;
            $arrNotification["row_id"] = $logged_user_id;
            $arrNotification["message"] = $message_body;
            $arrNotification["title"] = $message_title;
            
            $arrNotification["msg_type"] = 'interest_recive';
            $arrNotification["sound"] = "default";
        
            $check = $this->user->fcm($token, $arrNotification, "Android"); 
            if($check){
             $data = array(
                   
                    'logged_user' => $anotherprofile,
                    'second_user' => $logprofile,
                    'title' => $message_title,
                    'msg' => $message_body,
                    'action' => 'interest_recive',
                     );
                    $partner_program = $this->db->insert('notifications', $data);
            }
           }
         }
         
    }


    
         $this->response([
                    'status' => true,
                    //'message' => 'Notification send',
                   // 'check' => $check,
                    'message' => 'Interest Sent',
                    //'token' => $token,
                ], REST_Controller::HTTP_OK);
   
            
        // }
      
}
 //=============================================================//
 public function package_membership_post()
  {
      
       $this->db->select('*');
        $this->db->from('packages_membership');
        $this->db->where('status', 'Active');
         $this->db->order_by("sequence_order", "asc");
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $pack = $query1->result();
            
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'packages' => $pack,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                  
                    
                ], REST_Controller::HTTP_OK);
       
            }
        
  }
//========================================================================//   
 
   public function contact_count_day_post(){
    $profile = trim($this->post('loged_profile_id'));   
$today = date("Y-m-d");
     $querycc = $this->db->query("SELECT * FROM recently_viewed_profiles where contact_viewed_date = '$today' AND contact_viewed = 'Yes' AND logged_profile_id = '$profile' ");
   $count_view_profile =  $querycc->num_rows(); 
   
   if($count_view_profile == 5){
    $this->response([
                    'status' => false,
                    'message' => 'You can view only 5 Contacts in 24 hours.
You can view more contacts after the given hour limit.',
                    
                ], REST_Controller::HTTP_OK);
      }else{
          $this->response([
                    'status' => true,
                    'message' => 'success',
                    
                ], REST_Controller::HTTP_OK);
      }
   
   }
   
 //========================================================================//  
 
     function profile_percent_post(){
         
         $total = "19"; //"19"
         $logged_profil_id = $this->input->post("logged_profil_id");
         
         $res = $this->get_user_dt($logged_profil_id);
         
         if(!empty($res)){
         
         
         //basic info
         $profile_created_for = ($res[0]['profile_created_for']!="") ? 1 : 0;
         $height_point = ($res[0]['height_point']!="") ? 1 : 0;
         $dob = ($res[0]['dob']!="") ? 1 : 0;
         $mother_tongue = ($res[0]['mother_tongue']!="") ? 1 : 0;
         $martial_status = ($res[0]['martial_status']!="") ? 1 : 0;
         
         
         //contact info
   
         $perm_country = ($res[0]['perm_country']!="") ? 1 : 0;
         $perm_city = ($res[0]['perm_city']!="") ? 1 : 0;
         $perm_state = ($res[0]['perm_state']!="") ? 1 : 0;
         $perm_address = ($res[0]['perm_address']!="") ? 1 : 0;
         
         //family information
         
          $fatherrname = ($res[0]['fatherrname']!="") ? 1 : 0;
          $motherrname = ($res[0]['motherrname']!="") ? 1 : 0;
          $family_values = ($res[0]['family_values']!="") ? 1 : 0;
          
          //education
          
          $college_univ = ($res[0]['college_univ']!="") ? 1 : 0;
          $education = ($res[0]['education']!="") ? 1 : 0;
          $occup = ($res[0]['occup']!="") ? 1 : 0;
          $money = ($res[0]['money']!="") ? 1 : 0;
          $work_city = ($res[0]['work_city']!="") ? 1 : 0;
          
          //horoscope
          $rashi = ($res[0]['rashi']!="") ? 1 : 0;
          
          //file name
          $file_name = ($res[0]['file_name']!="" && $res[0]['file_name']!="image/user-img.jpg") ? 1 : 0;
          
         $final = $profile_created_for + $height_point + $dob + $mother_tongue + $martial_status + 
         $perm_country + $perm_city + $perm_state +$perm_address + $fatherrname + 
         $motherrname + $family_values + $college_univ +  $occup + $money + 
         $education+ $work_city + $rashi + $file_name;
        //  var_dump($profile_created_for , $height_point , $dob , $mother_tongue , $martial_status , 
        //  $perm_country , $perm_city , $perm_state ,$perm_address , $fatherrname , 
        //  $motherrname , $family_values , $college_univ ,  $occup , $money , 
        //  $education, $work_city , $rashi , $file_name);die;
         
        //  $total = "100";
         
         $calculate_percentage = ($final/$total)*100;
         
          $this->response([
                    'status' => true,
                    'message' => 'success',
                    'total_percent' => floor($calculate_percentage),
                    
                ], REST_Controller::HTTP_OK);
         
         }
     }
     
     function get_user_dt($profile_id=""){
       
       
       if($profile_id){
           
          $data = $this->User_model_api_3->get_user_all_data(array("profile_id"=>$profile_id));
          
        
          
          $val = array();
          if(!empty($data)){
              foreach($data AS $vals){
                  
                        $this->db->select("*");
                		$this->db->from('user_register');  
                	    $this->db->where('profile',$vals->profile); 
                		$this->db->limit(1);
                		$query_user = $this->db->get();
                		
                		$userss_id  = (!empty($query_user->result())) ? $query_user->result()[0]->id : "";
                		
                		
                		$this->db->select("*");
                		$this->db->from('contact_info');  
                	    $this->db->where('reg_profil_id',$vals->profile); 
                		$query_con = $this->db->get();
                		$perm_country = $perm_state = $perm_address = $perm_city = "";
                		if(!empty($query_con->result())){
                		    $perm_country = $query_con->result()[0]->perm_country;
                		    $perm_state = $query_con->result()[0]->perm_state;
                		    $perm_city = $query_con->result()[0]->perm_city;
                		    $perm_address = $query_con->result()[0]->perm_address;
                		}
                  
                        $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$userss_id);
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id','DESC'); 
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
                		$file  = (!empty($query3->result()) && $query3->result()[0]->file_name!="") ? "uploads/".$query3->result()[0]->file_name : "image/user-img.jpg";
                  
                          $string = "'"; 
                          $today = date('Y-m-d');
                                $diff = date_diff(date_create($vals->dob), date_create($today));
        	                    $age = $diff->format('%y');
        	              $position = '1'; 
                          $height_cal = substr_replace($vals->height, $string, $position, 0 )." ft";  
                          
                          //calculate height
                            $inputs = $vals->height ;
                            if(isset($inputs[1]) && $inputs[1] === '0') {
                                $inputs[1] = "'";
                            }
                            $string = "'"; 
                            $position = '1'; 
                            $heights= substr_replace($inputs, $string, $position, 0 )." ft";
                            $ht_cal = str_replace("''","'",$heights);
                            $heightss= substr_replace($inputs, $string, $position, 0 );
                            $ht_cal_pt = str_replace("''",".",$heightss);
                       //   ...................................
                          
                          $city_get = $this->User_model_api_3->get_user_city(array("reg_profil_id"=>$vals->profile));
            			  $city_name = (!empty($city_get)) ? $city_get[0]->perm_city : "";
    			  
                  $val[] = array(
                      'id'=>$userss_id,
                      'profile'=>$vals->profile,
                      'first_name'=>$vals->first_name,
                      'mobile'=>$vals->mobile,
                      'email'=>$vals->email,
                      'watsapp_num'=>$vals->watsapp_num,
                      'gender'=>$vals->gender,
                      'martial_status'=>$vals->martial_status,
                      'child_frm_marriage'=>$vals->child_frm_marriage,
                      'childstaying'=>$vals->childstaying,
                      'caste'=>$vals->caste,
                      'religion'=>$vals->religion,
                      'sub_caste'=>$vals->sub_caste,
                       'marry_other_caste'=>$vals->marry_other_caste,
                      'profile_created_for'=>$vals->profile_created_for,
                      'dob'=>$vals->dob,
                      'age'=>$age." Yrs",
                      'blood_group'=>$vals->blood_group,
                      'mother_tongue'=>$vals->mother_tongue,
                      'body_type'=>$vals->body_type,
                      'body_complexion'=>$vals->body_complexion,
                      'weight'=>$vals->weight,
                      'height'=>$ht_cal,
                      'height_point'=>$ht_cal_pt,
                      'phy_disable'=>$vals->phy_disable,
                      'phy_disable_details'=>$vals->phy_disable_details,
                      'lens'=>$vals->lens,
                      'birth_time'=>$vals->birth_time,
                      'birth_city'=>$city_name,
                      'primary_edu'=>$vals->primary_edu,
                       'highest_education'=>$vals->highest_education,
                      'education_field'=>$vals->education_field,
                      'education'=>$vals->education,
                      'other_degree'=>$vals->other_degree,
                      'college_univ'=>$vals->college_univ,
                      'add_edu'=>$vals->add_edu,
                      'occup'=>$vals->occup,
                      'profession_details'=>$vals->profession_details,
                      'designation'=>$vals->designation,
                      'company_name'=>$vals->company_name,
                      'currency'=>$vals->currency,
                      'money'=>$vals->money,
                      'work_city'=>$vals->work_city,
                      'about_career'=>$vals->about_career,
                       'about_family'=>$vals->about_family,
                      'no_of_sister_married'=>$vals->no_of_sister_married,
                      'no_of_sister'=>$vals->no_of_sister,
                       'no_of_brother_married'=>$vals->no_of_brother_married,
                      'no_of_brother'=>$vals->education_field,
                      'mother_native_place'=>$vals->mother_native_place,
                      'mother_desgintion'=>$vals->mother_desgintion,
                      'mother_desgintion'=>$vals->mother_desgintion,
                      'mother_occupation'=>$vals->mother_occupation,
                      'mother_maternalrname'=>$vals->mother_maternalrname,
                      'mother_presence'=>$vals->mother_presence,
                      'motherrname'=>$vals->motherrname,
                      'father_native_place'=>$vals->father_native_place,
                      'father_desgintion'=>$vals->currency,
                      'father_occupation'=>$vals->father_occupation,
                      'father_presence'=>$vals->father_presence,
                      'fatherrname'=>$vals->fatherrname,
                       'relative_contact_no'=>$vals->relative_contact_no,
                      'relation_member'=>$vals->relation_member,
                      'relative_name'=>$vals->relative_name,
                      'separate_p'=>$vals->separate_p,
                      'intercaste_p'=>$vals->intercaste_p,
                      'family_current_location'=>$vals->family_current_location,
                       'relative_address'=>$vals->relative_address,
                      'family_values'=>$vals->family_values,
                      'family_finacial_backg'=>$vals->family_finacial_backg,
                      'family_annual_income'=>$vals->family_annual_income,
                      'loan_libilities'=>$vals->loan_libilities,
                      'other_libilities'=>$vals->other_libilities,
                      
                      'smooking'=>$vals->smooking,
                      'drinking'=>$vals->drinking,
                      'party_pub'=>$vals->party_pub,
                       'diet'=>$vals->diet,
                      'hobbie'=>$vals->hobbie,
                      'file_name'=>$file,
                      
                       'rashi'=>$vals->rashi,
                      'charan'=>$vals->charan,
                      'nadi'=>$vals->nadi,
                      'mangal'=>$vals->mangal,
                      'nakshtra'=>$vals->nakshtra,
                      'perm_country'=>$perm_country,
                      'perm_state'=>$perm_state,
                      'perm_city'=>$perm_city,
                       'perm_address'=>$vals->perm_address,
                      );
                  
              }
          }else{
               $this->response([
                    'status' => FALSE,
                    'message' => 'Profile id is not found'
                ], REST_Controller::HTTP_OK);
              
          }
          
          
         
         return $val;
          
       }else{
            $this->response([
                    'status' => FALSE,
                    'message' => 'Please provide profile id'
                ], REST_Controller::HTTP_OK);
        }
       
   }
 
   public function profile_complete_percent_post(){
       $total = '20';
       $con['conditions'] = $con2['conditions'] = array('reg_profil_id' => $this->post('logged_profil_id'));
       $con3['conditions'] = array('profile' => $this->post('logged_profil_id'));
       $con['returnType'] = $con2['returnType'] = $con3['returnType'] ='single';
       $contact_detail = $this->User_model_api_3->getcontactinfo_details($con);
      // var_dump($contact_detail);
       $city = $contact_detail['perm_city'];
       
       
       $family_detail = $this->User_model_api_3->getfamily_details($con2);
       $family_values = $family_detail['family_values'];
       
       $user_detail = $this->User_model_api_3->getusers_details($con3);
       $name =  $user_detail['first_name'];
       $mobile = $user_detail['mobile'];
       $gender = $user_detail['gender'];
       $martial_status = $user_detail['martial_status'];
       $caste = $user_detail['caste'];
       $profile_created_for = $user_detail['profile_created_for'];
       $height = $user_detail['height'];
       $birth_time = $user_detail['birth_time'];
       
       $this->db->select("*");
                		$this->db->from('profile_images');  
                	    $this->db->where('reg_id',$user_detail['id']); 
                	    $this->db->where('main_pic','1');
                	    $this->db->order_by('id', 'desc');
                		$this->db->limit(1);
                		$query3 = $this->db->get();
                		
    if($query3->num_rows() > 0){
        $file = '1';
    }
    
   
    
       $this->response([
                    'status' => true,
                    'message' => 'success',
                    'total_percent' => '20',
                    
                ], REST_Controller::HTTP_OK);
   }
 //===========================================================//  
}    