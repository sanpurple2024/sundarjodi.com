<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Load the Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Authentication extends REST_Controller {

    public function __construct() { 
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        // Load the user model
        $this->load->model('user');
    }
    
    public function login_post() {
        $token = strip_tags($this->post('token'));
        // Get the post data
        $mobile = $this->post('mobile');
        $password = $this->post('password');
        
        // Validate the post data
        if(!empty($mobile) && !empty($password)){
            
            // Check if any user exists with the given credentials
            $con['returnType'] = 'single';
            $con['conditions'] = array(
                'mobile' => $mobile,
                'password' => sha1($password),
                
            );
            $user = $this->user->getRows($con);
            
            if($user){
                // Set the response and exit
                $this->db->select("*");
                $this->db->from('user_register');  
                $this->db->where('mobile',$mobile ); 
                $querypci = $this->db->get();
                foreach($querypci->result() as $row_pf_2)
                {
                    
              $pf_t_id =  $row_pf_2->id;
              $status =  $row_pf_2->status;
              
              $discount_percent =  $row_pf_2->discount_percent;
              $discount_validity =  $row_pf_2->discount_validity;
            //   $pf_t_id =  $row_pf_2->id;
                }
                
               if($status != '3'){ 
                
                
                $this->db->set('token', $token);
                $this->db->where('id', $pf_t_id);
                $register =   $this->db->update('user_register');
                
           $anotherdate = Date('Y-m-d', strtotime('+15 days')); 
            $NewDate = Date('Y-m-d', strtotime('+3 days')); 
            $today = Date('Y-m-d'); 
            
        $query3 = $this->db->query("SELECT profile, DATEDIFF(CURDATE(), login_session) AS 'no_of_days' from user_register where mobile = '$mobile' ");
       // $count_check = $query->result_array();
        foreach ($query3->result() as $row31){ 
            $userprofile =	$row31->profile;
            $no_of_days =	$row31->no_of_days;
        }
        
        if($no_of_days > 30 && $no_of_days < 60){
            
            $disocunt = '60';
            $validity = $NewDate;
        }else if($no_of_days > 60 && $no_of_days < 90) {
            
            
            $disocunt = '70';
            $validity = $NewDate;
        }else if($no_of_days > 90 ) {
            
            $disocunt = '80';
            $validity = $NewDate;
        }else if($no_of_days < 30 ){
             $festivaloffer = '45';
            
             $disocunt = $festivaloffer;
             $validity = $anotherdate;
        }
        if($discount_validity == '0000-00-00'){
                $this->db->set('discount_percent', $disocunt);
                $this->db->set('discount_validity', $validity);
                $this->db->where('id', $pf_t_id);
                $register =   $this->db->update('user_register');
        }
        $login_time =   date('Y-m-d H:i:s');
            $this->db->set('login_session', $login_time);
            $this->db->where('mobile', $mobile);
            $result=$this->db->update('user_register');
            
           $data_logged = array(
             'mobile' => $mobile,   
            'logged_date_time' => date('Y-m-d H:i:s'),
             );
        $query_in =  $this->db->insert('user_logged_info', $data_logged);
        
        
                $this->response([
                    'status' => TRUE,
                    'message' => 'User login successful.',
                    'data' => $user
                ], REST_Controller::HTTP_OK);
                
               }else{
                     $this->response([
                    'status' => false,
                    'message' => 'Invalid mobile number or password.',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
               }     
                
            }else{
                
                 $this->response([
                    'status' => false,
                    'message' => 'Invalid mobile number or password.',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
                
                // Set the response and exit
                //BAD_REQUEST (400) being the HTTP response code
           // $this->response("Wrong mobile number or password.", REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            // Set the response and exit
            $this->response("Provide mobile number and password.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }
//========================================================================// 
function getRandomString($length = 9) {
    $characters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}
function getsearchid($length = 5) {
    $characters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}
//========================================================================// 
    public function registration_post() {
        // Get the post data
        $first_name = trim($this->post('first_name'));
        $gender = trim($this->post('gender'));
        $email = trim($this->post('email'));
        $password = $this->post('password');
        $phone = trim($this->post('mobile'));
        $martial_status = trim($this->post('martial_status'));
        $caste = trim($this->post('caste'));
        $otp = rand(10000,99999);
        $profile = $this->getRandomString();
        
        $token = trim($this->post('token'));
        
        
        // Validate the post data
        if(!empty($first_name) && !empty($phone) && !empty($email) && !empty($password) && !empty($caste) && !empty($martial_status)){
            
            // Check if the given email already exists
            $con['returnType'] = 'count';
            $con['conditions'] = array(
                'mobile' => $phone,
            );
            $userCount = $this->user->getRows($con);
            
            if($userCount > 0){
                
                $this->response([
                    'status' => false,
                    'message' => 'The given mobile already exists.',
                   // 'data' => $user
                ], REST_Controller::HTTP_OK);
                
               
            }else{
                // Insert user data
                $userData = array(
                    'first_name' => $first_name,
                    'gender' => $gender,
                    'profile' => $profile,
                    'email' => $email,
                    'password' => sha1($password),
                    'mobile' => $phone,
                    'martial_status' => $martial_status,
                    'caste' => $caste,
                    'created_user' => date("Y-m-d H:i:s"),
                    'login_session' => date("Y-m-d H:i:s"),
                    'status'  => '0',
                    'usercode'  => $password,
                    'token'  => $token,
                    'unread'  => 1,
                    'otp' => $otp,
                    'signupBy' => 'App',
                    'discount_percent' => '45',
                    'discount_validity' => date("Y-m-d H:i:s"),
                    
                );
                $insert = $this->user->insert($userData);
                
                // Check if the user data is inserted
                if($insert){
                    
        $authKey = "310291AGuGg48FZ2k5e060d12P1";
          $senderId = "SUNDAR";
          $route = "4";
            //Multiple mobiles numbers separated by comma
            $mobileNumber = '91'.$phone;
            //Your message to send, Add URL encoding here.
            
 $message = urlencode("Sundarjodi : Your verification code is ".$otp." PupH6VD96vr");
           
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
            
            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //get response
            $output = curl_exec($ch);
            //Print error if any
            if(curl_errno($ch))
            {
                 'error:' . curl_error($ch);
            }
            curl_close($ch);
             $output;
        //-------------------------------------------------------//    
        
        
        //-------------------------------------------------------//
                    
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
            // $ho_id = $this->db->insert('horoscope_details', $data_cont);
         //   $this->session->set_userdata('user_id', $insert_reg);
            
            $data_member = array(
            'member_profile_id' => $profile,
            'total_profiles_alloted' => '10',
            'remaining_profiles' => '0',
            'package_validity' => date('Y-m-d H:i:s', strtotime(' + 100 days')),
            'payment_mode' => 'Free',
            'payment_status' => 'Success',
            'status' => '1',
            'gender' => $gender,
            'created_date' => date('Y-m-d H:i:s')
            
             );
            $member = $this->db->insert('memberships', $data_member); 
           
            $data_logged = array(
             'mobile' => $phone,   
            'logged_date_time' => date('Y-m-d H:i:s'),
             );
             $logged_info = $this->db->insert('user_logged_info', $data_logged);
             
             
                
                    // Set the response and exit
                    $this->response([
                    'status' => TRUE,
                    'message' => 'The user has been added successfully.',
                    'data' => array(
                    'id' => $insert,
                    'first_name' => $first_name,
                    'gender' => $gender,
                    'profile' => $profile,
                    'email' => $email,
                    'password' => sha1($password),
                    'mobile' => $phone,
                    'martial_status' => $martial_status,
                    'caste' => $caste,
                    'otp' => $otp,
                    )
                    ], REST_Controller::HTTP_OK);
                }else{
                     $this->response([
                        'status' => false,
                        'message' => 'Some problems occurred, please try again',
                        //'data' => $insert
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    
        
                }
            }
        }else{
             $this->response([
                        'status' => false,
                        'message' => 'Provide complete information',
                        //'data' => $insert
                    ], REST_Controller::HTTP_BAD_REQUEST);
     
        }
    }
//========================================================================//   
    public function resendotp_post() {
        // Get the post data
    $phone_num = strip_tags($this->post('mobile'));
    $otp = rand(10000,99999);
    
      if(!empty($phone_num)){
          $this->db->select("*");
                $this->db->from('user_register');  
                $this->db->where('mobile',$phone_num ); 
                $querypci = $this->db->get();
                foreach($querypci->result() as $row_pf_2)
                {
               $pf_t_id =  $row_pf_2->id;
                }
            $this->db->set('otp', $otp);
        $this->db->where('id', $pf_t_id);
        $update_otp = $this->db->update('user_register');
        if($update_otp){
            
        $authKey = "310291AGuGg48FZ2k5e060d12P1";
          $senderId = "SUNDAR";
          $route = "4";
            //Multiple mobiles numbers separated by comma
            $mobileNumber = '91'.$phone_num;
            //Your message to send, Add URL encoding here.
            $message = urlencode("Sundarjodi : Your verification code is ".$otp." PupH6VD96vr");
           
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
            
            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //get response
            $output = curl_exec($ch);
            //Print error if any
            if(curl_errno($ch))
            {
                echo 'error:' . curl_error($ch);
            }
            curl_close($ch);
             $output;  
            
            
             $this->response([
                    'status' => true,
                    'message' => 'Otp Send To Your Mobile Number',
                    'data' => array(
                    'mobile' => $phone_num,
                   // 'otp' => $otp,
                    )
                ], REST_Controller::HTTP_OK);
        }
        }else{
            // Set the response and exit
            $this->response("Provide mobile number", REST_Controller::HTTP_BAD_REQUEST);
        }
    }

//========================================================================//
function forgetpass_checkmbl_post(){
     
     $c_mobile = trim($this->post('mobile'));
     
     $this->db->where('mobile',$c_mobile);
    $query2 = $this->db->get('user_register');
      foreach ($query2->result() as $row){
        $mobile1 =	$row->mobile;
        
        }
      
      if(empty($mobile1 )){
          $this->response([
                    'status' => true,
                    'message' => 'Mobile Number Not Found',
                    'data' => array(
                    'mobile' => $c_mobile,
                   // 'otp' => $otp,
                    )
                ], REST_Controller::HTTP_OK);
         // echo 'Mobile Number is already registered';
      }else{
            $otp = rand(10000,99999);
          $mobile = $mobile1;
          
          //Your authentication key
          $authKey = "310291AGuGg48FZ2k5e060d12P1";
          $senderId = "SUNDAR";
          $route = "4";
            //Multiple mobiles numbers separated by comma
            $mobileNumber = '91'.$mobile;
            //Your message to send, Add URL encoding here.
            $message = urlencode("Sundarjodi : Your verification code is ".$otp." PupH6VD96vr");
           
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
            
            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //get response
            $output = curl_exec($ch);
            //Print error if any
            if(curl_errno($ch))
            {
                echo 'error:' . curl_error($ch);
            }else{
                   $this->response([
                    'status' => TRUE,
                    'message' => 'Otp Send',
                    'data' => array(
                    'mobile' => $mobile,
                    'otp' => $otp,
                    )
                    ], REST_Controller::HTTP_OK);
            }
            curl_close($ch);
            //echo $output;
            
         
            
      }
    }
//========================================================================//   
    public function update_newpasswrd_post() {
        
        $c_mobile = trim($this->post('mobile'));
         $new_password = trim($this->post('password'));
     
     $this->db->where('mobile',$c_mobile);
    $query2 = $this->db->get('user_register');
      foreach ($query2->result() as $row){
        $userid =	$row->id;
        $profile_id =	$row->profile;
        }
    $this->db->set('password', sha1($new_password));
    $this->db->set('usercode', $new_password);
    $this->db->where('id', $userid);
  $register =   $this->db->update('user_register');
    if($register){
        
        $this->response([
                    'status' => TRUE,
                    'message' => 'Password updated',
                    'data' => array(
                    'mobile' => $c_mobile,
                    'profile' => $profile_id,
                    )
                    ], REST_Controller::HTTP_OK);
    }    
    }
//========================================================================//   
    public function deleteuser_post() {
        // Get the post data
    $phone_num = strip_tags($this->post('mobile'));
     $this->db->where('mobile',$phone_num);
    $query2 = $this->db->get('user_register');
    if($query2->num_rows()>0){
      foreach ($query2->result() as $row){
        $userid =	$row->id;
        }
        $delete = $this->db->delete('user_register',array('id'=>$userid));
    if($delete){
            $this->response([
                    'status' => TRUE,
                    'message' => 'Data deleted successfully',
                    'data' => array(
                    'mobile' => $phone_num,
                    
                    )
                    ], REST_Controller::HTTP_OK);
    }  
    }else{
         $this->response([
                    'status' => TRUE,
                    'message' => 'No record found',
                    
                    ], REST_Controller::HTTP_OK);
    }
    }
//========================================================================//   
    public function verifymobile_post() {
        // Get the post data
    $phone_num = strip_tags($this->post('mobile'));
    $otp = strip_tags($this->post('otp'));
    
     $this->db->where('mobile',$phone_num);
     $this->db->where('otp',$otp);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
 foreach ($query12->result() as $row1){
        $userid =	$row1->id;
        }
            $this->response([
                    'status' => TRUE,
                    'message' => 'OTP Match',
                    
                    ], REST_Controller::HTTP_OK);
      
    }else{
         $this->response([
                    'status' => false,
                    'message' => 'Incorrect OTP',
                    
                    ], REST_Controller::HTTP_OK);
    }
    }
    
//========================================================================// 

    public function personal_information_post() {
        
        $profile_id = strip_tags($this->post('profile'));
        $profile_created_for = strip_tags($this->post('profile_created_for'));
        $dob = strip_tags($this->post('dob'));
        $birth_time = strip_tags($this->post('birth_time'));
        $birth_city = strip_tags($this->post('birth_city'));
        $mother_tongue = strip_tags($this->post('mother_tongue'));
        $weight = strip_tags($this->post('weight'));
        $height = strip_tags($this->post('height'));      
        $body_type = strip_tags($this->post('body_type'));
        $body_complexion = strip_tags($this->post('body_complexion'));
        $lens = strip_tags($this->post('lens'));
        $marry_other_caste = strip_tags($this->post('marry_other_caste'));
        $phy_disable = strip_tags($this->post('phy_disable'));
        $blood_group = strip_tags($this->post('blood_group'));
        $sub_caste = strip_tags($this->post('sub_caste'));
        $childstaying = strip_tags($this->post('childstaying'));
        $child_frm_marriage = strip_tags($this->post('child_frm_marriage'));
        
        
        $perm_country = strip_tags($this->post('perm_country'));
        $perm_state = strip_tags($this->post('perm_state'));
        $perm_city = strip_tags($this->post('perm_city'));
        $perm_address = strip_tags($this->post('perm_address'));
        $alter_mobile = strip_tags($this->post('alter_mobile'));
        $fblink = strip_tags($this->post('fblink'));
        
        $diet = strip_tags($this->post('diet'));
        $smooking = strip_tags($this->post('smooking'));
        $drinking = strip_tags($this->post('drinking'));
        $party_pub = strip_tags($this->post('party_pub'));
        $hobbie = strip_tags($this->post('hobbie'));
        
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
                $personalData['blood_group'] = $blood_group;
             $personalDataupdate = $this->user->personal_data_update($personalData, $userid);
          
          $contactData = array();
                $contactData['perm_country'] = $perm_country;
                $contactData['perm_state'] = $perm_state;
                $contactData['perm_city'] = $perm_city;
                $contactData['perm_address'] = $perm_address;
                $contactData['alter_mobile'] = $alter_mobile;
                $contactData['fblink'] = $fblink;
                $contactData['created_date'] = date('Y-m-d H:i:s');
                
          $contactDataupdate = $this->user->contact_data_update($contactData, $contactinfoid);
          
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
             
      
        
    }
    
   //========================================================================// 

   
    public function family_information_post() {
        
        $profile_id = strip_tags($this->post('profile'));
        $fatherrname = strip_tags($this->post('fatherrname'));
        $father_presence = strip_tags($this->post('father_presence'));
        $father_occupation = strip_tags($this->post('father_occupation'));
        $father_native_place = strip_tags($this->post('father_native_place'));
        $motherrname = strip_tags($this->post('motherrname'));
        $mother_presence = strip_tags($this->post('mother_presence'));  
        $mother_occupation = strip_tags($this->post('mother_occupation'));
        $mother_native_place = strip_tags($this->post('mother_native_place'));
        $no_of_brother = strip_tags($this->post('no_of_brother'));
        $no_of_brother_married = strip_tags($this->post('no_of_brother_married'));
        $no_of_sister = strip_tags($this->post('no_of_sister'));
        $no_of_sister_married = strip_tags($this->post('no_of_sister_married'));
        $intercaste_p = strip_tags($this->post('intercaste_p'));
        $separate_p = strip_tags($this->post('separate_p'));
        
        
        $family_values = strip_tags($this->post('family_values'));
        $family_finacial_backg = strip_tags($this->post('family_finacial_backg'));
        $family_annual_income = strip_tags($this->post('family_annual_income'));
        $loan_libilities = strip_tags($this->post('loan_libilities'));
        $about_family = strip_tags($this->post('about_family'));
        $relative_name = strip_tags($this->post('relative_name'));
        
        $relative_contact_no = strip_tags($this->post('relative_contact_no'));
        $relation_member = strip_tags($this->post('relation_member'));
       
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
                
             $personalDataupdate = $this->user->family_data_update($personalData, $userid);
          
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
                    
                    ], REST_Controller::HTTP_OK);
       }
        
    } 
//=====================================================================//
public function education_work_post() {
        
        $profile_id = strip_tags($this->post('profile'));
        $primary_edu = strip_tags($this->post('primary_edu'));
        $highest_education = strip_tags($this->post('highest_education'));
        $education_field = strip_tags($this->post('education_field'));
        $education = strip_tags($this->post('education'));
        $college_univ = strip_tags($this->post('college_univ'));
        $occup = strip_tags($this->post('occup'));
        $designation = strip_tags($this->post('designation'));
        $currency = strip_tags($this->post('currency'));
        $money = strip_tags($this->post('money'));
        $work_city= strip_tags($this->post('work_city'));
       
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
public function horoscope_post() {
        
        $profile_id = strip_tags($this->post('profile'));
        $rashi = strip_tags($this->post('rashi'));
        $charan = strip_tags($this->post('charan'));
        $nadi = strip_tags($this->post('nadi'));
        $nakshtra = strip_tags($this->post('nakshtra'));
        $gan = strip_tags($this->post('gan'));
        $devak = strip_tags($this->post('devak'));
        $mangal = strip_tags($this->post('mangal'));
        $gotra = strip_tags($this->post('gotra'));
        $about_me= strip_tags($this->post('about_me'));
       
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
   $this->db->set('about_me', $about_me);
    $this->db->set('unread', '1');
    $this->db->where('profile', $profile_id);
    $this->db->update('user_register');
    
          
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
public function partner_expectation_post() {
        
        $profile_id = strip_tags($this->post('profile'));
        $marital_status = strip_tags($this->post('marital_status'));
        $caste = strip_tags($this->post('caste'));
        $age_from = strip_tags($this->post('age_from'));
        $age_to = strip_tags($this->post('age_to'));
        $height_from = strip_tags($this->post('height_from'));
        $height_to = strip_tags($this->post('height_to'));
        $highest_education = strip_tags($this->post('highest_education'));
        $primary_edu = strip_tags($this->post('primary_edu'));
        $education_field = strip_tags($this->post('education_field'));
        $working_partner = strip_tags($this->post('working_partner'));
        $liv_city = strip_tags($this->post('liv_city'));
        $liv_state = strip_tags($this->post('liv_state'));
        $state_name = strip_tags($this->post('state_name'));
        $occup = strip_tags($this->post('occup'));
        $diet = strip_tags($this->post('diet'));
        $smooking = strip_tags($this->post('smooking'));
        $drinking = strip_tags($this->post('drinking'));
        $partner_pref = strip_tags($this->post('partner_pref'));
         
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
                $partnerexpData['partner_pref'] = $partner_pref;
                
 $partnerexpDataupdate = $this->user->partnerexp_data_update($partnerexpData, $userid);
   
          if($partnerexpDataupdate){
              
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

//========================================================================//    

//========================================================================//  
 public function sms_post() {
//      $this->db->where('mobile','9665703267');
//     $query12 = $this->db->get('user_register');
//     if($query12->num_rows()>0){
//  foreach ($query12->result() as $row1){
     
      // $phone_num =	$row1->mobile;
        $phone_num = 9665703267;
      $authKey = "310291AGuGg48FZ2k5e060d12P1";
          $senderId = "SUNDAR";
          $route = "4";
            //Multiple mobiles numbers separated by comma
            $mobileNumber = '91'.$phone_num;
            //Your message to send, Add URL encoding here.
            $message = urlencode("Ganesh Utsav offer! Get flat 40% discount on Membership! Offer available till 31-Aug 2020. For queries call 8421792179. ");
           
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
            
            //Ignore SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //get response
            $output = curl_exec($ch);
            //Print error if any
            if(curl_errno($ch))
            {
                echo 'error:' . curl_error($ch);
            }
            curl_close($ch);
             $output;   
        
  //}}
        
     
 }
 //=============================================================//
 
  public function cities_post(){   
        $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('country_id', '101');
        $this->db->order_by("name", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
           
        }else{
          return 0;
        }
        
        $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'cities' => array($t),
                ], REST_Controller::HTTP_OK);
                
        
        
    }
 
 //=============================================================//
 
  public function states_post(){   
        $this->db->select('*');
        $this->db->from('states');
        $this->db->where('country_id', '101');
        $this->db->order_by("name", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
            
        }else{
          return 0;
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                     'states' => array($t),
                ], REST_Controller::HTTP_OK);
       
    }
/**********************************************************************/
     public function districts_post(){   
         $state_id  = strip_tags($this->post('state_id')); 
        $this->db->select('*');
        $this->db->from('district');
        $this->db->where('state_id', $state_id);
        $this->db->order_by("name", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
            
        }else{
          return 0;
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'disctricts' => array($t),
                ], REST_Controller::HTTP_OK);
       
    }
/**********************************************************************/
     public function get_districts_post(){   
       
        $this->db->select('*');
        $this->db->from('district');
        //$this->db->where('state_id', $state_id);
        $this->db->order_by("name", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
            
        }else{
          return 0;
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'disctricts' => $t,
                ], REST_Controller::HTTP_OK);
       
    }
 //================================================================//
  public function uppic_post()
  {
    $profile_id  = strip_tags($this->post('profile'));
     $this->db->where('profile',$profile_id);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
     foreach ($query12->result() as $row1){
        $user_id =	$row1->id;
        }}
        
    $config['upload_path'] = '../uploads/';
    $config['allowed_types'] = 'jpg|jpeg|png';
   $config['max_size'] = '0';
    $imganame = $_FILES["file_name"]["name"];

    $this->load->library('upload', $config);
   

    //check if a file is being uploaded
    if(strlen($imganame)>0){

        if ( !$this->upload->do_upload("file_name"))//Check if upload is unsuccessful
        {
            $error = array('error' => $this->upload->display_errors());
           // print_r($errors);
           //  echo $errors;
              $this->response([
                    'status' => TRUE,
                    'message' => $errors
                ], REST_Controller::HTTP_OK);
        }
        else
        {
            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload->upload_path.$this->upload->file_name;
            $filename = $_FILES['file_name']['tmp_name'];


            $imgdata=@exif_read_data($this->upload->upload_path.$this->upload->file_name, 'IFD0');
               
         $userData = array(
                'reg_id' => $user_id,
                'file_name' => $this->upload->file_name
            );

             $upimage = $this->db->insert('profile_images', $userData);
             if($upimage){
                // echo 'Image save into database';
                  $upload = 'ok'; 
                   $this->response([
                    'status' => TRUE,
                    'message' => 'Image uploaded successfully.'
                ], REST_Controller::HTTP_OK);
             }
            list($width, $height) = getimagesize($filename);
            if ($width >= $height){
                $config['width'] = 500;
            }
            else{
                $config['height'] = 500;
            }
            $config['master_dim'] = 'auto';


            $this->load->library('image_lib',$config); 

            if (!$this->image_lib->resize()){  
                echo "error";
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
   $upload;
  }
   //================================================================//
  public function updoc_post()
  {
    $doc_type  = strip_tags($this->post('doc_type'));
    $profile_id  = strip_tags($this->post('profile'));
     $this->db->where('profile',$profile_id);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
     foreach ($query12->result() as $row1){
        $user_id =	$row1->id;
        }}
        
    $config['upload_path'] = '../uploads/';
    $config['allowed_types'] = 'jpg|jpeg|png';
   $config['max_size'] = '0';
    $imganame = $_FILES["file_name"]["name"];

    $this->load->library('upload', $config);
   

    //check if a file is being uploaded
    if(strlen($imganame)>0){

        if ( !$this->upload->do_upload("file_name"))//Check if upload is unsuccessful
        {
            $error = array('error' => $this->upload->display_errors());
           // print_r($errors);
           //  echo $errors;
              $this->response([
                    'status' => TRUE,
                    'message' => $errors
                ], REST_Controller::HTTP_OK);
        }
        else
        {
            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload->upload_path.$this->upload->file_name;
            $filename = $_FILES['file_name']['tmp_name'];


            $imgdata=@exif_read_data($this->upload->upload_path.$this->upload->file_name, 'IFD0');
               
         $userData = array(
                'reg_id' => $user_id,
                'file_name' => $this->upload->file_name,
                'doc_type' => $doc_type,
                'identity_badge' => '1',
            );

             $upimage = $this->db->insert('user_documents', $userData);
             if($upimage){
                // echo 'Image save into database';
                  $upload = 'ok'; 
                   $this->response([
                    'status' => TRUE,
                    'message' => 'Document uploaded successfully.'
                ], REST_Controller::HTTP_OK);
             }
            list($width, $height) = getimagesize($filename);
            if ($width >= $height){
                $config['width'] = 500;
            }
            else{
                $config['height'] = 500;
            }
            $config['master_dim'] = 'auto';


            $this->load->library('image_lib',$config); 

            if (!$this->image_lib->resize()){  
                echo "error";
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
   $upload;
  }
  
   public function profile_documents_post()
  {
        $profile_id  = strip_tags($this->post('profile'));
     $this->db->where('profile',$profile_id);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
     foreach ($query12->result() as $row1){
        $user_id =	$row1->id;
        }}
        
        $this->db->select('*');
        $this->db->from('user_documents');
        $this->db->where('reg_id', $user_id);
        $this->db->order_by("id", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
            
        }else{
          return 0;
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'images' => array($t),
                ], REST_Controller::HTTP_OK);
  }
  
  //============================================================//
  
  
 public function profile_image_post()
  {
        $profile_id  = strip_tags($this->post('profile'));
     $this->db->where('profile',$profile_id);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
     foreach ($query12->result() as $row1){
        $user_id =	$row1->id;
        }}
        
        $this->db->select('*');
        $this->db->from('profile_images');
        $this->db->where('reg_id', $user_id);
        $this->db->order_by("id", "asc"); 
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
        
        $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'images' => array($t),
                ], REST_Controller::HTTP_OK);    
        }else{
          //return 0;
          $this->response([
                    'status' => FALSE,
                    'message' => 'image not found',
                    //'images' => array($t),
                ], REST_Controller::HTTP_OK);
          
        }
         
  }
  
  //============================================================//
  
 public function profile_info_post()
  {
    $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('user_register');
        $this->db->where('profile', $profile_id);
         
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $personal = $query->result();
            
        }
        
         $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $contact = $query1->result();
           foreach ($query1->result() as $row13){
              
         $perm_city =	$row13->perm_city;
          if(is_numeric($perm_city)){
              
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $perm_city);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name =	$row113->name;
          }}
            $city = $city_name;
        }else{
            $city = $perm_city;
        }
        
          }  
        }
        
      $this->db->select('*');
        $this->db->from('personal_habits');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query2 = $this->db->get();
        if($query2->num_rows() > 0){
            
          $habbit = $query2->result();
        }
        
     $this->db->select('*');
        $this->db->from('education_work');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query4 = $this->db->get();
        if($query4->num_rows() > 0){
            
          $education = $query4->result();
          foreach ($query4->result() as $row13){
              
         $work_city =	$row13->work_city;
          if(!empty(is_numeric($work_city))){
              
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $work_city);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_work =	$row113->name;
          }}
            $city_work = $city_name_work;
        }else{
            $city_work = $work_city;
        }
        
          }
        }
        
        $this->db->select('*');
        $this->db->from('family_information');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query3 = $this->db->get();
        if($query3->num_rows() > 0){
            
          $family = $query3->result();
             foreach ($query3->result() as $row13){
         $father_native_place =	$row13->father_native_place;
         $mother_native_place =	$row13->mother_native_place; 
        
        if(is_numeric($father_native_place)){
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $father_native_place);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_nf =	$row113->name;
          }}
            $city_name_father = $city_name_nf;
        }else{
            $city_name_father = $father_native_place;
        }
        
         if(is_numeric($mother_native_place)){
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $mother_native_place);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_mn =	$row113->name;
          }}
            $city_name_mother = $city_name_mn;
        }else{
            $city_name_mother = $mother_native_place;
        }
        
          }
        }
        
        $this->db->select('*');
        $this->db->from('horoscope_details');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
            
          $horoscope = $query5->result();
        }
        
        $this->db->select('*');
        $this->db->from('partner_expection');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
            
          $partner_expection = $query5->result();
        }
        
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'personalinfo' => $personal,
                    'contactinfo' => $contact,
                    'city' => $city,
                    'habbitinfo' => $habbit,
                    'educationinfo' => $education,
                    'workcityname' => $city_work,
                    'familyinfo' => $family,
                    'fathernativeplace' => $city_name_father,
                    'motherrnativeplace' => $city_name_mother,
                    'horoscopeinfo' => $horoscope,
                    'partner_exception' => $partner_expection,
                ], REST_Controller::HTTP_OK);
  }
  //============================================================//
  public function mypersonalinfo_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('user_register');
        $this->db->where('profile', $profile_id);
         
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
          $personal = $query->result();
            foreach ($query->result() as $row13){
              
         $birth_city =	$row13->birth_city;
          if(is_numeric($birth_city)){
              
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $birth_city);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name =	$row113->name;
          }}
            $b_city = $city_name;
        }else{
            $b_city = $birth_city;
        }
        
          }
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'personalinfo' => $personal,
                    'birthcity' => $b_city,
                    
                ], REST_Controller::HTTP_OK);
  }
   //============================================================//
  public function mycontactinfo_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
       $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $contact = $query1->result();
           foreach ($query1->result() as $row13){
              
         $perm_city =	$row13->perm_city;
         $perm_state =	$row13->perm_state;
         
         if(is_numeric($perm_state)){
              
              $this->db->select('*');
        $this->db->from('states');
        $this->db->where('id', $perm_state);
        $query131 = $this->db->get();
        if($query131->num_rows() > 0){
          foreach ($query131->result() as $row1131){
               $state_name =	$row1131->name;
          }}
            $state = $state_name;
        }else{
            $state = $perm_state;
        }
        
          if(is_numeric($perm_city)){
              
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $perm_city);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name =	$row113->name;
          }}
            $city = $city_name;
        }else{
            $city = $perm_city;
        }
        
          } 
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'contactinfo' => $contact,
                    'city' => $city,
                    'state' => $state,
                    
                ], REST_Controller::HTTP_OK);
  }
  
    //============================================================//
  public function myhabitlifestyle_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('personal_habits');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query2 = $this->db->get();
        if($query2->num_rows() > 0){
            
          $habbit = $query2->result();
        }
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'habbitinfo' => $habbit,
                    
                ], REST_Controller::HTTP_OK);
  }
      //============================================================//
  public function myfamilyinfo_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('family_information');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query3 = $this->db->get();
        if($query3->num_rows() > 0){
          $family = $query3->result();
          
          foreach ($query3->result() as $row13){
         $father_native_place =	$row13->father_native_place;
         $mother_native_place =	$row13->mother_native_place; 
        
        if(is_numeric($father_native_place)){
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $father_native_place);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_work =	$row113->name;
          }}
            $city_name_father = $city_name_work;
        }else{
            $city_name_father = $work_city;
        }
        
         if(is_numeric($mother_native_place)){
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $mother_native_place);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_work =	$row113->name;
          }}
            $city_name_mother = $city_name_work;
        }else{
            $city_name_mother = $work_city;
        }
        
          }
        }
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'familyinfo' => $family,
                    'fathernativeplace' => $city_name_father,
                    'motherrnativeplace' => $city_name_mother,
                    
                ], REST_Controller::HTTP_OK);
  }
        //============================================================//
  public function myeduwrkinfo_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('education_work');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query4 = $this->db->get();
        if($query4->num_rows() > 0){
            
          $education = $query4->result();
          
          foreach ($query4->result() as $row13){
              
         $work_city =	$row13->work_city;
          if(is_numeric($work_city)){
              
              $this->db->select('*');
        $this->db->from('cities');
        $this->db->where('id', $work_city);
        $query13 = $this->db->get();
        if($query13->num_rows() > 0){
          foreach ($query13->result() as $row113){
               $city_name_work =	$row113->name;
          }}
            $city_work = $city_name_work;
        }else{
            $city_work = $work_city;
        }
        
          }
          
          
        }
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'educationinfo' => $education,
                    'workcityname' => $city_work,
                    
                ], REST_Controller::HTTP_OK);
  }
       //============================================================//
  public function myhoroscpe_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
       $this->db->select('*');
        $this->db->from('horoscope_details');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
            
          $horoscope = $query5->result();
        }
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'horoscopeinfo' => $horoscope,
                    
                ], REST_Controller::HTTP_OK);
  }
       //============================================================//
  public function mypartnerexpect_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
      $this->db->select('*');
        $this->db->from('partner_expection');
        $this->db->where('reg_profil_id', $profile_id);
         
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
            
          $partner_expection = $query5->result();
     
          
        }
        
        
        
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'partner_exception' => $partner_expection,
                    
                    
                ], REST_Controller::HTTP_OK);
  }
  //============================================================//
public function loguser_membership_post()
  {
    $profile_id  = strip_tags($this->post('profile'));
        
        $this->db->select('*');
        $this->db->from('memberships');
        $this->db->where('member_profile_id', $profile_id);
         
        $query = $this->db->get();
        if($query->num_rows() > 0){
        foreach ($query->result() as $row13){
         $payment_mode =	$row13->payment_mode;     
         $package_validity =	$row13->package_validity;
         $created_date =	$row13->created_date;
        }


$diff1 = abs(strtotime($package_validity) - strtotime($created_date));

$years = floor($diff1 / (365*60*60*24));
$months = floor(($diff1 - $years * 365*60*60*24) / (30*60*60*24));
if($payment_mode == 'Paid'){
		if($years != 0){
		    
		    if($years > 1){
		        
		        $diff = $years.' Years ';
		    }else{
		        
		         $diff = $years.' Year';
		    }
		   
		}else if($months != 0){
		    if($months == 5){
		         $diff = '6 Months';
		    }else { 
		         $diff = $months.' Months';
		    }
		    
		}else if($months != 0){
		     $diff = $days.' days';
		}
		
}else{
    $diff = 'Free';
}
         
// $diff1 = abs(strtotime($package_validity) - strtotime($created_date));

// $years = floor($diff1 / (365*60*60*24));
// $months = floor(($diff1 - $years * 365*60*60*24) / (30*60*60*24));
// $days = floor(($diff1 - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

// $mem = $years.' years, '.$months.' months'; 

          $membership = $query->result();
            
        }
        $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'membership' => $membership,
                   // 'interval' => $diff1,
                    'package_name' => $diff,
                   // 'check' => $mem,
                    
                    
                ], REST_Controller::HTTP_OK);
  }
  //============================================================//

   //================================================================//
  public function delete_image_post()
  {
    $img_id  = strip_tags($this->post('img_id'));
    
     $this->db->where('id', $img_id);
    $del = $this->db->delete('profile_images');
    if($del){
        $this->response([
                    'status' => TRUE,
                    'message' => 'Image deleted Successfully',
                    
                ], REST_Controller::HTTP_OK);
    }
  }
  //================================================================//

  
  //================================================================//
  
    function get_profiles_post() {
    $limit = 10; 
    $offset = $this->post('offset');
    if($offset == ''){
        $nw_offset = '0';
    }else{
      $nw_offset =   $offset;
    }
    $category = $this->post('category');
    $logged_user_profile  = strip_tags($this->post('logged_user_profile'));
    $city_name = $this->post('city_name');
    $high_education = $this->post('high_education');
     $occup_name = $this->post('occup_name');
    
    $this->db->where('profile',$logged_user_profile);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
    
        $this->db->select("*");
		$this->db->from('partner_expection');  
	    $this->db->where('reg_profil_id',$logged_user_profile ); 
		$this->db->limit(1);
		$query3 = $this->db->get();
        foreach ($query3->result() as $rowpf){
        $log_user_marital_status_pe =$rowpf->marital_status;
        $log_user_caste_pe1 =$rowpf->caste;
        $log_user_caste_pe = explode(", ",$log_user_caste_pe1);
        
        
        $log_user_age_from_pe =$rowpf->age_from;
        $log_user_age_to_pe =$rowpf->age_to;
        $log_user_height_from_pe =$rowpf->height_from;
        $log_user_height_to_pe =$rowpf->height_to;
        
        $log_user_highest_education_pe1 =$rowpf->highest_education;
        $log_user_highest_education_pe = explode(", ",$log_user_highest_education_pe1);
        
        $log_user_education_field_pe1 =$rowpf->education_field;
        $log_user_education_field_pe = explode(", ",$log_user_education_field_pe1);
        
        $log_user_primary_edu_pe1 =$rowpf->primary_edu;
        $log_user_primary_edu_pe = explode(", ",$log_user_primary_edu_pe1);
        
        $log_user_working_partner_pe =$rowpf->working_partner;
        
        $log_user_occup_pe1 =$rowpf->occup;
        $log_user_occup_pe = explode(", ",$log_user_occup_pe1);
        
        $log_user_liv_city_pe1 =$rowpf->liv_city;
        $log_user_liv_city_pe = explode(", ",$log_user_liv_city_pe1);
        
        $log_user_liv_state_pe1 =$rowpf->liv_state;
        $log_user_liv_state_pe = explode(", ",$log_user_liv_state_pe1);
        
        $log_user_liv_country_pe1 =$rowpf->liv_country;
        $log_user_liv_country_pe = explode(", ",$log_user_liv_country_pe1);
        
        $log_user_diet_pe =$rowpf->diet;
        $log_user_smooking_pe =$rowpf->smooking;
        $log_user_drinking_pe =$rowpf->drinking ;
        
        }
        
        
      if($category == 'newmatches_copy'){
      
        $return_arr = array();
        
  // $queindex = $this->db->query("create index newmatches ON interest(profile_id)");    
 //  print_r($this->db->last_query());
   
// $this->db->select('firstTable.*, secondTable.*');
// $this->db->from('firstTable');
// $this->db->join('secondTable', 'secondTable.id = firstTable.id');
// $this->db->where('firstTable.field_name','field_value');
// $query = $this->db->get();


    $queor = $this->db->query(" select id, first_name, caste, martial_status, height, weight,profile, dob from  user_register where caste LIKE '%$log_user_caste_pe1%' AND martial_status LIKE '%$log_user_marital_status_pe%' AND height >= '$log_user_height_from_pe' AND height <= '$log_user_height_to_pe'  AND status = '1' AND gender = '$show_gender' ORDER BY id desc limit $nw_offset,$limit");
    if($queor->num_rows() > 0){
   foreach ($queor->result() as $ropor){ 
        $id =  $ropor->id;
        $first_name =  $ropor->first_name;
        $caste =  $ropor->caste; $martial_status = $ropor->martial_status;
        $height =  $ropor->height; $weight =  $ropor->weight;
        $profile =  $ropor->profile; $dob =  $ropor->dob; 
   //---------------------------------------------------------------------// 
    $quedu = $this->db->query("select reg_profil_id, occup, education_field from  education_work where reg_profil_id = '$profile'   ");
    if($quedu->num_rows() > 0){
   foreach ($quedu->result() as $redu){
   $occup =  $redu->occup;
   $education_field =  $redu->education_field;
   //---------------------------------------------------------------------//
    $qucont = $this->db->query("select reg_profil_id, perm_city from  contact_info where reg_profil_id = '$profile'   ");
    if($qucont->num_rows() > 0){
   foreach ($qucont->result() as $cont){
   $perm_city =  $cont->perm_city;
   //-------------------------------------------------------------------------//
   $quimg = $this->db->query("select reg_id, file_name from  profile_images where reg_id = '$id'   ");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
  echo $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
    //-------------------------------------------------------------//
   
   //-------------------------------------------------------------------------//
    $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where member_profile_id = '$profile'   ");
    if($qumem->num_rows() > 0){
   foreach ($qumem->result() as $romem){
   $payment_mode =  $romem->payment_mode;
   }}
   
   
        $return_arr[] = array(
        "profile" => $profile, "id" => $id, 
        "first_name" => $first_name,
        "caste" => $caste, "martial_status" => $martial_status,
        "height" => $height,"weight" => $weight,
        "occup" => $occup,
        "education_field" => $education_field,
        "perm_city" => $perm_city,     
        "file_name" => $file_name,
        "sent" => $sent,
        "favourite" => $favourite,
        "payment_mode" => $payment_mode,            
            );
   } }  } }  }  }}  } 
   
               $this->response([
                    'status' => true,
                    'message' => 'New matches Fetch Successfully',
                    'profile' => $return_arr,
                    
                ], REST_Controller::HTTP_OK); 
    }
    
    if($category == 'premium'){
    //---------------------------------------------------------------------//

    $countmem = $this->db->query("select id from  memberships where payment_mode = 'Paid' AND gender = '$show_gender' ");
    $premium_tcount = $countmem->num_rows() ;

$this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite');
$this->db->from('memberships');
$this->db->join('user_register', 'user_register.profile = memberships.member_profile_id','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');

$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');


$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('memberships.payment_mode', 'Paid'); 
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.martial_status', $log_user_marital_status_pe);
$this->db->where_in('user_register.caste', $log_user_caste_pe);
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by('user_register.profile'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query = $this->db->get();
  $prime = $query->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'tcount' => "$premium_tcount",
                    'profile' => $prime,
                   
                ], REST_Controller::HTTP_OK);
       
    }
    
 if($category == 'newmatches'){
     
 $countnwmatc = $this->db->query("select id from  user_register where gender = '$show_gender' AND  caste = '$user_caste' AND  status = '1' limit 100");
    $newmatches_tcount = $countnwmatc->num_rows() ;  
    
   $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
   //,interest.sent
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');

$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');

$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.caste', $user_caste); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by('user_register.profile'); 
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query1 = $this->db->get();
  $newmatches = $query1->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'New matches Fetch Successfully',
                    'tcount' => "$newmatches_tcount",
                    'profile' => $newmatches,
                   
                ], REST_Controller::HTTP_OK);
       
    }
    
 
   
     if($category == 'match_of_day'){
    
     $countmatch = $this->db->query("select id from  user_register where gender = '$show_gender' AND  caste = '$user_caste' AND  status = '1' limit 20");
    $matchofday_tcount = $countmatch->num_rows() ; 
    
   $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,memberships.payment_mode,contact_info.perm_city,education_work.occup,education_work.education_field,profile_images.file_name,interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');

$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.caste', $user_caste); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('rand()'); 
$this->db->group_by('user_register.profile'); 
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query1 = $this->db->get();
  $matchofday = $query1->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'New matches Fetch Successfully',
                    'tcount' => "$matchofday_tcount",
                    'profile' => $matchofday,
                   
                ], REST_Controller::HTTP_OK);
       
    }
    
    if($category == 'looking_you'){
   
    $countloo = $this->db->query("select * from  user_register where gender = '$show_gender' AND caste = '$user_caste' and status = '1' group by profile");
    $loo_tcount = $countloo->num_rows() ;  
    
   $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,memberships.payment_mode,contact_info.perm_city,education_work.occup,education_work.education_field,profile_images.file_name,interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');

$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.caste', $user_caste); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by('user_register.profile'); 
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query1 = $this->db->get();
  $matchofday = $query1->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'New matches Fetch Successfully',
                    'profile' => $matchofday,
                    'tcount' => "$loo_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
    
 if($category == 'interest_received'){
     
    $countintrec = $this->db->query("select * from  interest where profile_id = '$user_id' AND sent = '1' AND reject = '0' AND accept = '0' group by logged_user_id");
    $intrec_tcount = $countintrec->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent_date,interest.id as intid');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.logged_user_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.profile_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 0);
$this->db->where('interest.accept', 0);
//$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
 $this->db->group_by('logged_user_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_received = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => ' Fetch Successfully',
                    'profile' => $interest_received,
                    'tcount' => "$intrec_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
      
 if($category == 'interest_accepted_receiv'){
     
     $countintrecac = $this->db->query("select * from  interest where profile_id = '$user_id' AND sent = '1' AND reject = '0' AND accept = '1' group by logged_user_id");
    $intrecac_tcount = $countintrecac->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.accept_date');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.logged_user_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.profile_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 0);
$this->db->where('interest.accept', 1);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('logged_user_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_received_accepted = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => ' Fetch Successfully',
                    'profile' => $interest_received_accepted,
                   'tcount' => "$intrecac_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
   if($category == 'interest_reject_receiv'){
    
    $countintrecre = $this->db->query("select * from  interest where profile_id = '$user_id' AND sent = '1' AND reject = '1' AND accept = '0' group by logged_user_id");
    $intrecre_tcount = $countintrecre->num_rows() ;
    
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.reject_date,interest.id as intid');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.logged_user_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.profile_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 1);
$this->db->where('interest.accept', 0);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('logged_user_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_received_rejected = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => ' Fetch Successfully',
                    'profile' => $interest_received_rejected,
                    'tcount' => "$intrecre_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
   if($category == 'interest_sent'){
   
   $countintsec = $this->db->query("select * from  interest where logged_user_id = '$user_id' AND sent = '1' AND reject = '0' AND accept = '0' group by profile_id");
    $intsent_tcount = $countintsec->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent_date');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.profile_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.logged_user_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 0);
$this->db->where('interest.accept', 0);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('profile_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_sent = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $interest_sent,
                   'tcount' => "$intsent_tcount",
                ], REST_Controller::HTTP_OK);
       
    }

        if($category == 'mutualmatches'){
    
    $countintsecacc = $this->db->query("select * from  interest where logged_user_id = '$user_id' or profile_id = '$user_id' AND sent = '1' AND reject = '1' AND accept = '1' group by profile_id");
    $intsentacc_tcount = $countintsecacc->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.accept_date');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.profile_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.logged_user_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 0);
$this->db->where('interest.accept', 1);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('profile_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_accept_sent = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $interest_accept_sent,
                    'tcount' => "$intsentacc_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
       if($category == 'interest_accept_sent'){
    
    $countintsecacc = $this->db->query("select * from  interest where logged_user_id = '$user_id' AND sent = '1' AND reject = '0' AND accept = '1' group by profile_id");
    $intsentacc_tcount = $countintsecacc->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.accept_date');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.profile_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.logged_user_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 0);
$this->db->where('interest.accept', 1);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('profile_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_accept_sent = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $interest_accept_sent,
                    'tcount' => "$intsentacc_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
           if($category == 'interest_reject_sent'){
    
    $countintsecrej = $this->db->query("select * from  interest where logged_user_id = '$user_id' AND sent = '1' AND reject = '1' AND accept = '0' group by profile_id");
    $intsentrej_tcount = $countintsecrej->num_rows() ;
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.reject_date');
$this->db->from('interest');
$this->db->join('user_register', 'user_register.id = interest.profile_id','left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('interest.logged_user_id', $user_id); 
$this->db->where('interest.sent', 1);
$this->db->where('interest.reject', 1);
$this->db->where('interest.accept', 0);
$this->db->where('user_register.status', 1); 
 $this->db->group_by('profile_id'); 
$this->db->order_by('interest.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_reject_sent = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $interest_reject_sent,
                    'tcount' => "$intsentrej_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
        
    
    if($category == 'favourite'){
       
   $countfav = $this->db->query("select * from  favourites where user_logged_id = '$user_id' group by profile_id");
    $fav_tcount = $countfav->num_rows() ;     
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite');
$this->db->from('favourites');
$this->db->join('user_register', 'user_register.id = favourites.profile_id','left');

$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
 $this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->where('favourites.user_logged_id', $user_id); 

//$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
 $this->db->group_by('favourites.profile_id'); 
$this->db->order_by('favourites.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_received = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'interest_received Fetch Successfully',
                    'profile' => $interest_received,
                   'tcount' => "$fav_tcount",
                ], REST_Controller::HTTP_OK);
       
    }
    
    if($category == 'recently_visitor'){
    
    $countvisi = $this->db->query("select * from  recently_viewed_profiles where viewed_profile_id = '$logged_user_profile' group by logged_profile_id");
    $visi_tcount = $countvisi->num_rows() ;  
    
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('recently_viewed_profiles');
$this->db->join('user_register', 'user_register.profile =  recently_viewed_profiles.logged_profile_id AND user_register.status = 1 ','left');

$this->db->join('memberships', 'memberships.member_profile_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('education_work', 'education_work.reg_profil_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->where('recently_viewed_profiles.viewed_profile_id',$logged_user_profile ); 
//$this->db->where('viewed_profile_id','user_register.profile' );
$this->db->where('user_register.gender', $show_gender); 
//$this->db->where('user_register.status', 1); 
$this->db->order_by('recently_viewed_profiles.id','desc'); 
$this->db->group_by("recently_viewed_profiles.logged_profile_id");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();
  $recent_view = $query11->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $recent_view,
                     'tcount' => "$visi_tcount",
                ], REST_Controller::HTTP_OK);
    } 
        
       if($category == 'who_view_contact'){
           
     $countvc = $this->db->query("select * from  recently_viewed_profiles where viewed_profile_id = '$logged_user_profile' AND contact_viewed = 'Yes' group by logged_profile_id");
    $vc_tcount = $countvc->num_rows() ;  
    
 $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode, interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('recently_viewed_profiles', 'recently_viewed_profiles.logged_profile_id = user_register.profile','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');

$this->db->where('viewed_profile_id',$logged_user_profile ); 
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
	$this->db->where('contact_viewed','Yes' );
$this->db->order_by('recently_viewed_profiles.id','desc'); 
$this->db->group_by("logged_profile_id");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();
  $recent_view = $query11->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Recently viewed Fetch Successfully',
                    'profile' => $recent_view,
                   'tcount' => "$vc_tcount",
                ], REST_Controller::HTTP_OK);
    } 
    
    if($category == 'recently_viewed'){
        
         $countrecen = $this->db->query("select * from  recently_viewed_profiles where logged_profile_id = '$logged_user_profile' group by viewed_profile_id");
    $recen_tcount = $countrecen->num_rows() ;
    
 $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('recently_viewed_profiles');
$this->db->join('user_register', 'user_register.profile = recently_viewed_profiles.viewed_profile_id','left');

$this->db->join('memberships', 'memberships.member_profile_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('education_work', 'education_work.reg_profil_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->where('logged_profile_id',$logged_user_profile ); 
//$this->db->where('viewed_profile_id','user_register.profile' );
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('recently_viewed_profiles.id','desc'); 
$this->db->group_by("recently_viewed_profiles.viewed_profile_id");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();
  $recent_view = $query11->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $recent_view,
                   'tcount' =>$recen_tcount
                ], REST_Controller::HTTP_OK);
    }
    
    if($category == 'viewed_contacts'){
        
     $countviews = $this->db->query("select * from  recently_viewed_profiles where logged_profile_id = '$logged_user_profile' AND contact_viewed = 'Yes' group by viewed_profile_id");
    $views_tcount = $countviews->num_rows() ;
    
 $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('recently_viewed_profiles', 'recently_viewed_profiles.viewed_profile_id = user_register.profile','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');

$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('logged_profile_id',$logged_user_profile ); 
$this->db->where('contact_viewed','Yes' );
//$this->db->where('viewed_profile_id','user_register.profile' );
$this->db->where('user_register.gender', $show_gender); 

$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by("recently_viewed_profiles.viewed_profile_id");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();
  $recent_view = $query11->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $recent_view,
                    'tcount' =>$views_tcount
                ], REST_Controller::HTTP_OK);
    }
    
    
    
 
  if($category == 'nevermarried' || $category == 'awaitingdivorced' || $category == 'divorced' || $category == 'widowed'){
    
   $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->where('user_register.gender', $show_gender); 

if($category == 'nevermarried'){
$this->db->where('user_register.martial_status', 'Never Married'); 
}

if($category == 'awaitingdivorced'){
$this->db->where('user_register.martial_status', 'Awaiting Divorced'); 
}

if($category == 'divorced'){
$this->db->where('user_register.martial_status', 'Divorced'); 
}
if($category == 'widowed'){
$this->db->where('user_register.martial_status', 'Widowed'); 
}
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by("user_register.profile");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query1 = $this->db->get();
  $maritalstatus = $query1->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $maritalstatus,
                   
                ], REST_Controller::HTTP_OK);
       
    }
    
    if($category == 'cities' || $category == 'highest_education' || $category == 'occupation'){
    
   $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');

if($category == 'cities'){
$this->db->like('contact_info.perm_city', $city_name); 
}
if($category == 'highest_education'){
$this->db->like('education_work.highest_education', $high_education); 
}
if($category == 'occupation'){
$this->db->like('education_work.occup', $occup_name); 
}
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query13 = $this->db->get();
  $cities1 = $query13->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => ' Fetch Successfully',
                    'profile' => $cities1,
                   
                ], REST_Controller::HTTP_OK);
       
    }
    
  if($category == 'favourite_me'){
      
         
  $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.user_logged_id as favourite');
$this->db->from('favourites');
$this->db->join('user_register', 'user_register.id = favourites.user_logged_id','left');

$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
 $this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
 $this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
 $this->db->join('interest', 'interest.logged_user_id = user_register.id AND interest.profile_id = '.$user_id, 'left');
$this->db->where('favourites.profile_id', $user_id); 

$this->db->where('user_register.status', 1); 
 $this->db->group_by('favourites.user_logged_id'); 
$this->db->order_by('favourites.id','desc'); 
if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
$query2 = $this->db->get();
  $interest_received = $query2->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Favrouit me Fetch Successfully',
                    'profile' => $interest_received,
                   
                ], REST_Controller::HTTP_OK);
  }
    
}
   //================================================================//
   function view_contact_post() {
   
    $log_user_profile  = strip_tags($this->post('logged_user_profile'));
    $profile  = strip_tags($this->post('view_user_profile'));
    
       $this->db->select('*');
        $this->db->from('memberships');
        $this->db->where('member_profile_id', $log_user_profile);
         
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
         $membership = $query->result();
         // $r = $membership['payment_mode'];
         foreach ($membership as $row1){
        $log_user_membership_id =  $row1->id;
       	$log_user_mem_remaining_profiles =  $row1->remaining_profiles;
		$log_user_mem_total_profiles_alloted =  $row1->total_profiles_alloted;
		$log_user_mem_payment_mode =  $row1->payment_mode;
		$log_user_mem_package_validity =  $row1->package_validity;
		$log_user_mem_created_date =  $row1->created_date;
        }
            
        }
        
                
           $this->db->select("*");
                      $this->db->from('recently_viewed_profiles');  
                      $this->db->where('logged_profile_id',$log_user_profile );
                      $this->db->where('viewed_profile_id',$profile );
                      $this->db->group_by('viewed_profile_id');
                      $queryr = $this->db->get();    
                   if ($queryr->num_rows() > 0){
                      foreach($queryr->result() as $rowi)
                        {
                        $recm_id =  $rowi->id;  
                        }
                       $this->db->set('contact_viewed', 'Yes');
                        $this->db->where('id', $recm_id);
                      $this->db->update('recently_viewed_profiles');
                      
                      
                     /*--------------------------------------------*/
                                  $queryccc = $this->db->query("SELECT *  FROM recently_viewed_profiles where logged_profile_id = '$log_user_profile' AND contact_viewed = 'Yes' AND created_date >= '$log_user_mem_created_date' AND created_date <= '$log_user_mem_package_validity' ");
      $count_profiles =  $queryccc->num_rows();
      if($count_profiles != $log_user_mem_remaining_profiles){
                     $remprofl =  $log_user_mem_remaining_profiles + 1;
                     $this->db->set('remaining_profiles', $remprofl);
                        $this->db->where('id', $log_user_membership_id);
                      $this->db->update('memberships');
      }
    
        $this->db->select('*');
        $this->db->from('memberships');
        $this->db->where('member_profile_id', $log_user_profile);
         
        $query = $this->db->get();
        if($query->num_rows() > 0){
            
         $membership = $query->result();
         // $r = $membership['payment_mode'];
         foreach ($membership as $rowmp){
       	$remaining_profiles =  $rowmp->remaining_profiles;
		$total_profiles_alloted =  $rowmp->total_profiles_alloted;
		
        }
        }
                    }
                    
                    
          $message_body = "Profile id: ".$log_user_profile." has viewed your contact number";
     $message_title = "Your contact number has viewed";
  
  $this->db->where('profile', $profile);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        
       
if(!empty($token)){   
$notification = array();
$arrNotification= array();			
$arrData = array();		
$arrNotification["profile_id"] = $profile;
//$arrNotification["row_id"] = $logged_user_id;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["msg_type"] = 'contact_view';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
       
       
        $this->db->select("*");
        $this->db->from('notifications');  
        $this->db->where('logged_user',$profile);
        $this->db->where('second_user',$log_user_profile   );
         $this->db->where('action','contact_view' );
        $queryrw = $this->db->get();    
        if ($queryrw->num_rows() > 0){}else{ 
         $data_notify = array(
            'logged_user' => $profile,
            'second_user' => $log_user_profile ,
            'date_created' => date('Y-m-d'),
            'title' => $message_title,
            'msg' => $message_body,
             'read_unread' => 0,
            'action' => 'contact_view'
             );
 $notifications =   $this->db->insert('notifications', $data_notify); 
}
        }
}
        
    }             
                    
                     $this->response([
                    'status' => true,
                    'viewed_contacts' => $remaining_profiles,
                    'total_profiles_alloted' => $total_profiles_alloted,
                    
                ], REST_Controller::HTTP_OK); 
    
   }
  //================================================================//
  
    public function report_profile_post() {
    $logged_user_profileid = strip_tags($this->post('logged_user_profile'));  
    $profile_id = strip_tags($this->post('profile_id'));  
    $reason = strip_tags($this->post('reason'));
    $comment = strip_tags($this->post('comment'));
    
   if(!empty($profile_id) )
   {
       
            $data = array(
           
            'by_user' => $logged_user_profileid,
            'to_user' => $profile_id,
            'reason' => $reason,
            'comment' => $comment,
            
             );
            $partner_program = $this->db->insert('report_fake_profile', $data);
            if($partner_program){
                
                 $this->response([
                    'status' => true,
                    'message' => 'Profile Report Sent',
                    
                ], REST_Controller::HTTP_OK); 
            }else{
                $this->response([
                    'status' => true,
                    'message' => 'Something went wrong. please try again later',
                    
                ], REST_Controller::HTTP_OK); 
            }
        
   }else{
        $this->response([
                    'status' => false,
                    'message' => 'Provide Complete Information',
                    
                ], REST_Controller::HTTP_OK);
   }
  }
  
  //================================================================//
public function update_viewed_profile_post() {
          
     $log_user_profile = strip_tags($this->post('logged_user_profile'));
     $profile = strip_tags($this->post('another_profile_id'));
     $log_user_regid = strip_tags($this->post('logged_user_tid'));
     $regid = strip_tags($this->post('anotherprofile_tid'));
     $todaydate = date('Y-m-d H:i:s');
     
     if(!empty($log_user_profile) || !empty($profile) ){
          	
		
		$this->db->select("*");
        $this->db->from('recently_viewed_profiles');  
        $this->db->where('logged_profile_id',$log_user_profile );
        $this->db->where('viewed_profile_id',$profile );
        $queryrw = $this->db->get();    
        if ($queryrw->num_rows() > 0){
            foreach($queryrw->result() as $rowi)
                        {
                        $rid =  $rowi->id;  
                        }
            
            $this->db->set('last_view_profile', date('Y-m-d'));
                        $this->db->where('id', $rid);
                      $this->db->update('recently_viewed_profiles');
        }else{
            
        $insert_contact = array(  
                'logged_profile_id'  => $log_user_profile,
                'viewed_profile_id'  => $profile, 
                'created_date'  => date('Y-m-d H:i:s'), 
                ); 
    $this->db->insert('recently_viewed_profiles', $insert_contact); 
        
      $message_body = "Profile id: ".$log_user_profile." has viewed your profile";
     $message_title = "Your profile has viewed";
  
  $this->db->where('profile', $profile);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
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
$arrNotification["msg_type"] = 'profile_view';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
       
       
        $this->db->select("*");
        $this->db->from('notifications');  
        $this->db->where('logged_user',$profile);
        $this->db->where('second_user',$log_user_profile );
         $this->db->where('action','profile_view' );
        $queryrw = $this->db->get();    
        if ($queryrw->num_rows() > 0){}else{ 
         $data_notify = array(
            'logged_user' => $profile,
            'second_user' => $log_user_profile ,
            'date_created' => date('Y-m-d'),
            'title' => $message_title,
            'msg' => $message_body,
             'read_unread' => 0,
            'action' => 'profile_view'
             );
 $notifications =   $this->db->insert('notifications', $data_notify); 
}
        }
}
        
    }
        
        $this->response([
                    'status' => true,
                    'message' => 'Done successfully',
                    
                ], REST_Controller::HTTP_OK);
     }
     
     }
}
   //================================================================//

  //================================================================//
public  function accept_interest_post(){
       $accept_date = date('Y-m-d H:i:s');
         $int_id =   $this->input->post('intid');
         
          $this->db->where('id',$int_id);
    $query2 = $this->db->get('interest');
      foreach ($query2->result() as $row){
     $logedduser     =	$row->logged_user_id;   // 3831
      $seconduser  =	$row->profile_id;       // 97
        } 
      
         $this->db->where('id',$seconduser);
    $query2e = $this->db->get('user_register');
      foreach ($query2e->result() as $rowe){
          $notify_seconduser =	$rowe->profile;
        }  
        
           $this->db->where('id',$logedduser);
    $query2es = $this->db->get('user_register');
      foreach ($query2es->result() as $rowes){
          $notify_logeduser =	$rowes->profile;
        }  
        
        //$profile_id = $this->uri->segment(5);
        $this->db->set('accept', 1);
        $this->db->set('reject', 0);
        $this->db->set('accept_date', $accept_date);
        $this->db->where('id', $int_id);
      $accept = $this->db->update('interest');  
      if($accept){
          
         $message_body = "Profile id: ".$notify_seconduser." has accepted your interest";
     $message_title = "Your Interest has accepted";
  
  $this->db->where('profile', $notify_logeduser);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        
       
if(!empty($token)){   
$notification = array();
$arrNotification= array();			
$arrData = array();		
$arrNotification["profile_id"] = $notify_seconduser;
$arrNotification["row_id"] = $logedduser;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["msg_type"] = 'interest_accept';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
     
        
     $data_notify = array(
            'logged_user' => $notify_logeduser,
            'second_user' => $notify_seconduser,
            'date_created' => date('Y-m-d'),
            'title' => $message_title,
            'msg' => $message_body,
             'read_unread' => 0,
            'action' => 'interest_accept'
             );
 $notifications =   $this->db->insert('notifications', $data_notify);       
 
}
    
}
}
         $this->response([
                    'status' => true,
                    'message' => 'Interest Accepted successfully',
                    
                ], REST_Controller::HTTP_OK);
       
      }
      //  redirect('Welcome/receivedinterest/', 'refresh');
            
    }
    //================================================================//
public  function reject_interest_post(){
    
    $reject_date = date('Y-m-d H:i:s');
         $int_id =   $this->input->post('intid');
         
           $this->db->where('id',$int_id);
    $query2 = $this->db->get('interest');
      foreach ($query2->result() as $row){
     $logedduser     =	$row->logged_user_id;   // 3831
      $seconduser  =	$row->profile_id;       // 97
        } 
      
         $this->db->where('id',$seconduser);
    $query2e = $this->db->get('user_register');
      foreach ($query2e->result() as $rowe){
          $notify_seconduser =	$rowe->profile;
        }  
        
           $this->db->where('id',$logedduser);
    $query2es = $this->db->get('user_register');
      foreach ($query2es->result() as $rowes){
          $notify_logeduser =	$rowes->profile;
        } 
        
        //$profile_id = $this->uri->segment(5);
       // $this->db->set('accept', 1);
        $this->db->set('reject', 1);
        $this->db->set('accept', 0);
        $this->db->set('reject_date', $reject_date);
        $this->db->where('id', $int_id);
      $reject = $this->db->update('interest');  
      if($reject){
       
        $message_body = "Profile id: ".$notify_seconduser." has rejected your interest";
     $message_title = "Your Interest has rejected";
  
  $this->db->where('profile', $notify_logeduser);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        
       
if(!empty($token)){   
$notification = array();
$arrNotification= array();			
$arrData = array();		
$arrNotification["profile_id"] = $notify_seconduser;
$arrNotification["row_id"] = $logedduser;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["msg_type"] = 'interest_reject';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
     
        
     $data_notify = array(
            'logged_user' => $notify_logeduser,
            'second_user' => $notify_seconduser,
            'date_created' => date('Y-m-d'),
            'title' => $message_title,
            'msg' => $message_body,
             'read_unread' => 0,
            'action' => 'interest_reject'
             );
 $notifications =   $this->db->insert('notifications', $data_notify);       
 
}
    
}
}  
          
          $this->response([
                    'status' => true,
                    'message' => 'Interest Rejected',
                    
                ], REST_Controller::HTTP_OK);
          
      }
                
                
      //  redirect('Welcome/receivedinterest/', 'refresh');
       
      }
      //  redirect('Welcome/receivedinterest/', 'refresh');
            
 //=================================================================//
 public  function search_by_id_post(){
     
      $profile =   $this->input->post('profile');
      $logged_user_profile =   $this->input->post('logged_user_profile');
      
      $this->db->like('profile',$logged_user_profile);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
    
$this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite');
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
//$this->db->where('logged_user_id',$user_id);
$this->db->where('user_register.gender',$show_gender ); 
$this->db->like('user_register.profile', $profile); 
$this->db->where('user_register.status', 1); 
$this->db->group_by('user_register.profile'); 

$query1 = $this->db->get();
  $searchbyid = $query1->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $searchbyid,
                    
                ], REST_Controller::HTTP_OK);
 }

  
 
//=================================================================// 
  public  function save_search_post(){
      
       $return_arr = array();
      $limit = 10;
      $offset =   $this->input->post('offset');
     
      $profile1 =   $this->input->post('profile');
      $searchid =    $this->getsearchid();
      
      $marital_status =   $this->input->post('marital_status');
      $age_from =   $this->input->post('age_from');
      $age_to =   $this->input->post('age_to');
      
      $height_from =   $this->input->post('height_from');
      $height_to =   $this->input->post('height_to');
       
      $state =   $this->input->post('state');
      $city =   $this->input->post('city');
      $caste =   $this->input->post('caste');
      $education_field =   $this->input->post('education_field');
      $highest_education =   $this->input->post('highest_education');
      $occup =   $this->input->post('occup');
     
      $diet =   $this->input->post('diet');
      
      $smooking =   $this->input->post('smooking');
      
      $drinking =   $this->input->post('drinking');
 
     $data_member = array(
            'profile_id' => $profile1,
            'search_id' => $searchid,
            'marital_status' => $marital_status,
            'age_from' => $age_from,
            'age_to' => $age_to,
            'height_from' => $height_from,
            'height_to' => $height_to,
            'state' => $state,
            'city' => $city,
            'caste' => $caste,
            'education_field' => $education_field,
            'highest_education' => $highest_education,
            'occup' => $occup,
            'diet' => $diet,
            'smooking' => $smooking,
            'drinking' => $drinking,
            
             );
            $member = $this->db->insert('quick_search', $data_member);
  
            if($member){
   	  
	$this->db->where('profile',$profile1);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
    
        $this->db->select("*");
		$this->db->from('quick_search');  
		$this->db->where('search_id',$searchid ); 
		$querypci = $this->db->get();
		foreach($querypci->result() as $rowcf)
		{
		    
			$search_name =$rowcf->search_name;
			$marital =$rowcf->marital_status;
			$maritalstatus = explode(", ",$marital);
			if($maritalstatus == '' || $marital == 'Any'){
			    $marital_status = '';
			}else{
			    
			$maritalstatus1 = '"' . implode('", "', $maritalstatus) . '"';
			if($maritalstatus1 == '""'){
			    $marital_status = '';
			}else{
			   $marital_status = 'AND martial_status IN ('.$maritalstatus1.')'; 
			}
			}
			
			$age_from =$rowcf->age_from;
			$age_to =$rowcf->age_to;
			if($age_from != '' && $age_to != ''){
			    $age = "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_from'
AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
			}
			
			$height_from =$rowcf->height_from;
			$height_to =$rowcf->height_to;
	        if($height_from != '' && $height_to != ''){
			    $height = "AND height >= '$height_from'
AND height <= '$height_to'";
			}
			
			
			$caste_d = $rowcf->caste;
			$caste_s = explode(", ",$caste_d);
			 $incaste1 = '"' . implode('", "', $caste_s) . '"';
			if($caste_d == 'Any' || $caste_s == '' ){
			   $incaste = ''; 
			}else{
			  if($incaste1 == '""'){
			      $incaste = '';
			  }else{
			      $incaste = 'AND caste IN ('.$incaste1.')';  
			  }
			}
			
		    $city =$rowcf->city;
		    $city_s = explode(", ",$city);
			 $city1 = '"' . implode('", "', $city_s) . '"';
			if($city == 'Any' || $city_s == '' ){
			   $incity = ''; 
			}else{
			  if($city1 == '""'){
			      $incity = '';
			  }else{
			      $incity = 'AND perm_city IN ('.$city1.')';  
			  }
			}
			
			 $state =$rowcf->state;
		    $state_s = explode(", ",$state);
			 $state1 = '"' . implode('", "', $state_s) . '"';
			if($state == 'Any' || $state_s == '' ){
			   $instate = ''; 
			}else{
			  if($state1 == '""'){
			      $instate = '';
			  }else{
			      $instate = 'AND perm_state IN ('.$state1.')';  
			  }
			}

			
			$education_field =$rowcf->education_field;
			$education_field_s = explode(", ",$education_field);
			 $education_field1 = '"' . implode('", "', $education_field_s) . '"';
			if($education_field == 'Any' || $education_field_s == '' ){
			   $ineducation_field = ''; 
			}else{
			  if($education_field1 == '""'){
			      $ineducation_field = '';
			  }else{
			      $ineducation_field = 'AND education_field IN ('.$education_field1.')';  
			  }
			}
			
			$highest_education =$rowcf->highest_education;
			$highest_s = explode(", ",$highest_education);
			 $highest1 = '"' . implode('", "', $highest_s) . '"';
			if($highest_education == 'Any' || $highest_s == '' ){
			   $inhighest = ''; 
			}else{
			  if($highest1 == '""'){
			      $inhighest = '';
			  }else{
			      $inhighest = 'AND highest_education IN ('.$highest1.')';  
			  }
			}
			
			$occup =$rowcf->occup;
			$occup_s = explode(", ",$occup);
			 $occup1 = '"' . implode('", "', $occup_s) . '"';
			if($occup == 'Any' || $occup_s == '' ){
			   $inoccup = ''; 
			}else{
			  if($occup1 == '""'){
			      $inoccup = '';
			  }else{
			      $inoccup = 'AND occup IN ('.$occup1.')';  
			  }
			}
			
			$diet =$rowcf->diet;
			$diet_s = explode(", ",$diet);
			 $diet1 = '"' . implode('", "', $diet_s) . '"';
			if($diet == 'Any' || $diet_s == '' ){
			   $indiet = ''; 
			}else{
			  if($diet1 == '""'){
			      $indiet = '';
			  }else{
			      $indiet = 'AND diet IN ('.$diet1.')';  
			  }
			}
				
// 		
// 			$smooking =$rowcf->smooking;
// 			$drinking =$rowcf->drinking;
		}
		
	 $incaste;
// 	/*---------------------------------------------------------------*/
	$today = date('Y-m-d');
	$querymp = $this->db->query("SELECT *  FROM user_register where status = '1' 
	AND gender = '$show_gender'  $marital_status $incaste $age $height
	ORDER BY id DESC LIMIT $offset, $limit");
    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
         
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
  echo $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
    //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
    $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where member_profile_id = '$profile'   ");
    if($qumem->num_rows() > 0){
   foreach ($qumem->result() as $romem){
   $payment_mode =  $romem->payment_mode;
   }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}}
    /*---------------------------------------------------------------*/	
	
    $this->response([
                    'status' => true,
                    'message' => 'data fetched',
                    'searchid' => $searchid,
                    'profile' => $return_arr,
                    
                ], REST_Controller::HTTP_OK); 
		
            }
         
 }
 
 //=================================================================//
  public  function quick_search_post(){
      
    
    $searchid =   $this->input->post('searchid');
    
    $logged_user_profile =   $this->input->post('logged_user_profile');
      
    $this->db->where('profile',$logged_user_profile);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
   $this->db->select("*");
		$this->db->from('quick_search');  
		$this->db->where('search_id',$searchid ); 
		$querypci = $this->db->get();
		foreach($querypci->result() as $rowcf)
		{
			$search_name =$rowcf->search_name;
			$marital_status =$rowcf->marital_status;
			
			$age_from =$rowcf->age_from;
			$age_to =$rowcf->age_to;
			if($age_from != '' && $age_to != ''){
			    $age = "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_from'
AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
			}
			
			$height_from =$rowcf->height_from;
			$height_to =$rowcf->height_to;
	        if($height_from != '' && $height_to != ''){
			    $height = "AND height >= '$height_from'
AND height <= '$height_to'";
			}
			
			$city1 =$rowcf->city;
		if($city1 != ''){
		    $city = "AND perm_city = '$city1'";
		}
			 $caste = $rowcf->caste;
			 
			$education_field =$rowcf->education_field;
			
			$highest_education =$rowcf->highest_education;
			
			$occup =$rowcf->occup;
			
			
			$diet =$rowcf->diet;
	    	if($diet != ''){
			    $diet2 = 'AND personal_habits.diet = "'.$diet.'"';
			}else{
			    $diet2 = ""; 
			}
			$smooking =$rowcf->smooking;
			$drinking =$rowcf->drinking;
		}
		
		 
     $this->db->select('user_register.id,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite');
$this->db->from('user_register');
$this->db->join('memberships', 'memberships.member_profile_id = user_register.profile','left');
$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile', 'left');

 $this->db->join('personal_habits', 'personal_habits.reg_profil_id = user_register.profile AND  personal_habits.smooking = "'.$smooking.'" AND  personal_habits.drinking = "'.$drinking.'"', 'left');

$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');

$this->db->where('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >=', $age_from);
$this->db->where('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <=', $age_to);

if($occup != ''){
$this->db->where('education_work.occup', $occup); 
}
if($education_field != ''){
$this->db->where('education_work.education_field', $education_field); 
}
if($highest_education != ''){
$this->db->where('education_work.highest_education', $highest_education); 
}
//$this->db->where('logged_user_id',$user_id);
$this->db->where('user_register.martial_status',$marital_status ); 
if($caste != ''){
$this->db->where('user_register.caste',$caste ); 
}

$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
$this->db->group_by('user_register.profile'); 

$query1 = $this->db->get();
  $search = $query1->result_array();

  if($search){
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $search,
                    
                ], REST_Controller::HTTP_OK);
  }else{
      $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                    
                    
                ], REST_Controller::HTTP_OK);
  }	
      
  }
  //=============================================================// 
  function update_search_post(){
    $search_id  =   $this->input->post('searchid');
    $name_search = $this->input->post('name_search');
   
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
                    'message' => 'Search Saved Successfully',
                ], REST_Controller::HTTP_OK);
            
        }
}
//=============================================================// 
  public function view_saved_search_post()
  {
      $profile_id  = strip_tags($this->post('profile'));
        
       $this->db->select('*');
        $this->db->from('quick_search');
        $this->db->where('profile_id', $profile_id);
        $this->db->where("search_name != ''");
         $this->db->order_by("id", "desc");
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $savedsearch = $query1->result();
            
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'saved_search' => $savedsearch,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                  
                    
                ], REST_Controller::HTTP_OK);
       
            }
        
  }
  
 /*****************************************************************/
function save_fav_post(){
    
     $logged_user_id = $this->input->post('logged_user_id');
     $another_user_id = $this->input->post('another_user_id');
    // $remove_add = $this->input->post('remove_add');
     
      $this->db->where('id',$another_user_id);
    $query2 = $this->db->get('user_register');
      foreach ($query2->result() as $row){
     $anotherprofile   =	$row->profile;
        }
        
        $this->db->where('id',$logged_user_id);
    $query22 = $this->db->get('user_register');
      foreach ($query22->result() as $row1){
     $logprofile   =	$row1->profile;
        }
     
      $data_cont = array(
            'profile_id' => $another_user_id,
            'user_logged_id' => $logged_user_id,
           
             );
       $this->db->select('*');
        $this->db->from('favourites');
        $this->db->where('profile_id', $another_user_id);
        $this->db->where('user_logged_id', $logged_user_id);
     
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            foreach ($query1->result() as $row4){
     $fav_id   =	$row4->id;
        }
                
          $delete = $this->db->delete('favourites',array('id'=>$fav_id));      
         $this->response([
                    'status' => TRUE,
                    'message' => 'Profile remove from favourite',
                 
                ], REST_Controller::HTTP_OK);
        }else{
            
            $this->db->insert('favourites', $data_cont); 
            
     $message_body = "Profile id: ".$logprofile." has  shortlisted you";
     $message_title = "You have been shortlisted";
  
  $this->db->where('profile', $anotherprofile);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
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
$arrNotification["msg_type"] = 'add_fav';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
 $data = array(
           
            'logged_user' => $logprofile,
            'second_user' => $anotherprofile,
            'title' => $message_title,
            'msg' => $message_body,
            'action' => 'add_fav',
             );
            $partner_program = $this->db->insert('notifications', $data);
}
}
 }

    
         $this->response([
                    'status' => true,
                    'message' => 'Profile added to favourite',
                //    'check' => $check,
                    //'token' => $token,
                ], REST_Controller::HTTP_OK);
    
            
            //  $this->response([
            //         'status' => TRUE,
            //         'message' => 'Profile added to favourite',
                 
            //     ], REST_Controller::HTTP_OK);
        }
      
     
}
//===================================================================//
 /************************************************************/
    function notify_users_post(){
             
    $token =   trim($this->input->post('token'));
    $notify_msg = trim($this->input->post('notify_msg'));
   // $notify_msg = trim($this->input->post('notify_msg'));

        
    $message_body = $notify_msg;
   $message_title = "SUNDAR JODI";
   $message_photo = "https://sundarjodi.com/offers/diwali_sec.png";
   
  
    $query23 = $this->db->get('user_register');
   // foreach ($query23->result() as $row3){
     //   $tid =	$row3->id;
        
       
if(!empty($token)){  
   // echo $tid.' ';
$notification = array();
$arrNotification= array();			
$arrData = array();		
//$arrNotification["profile_id"] = $logprofile;
//$arrNotification["row_id"] = $logged_user_id;
//$arrNotification["profile_id"] = $user_id;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["photo"] = $message_photo;
$arrNotification["msg_type"] = 'offer';
 $arrNotification["sound"] = "default";
// $arrNotification["type"] = 1;


$check = $this->user->fcm($token, $arrNotification, "Android"); 
// if($check){
//  $data = array(
           
//             'logged_user' => $logprofile,
//             'second_user' => $anotherprofile,
//             'title' => $message_title,
//             'msg' => $message_body,
//             'action' => 'add_fav',
//              );
//             $partner_program = $this->db->insert('notifications', $data);
// }
}
// }

    if($check){
         $this->response([
                    'status' => true,
                    'message' => 'Notification send',
                    'check' => $check,
                    //'token' => $token,
                ], REST_Controller::HTTP_OK);
    }    
      
    }
//=============================================================//
function send_interest_post(){
     $logged_user_id = $this->input->post('logged_user_id');
     $another_user_id = $this->input->post('another_user_id');
     
     $this->db->where('id',$another_user_id);
    $query2 = $this->db->get('user_register');
      foreach ($query2->result() as $row){
     $anotherprofile   =	$row->profile;
        }
        
        $this->db->where('id',$logged_user_id);
    $query22 = $this->db->get('user_register');
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
            $this->response([
                    'status' => TRUE,
                    'message' => 'Profile remove from interest',
                 
                ], REST_Controller::HTTP_OK);
                
                
         
        }else{
        $intr =     $this->db->insert('interest', $data_cont); 
          
 $message_body = "New Interest Received from Profile id: ".$logprofile;
   $message_title = "Interest Received";
  // $message_photo = "https://sundarjodi.com/graphic_app/designs_uploads/30faa7732dd59463cab93479d537f627.png";
  
  $this->db->where('profile', $anotherprofile);
    $query23 = $this->db->get('user_register');
    $t = $query23->row_array();
    
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

    
         $this->response([
                    'status' => true,
                    //'message' => 'Notification send',
                   // 'check' => $check,
                    'message' => 'Interest Sent',
                    //'token' => $token,
                ], REST_Controller::HTTP_OK);
   
            
        }
      
}
//===================================================================//
     function chat_save_post()
    {   
       
        $log_user_profile =   $this->input->post('logged_user_profile');   
        $user_profile =   $this->input->post('user_profile');  
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
          $log_user_profile =   $this->input->post('logged_user_profile'); 
          
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
 function chat_msg()
    {
         $log_user_profile =   $this->input->post('logged_user_profile'); 
         $chat_userprofile =   $this->input->post('user_profile');
         
        $this->db->select('user_register.id,user_register.mobile,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,profile_images.file_name');
$this->db->from('chat')->group_start()
                                    ->where('chat_from', $log_user_profile)
                                    ->where('chat_to', $chat_userprofile)
                                    
                                    ->or_group_start()
                                            ->where('chat_from', $chat_userprofile)
                                    ->where('chat_to',$log_user_profile )
                                    ->order_by('id', 'asc')
                                    ->group_end()
                                    
                            ->group_end();

$this->db->join('user_register', 'user_register.profile = chat.chat_from','left');

$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');

 
// $this->db->where('chat.chat_to',$log_user_profile );
// $this->db->order_by('chat.id', 'desc');
// $this->db->group_by("chat.chat_from");


  $query1 = $this->db->get();
  $chatlist = $query1->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'chatlist' => $chatlist,
                    
                ], REST_Controller::HTTP_OK);
        
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
 //=============================================================//
 public function payment_status_post()
  {
    
    $this->load->view('payment_check');

  } 
  
  //=================================================================//
  function notifiy_post(){
    
   //$token = "caIUiSKNSGS8mp70ypEBh7:APA91bExoGcn67y2Qz11mA7aRVU7b6fzt7QCWV90IyAWMA85khGHU_jYRuja97AYNNgN-UZBnXy4cb81DY3ZdQfB6LMy5ePhXjdUBJ1Y6HKqaw1XIT25DVgala-TpDhyX7Twna3xWkwM";
   $message_body = "SUNDAR JODI - Marathi vadhu var suchak | vadhu var suchak Pune ";
   $message_title = "Welcome to SUNDAR JODI";
   $message_photo = "https://sundarjodi.com/graphic_app/designs_uploads/30faa7732dd59463cab93479d537f627.png";
   
    $query2 = $this->db->get('user_register');
    $t = $query2->row_array();
    
    foreach ($query2->result() as $row){
        $tid =	$row->id;
        $token =	$row->token;
        $user_id =	$row->profile;
       
if(!empty($token)){   
$notification = array();
$arrNotification= array();			
$arrData = array();		
$arrNotification["profile_id"] = $user_id;
$arrNotification["row_id"] = $row_id;
//$arrNotification["profile_id"] = $user_id;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["photo"] = $message_photo;
$arrNotification["msg_type"] = 'interest_send';
 $arrNotification["sound"] = "default";
// $arrNotification["type"] = 1;


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
         $data = array(
           
            //'profile' => $user_id,
            'title' => $message_title,
            'message' => $message_body,
            'photo' => $message_photo,
            'msg_type' => 'Welcome',
             );
            $partner_program = $this->db->insert('notify_message', $data);
}
}
 }

    if($check){
         $this->response([
                    'status' => true,
                    'message' => 'Notification send',
                    'check' => $check,
                    //'token' => $token,
                ], REST_Controller::HTTP_OK);
    }
  }

  //=============================================================//
   function count_post(){
       
    $userlogin_id =   $this->input->post('logged_user_id');  
    $log_user_profile =   $this->input->post('logged_user_profile'); 
     
 $query = $this->db->query("SELECT *  FROM interest where profile_id = '$userlogin_id' AND reject = 0 AND accept = 0 group by logged_user_id");
   $count_int_receive =  $query->num_rows();
  
 $query1 = $this->db->query("SELECT *  FROM interest where profile_id = '$userlogin_id' AND reject = 0 AND accept = 1 group by logged_user_id");
   $count_int_accepted =  $query1->num_rows(); 
   
        
$query2 = $this->db->query("SELECT viewed_profile_id  FROM recently_viewed_profiles where viewed_profile_id = '$log_user_profile' GROUP BY logged_profile_id ");
  $count_viewed_profile =  $query2->num_rows();
   
  $this->response([
                    'status' => true,
                    'message' => 'done success',
                    'count_int_receive' => $count_int_receive,
                    'count_int_accepted' => $count_int_accepted,
                    'count_viewed_profile' => $count_viewed_profile,
                    
                ], REST_Controller::HTTP_OK);
   }
   //=============================================================//
 public function get_offers_post()
  {
      
       $this->db->select('*');
        $this->db->from('offers');
        $this->db->where('status', 1);
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $ad = $query1->result();
            
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'offer' => $ad,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                  
                    
                ], REST_Controller::HTTP_OK);
       
            }
        
  } 
  //=============================================================//
 public function advertisment_post()
  {
      
       $this->db->select('*');
        $this->db->from('advertisment');
        $this->db->where('status', 'Active');
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $ad = $query1->result();
            
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'ads' => $ad,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                  
                    
                ], REST_Controller::HTTP_OK);
       
            }
        
  }  
    //=============================================================//
 public function notifications_post()
  {
      $log_user_profile =   $this->input->post('logged_user_profile');
      
       $this->db->select('*');
        $this->db->from('notifications');
        $this->db->where('logged_user', $log_user_profile);
        $this->db->order_by('id', 'desc');
       $this->db->limit(15);
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $ad = $query1->result();
            
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'notification' => $ad,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Record Found',
                  
                    
                ], REST_Controller::HTTP_OK);
       
            }
        
  } 
     //=============================================================//
 public function profile_complete_post()
  {
     $log_user_profile =   $this->input->post('logged_user_profile');
     
    $this->db->select('*');
        $this->db->from('user_register');
        $this->db->where('profile', $log_user_profile);
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
         foreach ($query1->result() as $row1){
          $log_user_log_user_username = 	$row1->first_name;
          $log_user_usermobile = 	$row1->mobile;
          $log_user_useremail = 	$row1->email;
          $log_user_caste = 	$row1->caste;
          $log_user_gender = 	$row1->gender;
          $log_mother_tongue = 	$row1->mother_tongue;
          $log_user_height1 = 	$row1->height;
          $log_user_martial_status = 	$row1->martial_status;
          $log_user_dob1 = 	$row1->dob;
          $log_user_birth_time = 	$row1->birth_time;
          $log_user_birth_city = 	$row1->birth_city;
          $log_user_body_type = 	$row1->body_type;
          $log_user_body_complexion = 	$row1->body_complexion;
          $log_user_weight = 	$row1->weight;
          $log_user_blood_group = 	$row1->blood_group;
          $log_user_lens = 	$row1->lens;
          $log_user_phy_disable = 	$row1->phy_disable;
         }
       } 
       
            $login_time =   date('Y-m-d H:i:s');
            $this->db->set('login_session', $login_time);
            $this->db->where('mobile', $log_user_usermobile);
            $result = $this->db->update('user_register');
           
           $data_logged = array(
             'mobile' => $log_user_usermobile,   
            'logged_date_time' => date('Y-m-d H:i:s'),
             );
         $result2 = $this->db->insert('user_logged_info', $data_logged);
       
    $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query2 = $this->db->get();
        if($query2->num_rows() > 0){
         foreach ($query2->result() as $row2){
          $log_user_perm_city = 	$row2->perm_city;
          $perm_state122 = 	$row2->perm_state;
          $log_user_perm_address = 	$row2->perm_address;
         }
       } 
       
         $this->db->select('*');
        $this->db->from('personal_habits');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query3 = $this->db->get();
        if($query3->num_rows() > 0){
         foreach ($query3->result() as $row3){
          $log_user_diet = 	$row3->diet;
          $log_user_smooking = 	$row3->smooking;
          $log_user_drinking = 	$row3->drinking;
          $log_user_hobbie = 	$row3->hobbie;
         }
       } 
       
          $this->db->select('*');
        $this->db->from('family_information');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query4 = $this->db->get();
        if($query4->num_rows() > 0){
         foreach ($query4->result() as $row4){
          $log_user_fatherrname = 	$row4->fatherrname;
          $log_user_father_presence = 	$row4->father_presence;
          $father_native_place11 = 	$row4->father_native_place;
          $log_user_motherrname = 	$row4->motherrname;
          $log_user_mother_presence = 	$row4->mother_presence;
          $mother_native_place11 = 	$row4->mother_native_place;
          $log_user_family_values = 	$row4->family_values;
          $log_user_family_finacial_backg = 	$row4->family_finacial_backg;
          $log_user_family_annual_income = 	$row4->family_annual_income;
          
         }
       } 
       
         $this->db->select('*');
        $this->db->from('education_work');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
         foreach ($query5->result() as $row5){
          $log_user_primary_edu = 	$row5->primary_edu;
          $log_user_highest_education = 	$row5->highest_education;
          $log_user_education_field = 	$row5->education_field;
          $log_user_education = 	$row5->education;
          $log_user_college_univ = 	$row5->college_univ;
          $log_user_occup = 	$row5->occup;
          $log_user_money = 	$row5->money;
          $log_user_work_city = 	$row5->work_city;
         
         }
       }
     
      $this->db->select('*');
        $this->db->from('partner_expection');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query6 = $this->db->get();
        if($query6->num_rows() > 0){
         foreach ($query6->result() as $row6){
          $log_user_marital_status_pe = 	$row6->marital_status;
          $log_user_caste_pe1 = 	$row6->caste;
          $log_user_highest_education_pe1 = 	$row6->highest_education;
          $log_user_education_field_pe1 = 	$row6->education_field;
          $log_user_primary_edu_pe1 = 	$row6->primary_edu;
          $log_user_working_partner_pe = 	$row6->working_partner;
          $log_user_occup_pe1 = 	$row6->occup;
          
         }
       }
         $this->db->select('*');
        $this->db->from('horoscope_details');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query7 = $this->db->get();
        if($query7->num_rows() > 0){
         foreach ($query7->result() as $row7){
          $log_user_rashi = 	$row7->rashi;
         
          
         }
       }
 
      
    if($log_user_log_user_username != '') {$a = 1;}else{  $a = 0;}
    if($log_user_usermobile != '') {$b = 1;}else{  $b = 0;}
    if($log_user_useremail != '') {$c = 1;}else{  $c = 0;}
    if($log_user_caste != '') {$d = 1;}else{  $d = 0;}
    if($log_user_gender != '') {$e = 1;}else{  $e = 0;}
    if($log_mother_tongue != '') {$f = 1;}else{  $f = 0;}
    if($log_user_height1 != '') {$g = 1;}else{  $g = 0;}
    if($log_user_martial_status != '') {$h = 1;}else{  $h = 0;}
    
    if($log_user_dob1 != '0000-00-00') {$j = 1;}else{  $j = 0;}
    if($log_user_birth_time != '00:00:00') {$k = 1;}else{  $k = 0;}
    if($log_user_birth_city != '') {$l = 1;}else{ $l = 0;}
    if($log_user_body_type != '') {$m = 1;}else{ $m = 0;}
    if($log_user_body_complexion != '') {$n = 1;}else{  $n = 0;}
    if($log_user_weight != '') {$o = 1;}else{  $o = 0;}
    if($log_user_blood_group != '') {$p = 1;}else{  $p = 0;}
    if($log_user_lens != '') {$q = 1;}else{  $q = 0;}
    if($log_user_phy_disable != '') {$r = 1;}else{  $r = 0;}
    
    
    if($log_user_perm_city != '') {$u = 1;}else{  $u = 0;}
    if($perm_state122 != '') {$v = 1;}else{  $v = 0;}
    if($log_user_perm_address != '') {$w = 1;}else{  $w = 0;}
    
    if($log_user_diet != '') {$x = 1;}else{  $x = 0;}
    if($log_user_smooking != '') {$y = 1;}else{  $y = 0;}
    if($log_user_drinking != '') {$z = 1;}else{  $z = 0;}
    if($log_user_hobbie != '') {$ab= 1;}else{  $ab = 0;}
    
    if($log_user_fatherrname != '') {$ac = 1;}else{  $ac = 0;}
    if($log_user_father_presence != '') {$ad = 1;}else{  $ad = 0;}
    if($father_native_place11 != '') {$ae = 1;}else{  $ae = 0;}
    if($log_user_motherrname != '') {$af = 1;}else{  $af = 0;}
    if($log_user_mother_presence != '') {$ag = 1;}else{  $ag = 0;}
    if($mother_native_place11 != '') {$ah= 1;}else{  $ah = 0;}
   
    if($log_user_family_values != '') {$am = 1;}else{  $am = 0;}
    if($log_user_family_finacial_backg != '') {$an = 1;}else{  $an = 0;}
    if($log_user_family_annual_income != '') {$ao = 1;}else{  $ao = 0;}
    
    if($log_user_primary_edu != '') {$aq = 1;}else{  $aq = 0;}
    if($log_user_highest_education != '') {$ar = 1;}else{  $ar = 0;}
    if($log_user_education_field != '') {$as = 1;}else{  $as = 0;}
    if($log_user_education != '') {$at = 1;}else{  $at = 0;}
    if($log_user_college_univ != '') {$au = 1;}else{  $au = 0;}
    if($log_user_occup != '') {$av = 1;}else{  $av = 0;}
    
    if($log_user_money != '') {$ay = 1;}else{  $ay = 0;}
    if($log_user_work_city != '') {$az = 1;}else{  $az  = 0;}
    
    if($log_user_marital_status_pe != '') {$ba = 1;}else{  $ba = 0;}
    if($log_user_caste_pe1 != '') {$bb = 1;}else{  $bb = 0;}
    if($log_user_highest_education_pe1 != '') { $bc = 1; }else{  $bc = 0;}
    if($log_user_education_field_pe1 != '') {$bd = 1;}else{  $bd = 0;}
    if($log_user_primary_edu_pe1 != '') {$be = 1;}else{  $be = 0;}
    if($log_user_working_partner_pe != '') {$bf = 1;}else{  $bf = 0;}
    if($log_user_occup_pe1 != '') {$bg = 1;}else{  $bg = 0;}
    
    
    if($log_user_rashi != '') {$bh = 1;}else{ $bh = 0;}
    
    $total_fileds = 49;
    $total_fill = $a + $b +$c + $d + $e + $f + $g + $h + $j + $k + $l + $m + $n + $o + $p + $q + $r + $u + $v +$w +$x + $y + $z  + $ab + $ac + $ad + $ae + $af + $ag + $ah + $am + $an + $ao  + $aq + $ar + $as + $at + $au + $av + $ay + $az +  $ba +  $bb  + $bc + $bd + $be + $bf + $bg + $bh;  
    $total_sum = $total_fill/$total_fileds*100;
     $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'sum' => round($total_sum),
                    
                ], REST_Controller::HTTP_OK);
    
  }
  
   //=============================================================//
   //=============================================================//
 public function identity_badge_post()
  {
      $log_user_id =   $this->input->post('logged_user_id');
      
       $this->db->select('*');
        $this->db->from('user_documents');
        $this->db->where('reg_id', $log_user_id);
        $this->db->limit(1);
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
          $ad = $query1->result();
          foreach ($query1->result() as $row7){
          $identity_badge = 	$row7->identity_badge;
         }
            
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    
                    'identity_badge' => $identity_badge,
                    
                ], REST_Controller::HTTP_OK);
           
       }else{
           
         $this->response([
                    'status' => TRUE,
                    'message' => 'No Document Found',
                     'identity_badge' => '0',
                ], REST_Controller::HTTP_OK);
       
            }
        
  } 
  //=================================================================//
 public  function preference_compare_post(){
     
    $logged_user_profile =   $this->input->post('logged_user_profile');
    
$this->db->select('user_register.id,user_register.dob,user_register.height,user_register.profile,user_register.caste,user_register.martial_status,user_register.status,contact_info.perm_state,contact_info.perm_city,education_work.highest_education,education_work.education_field,personal_habits.diet');
$this->db->from('user_register');
$this->db->join('personal_habits', 'personal_habits.reg_profil_id = user_register.profile','left');
$this->db->join('education_work', 'education_work.reg_profil_id = user_register.profile','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = user_register.profile','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
//$this->db->where('logged_user_id',$user_id);
//$this->db->where('profile_id',$regid ); 
$this->db->where('user_register.profile', $logged_user_profile); 
 

$query1 = $this->db->get();
  $searchbyid = $query1->result_array();
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $searchbyid,
                    
                ], REST_Controller::HTTP_OK);
 }
     //=============================================================//
 public function check_profile_fill_post()
  {
     $log_user_profile =   $this->input->post('logged_user_profile');
     
    $this->db->select('*');
        $this->db->from('user_register');
        $this->db->where('profile', $log_user_profile);
        $query1 = $this->db->get();
        if($query1->num_rows() > 0){
            
             $personal = $query1->result();
             
         foreach ($query1->result() as $row1){
          $log_user_log_user_username = 	$row1->first_name;
          $log_user_usermobile = 	$row1->mobile;
          $log_user_useremail = 	$row1->email;
          $log_user_caste = 	$row1->caste;
          $log_user_gender = 	$row1->gender;
          $log_mother_tongue = 	$row1->mother_tongue;
          $log_user_height1 = 	$row1->height;
          $log_user_martial_status = 	$row1->martial_status;
          $log_user_dob1 = 	$row1->dob;
          $log_user_birth_time = 	$row1->birth_time;
          $log_user_birth_city = 	$row1->birth_city;
          $log_user_body_type = 	$row1->body_type;
          $log_user_body_complexion = 	$row1->body_complexion;
          $log_user_weight = 	$row1->weight;
          $log_user_blood_group = 	$row1->blood_group;
          $log_user_lens = 	$row1->lens;
          $log_user_phy_disable = 	$row1->phy_disable;
         }
       } 
       
    $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query2 = $this->db->get();
        if($query2->num_rows() > 0){
            
             $contact = $query2->result();
             
         foreach ($query2->result() as $row2){
          $log_user_perm_city = 	$row2->perm_city;
          $perm_state122 = 	$row2->perm_state;
          $log_user_perm_address = 	$row2->perm_address;
         }
       } 
       
         $this->db->select('*');
        $this->db->from('personal_habits');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query3 = $this->db->get();
        if($query3->num_rows() > 0){
            $habbit = $query3->result();
         foreach ($query3->result() as $row3){
          $log_user_diet = 	$row3->diet;
          $log_user_smooking = 	$row3->smooking;
          $log_user_drinking = 	$row3->drinking;
          $log_user_hobbie = 	$row3->hobbie;
         }
       } 
       
          $this->db->select('*');
        $this->db->from('family_information');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query4 = $this->db->get();
        if($query4->num_rows() > 0){
             $family = $query4->result();
         foreach ($query4->result() as $row4){
          $log_user_fatherrname = 	$row4->fatherrname;
          $log_user_father_presence = 	$row4->father_presence;
          $father_native_place11 = 	$row4->father_native_place;
          $log_user_motherrname = 	$row4->motherrname;
          $log_user_mother_presence = 	$row4->mother_presence;
          $mother_native_place11 = 	$row4->mother_native_place;
          $log_user_family_values = 	$row4->family_values;
          $log_user_family_finacial_backg = 	$row4->family_finacial_backg;
          $log_user_family_annual_income = 	$row4->family_annual_income;
          
         }
       } 
       
         $this->db->select('*');
        $this->db->from('education_work');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query5 = $this->db->get();
        if($query5->num_rows() > 0){
             $education = $query5->result();
         foreach ($query5->result() as $row5){
          $log_user_primary_edu = 	$row5->primary_edu;
          $log_user_highest_education = 	$row5->highest_education;
          $log_user_education_field = 	$row5->education_field;
          $log_user_education = 	$row5->education;
          $log_user_college_univ = 	$row5->college_univ;
          $log_user_occup = 	$row5->occup;
          $log_user_money = 	$row5->money;
          $log_user_work_city = 	$row5->work_city;
         
         }
       }
     
      $this->db->select('*');
        $this->db->from('partner_expection');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query6 = $this->db->get();
        if($query6->num_rows() > 0){
             $partner_expection = $query6->result();
         foreach ($query6->result() as $row6){
          $log_user_marital_status_pe = 	$row6->marital_status;
          $log_user_caste_pe1 = 	$row6->caste;
          $log_user_highest_education_pe1 = 	$row6->highest_education;
          $log_user_education_field_pe1 = 	$row6->education_field;
          $log_user_primary_edu_pe1 = 	$row6->primary_edu;
          $log_user_working_partner_pe = 	$row6->working_partner;
          $log_user_occup_pe1 = 	$row6->occup;
          
         }
       }
         $this->db->select('*');
        $this->db->from('horoscope_details');
        $this->db->where('reg_profil_id', $log_user_profile);
        $query7 = $this->db->get();
        if($query7->num_rows() > 0){
            $horoscope = $query7->result();
         foreach ($query7->result() as $row7){
          $log_user_rashi = 	$row7->rashi;
         
          
         }
       }
 
  if($log_mother_tongue == '' || $log_user_height1 == '' ||  $log_user_dob1 == '0000-00-00' || $log_user_birth_time == '00:00:00' || $log_user_birth_city == '' || $log_user_weight == '' ||  $log_user_perm_city == '' || $perm_state122 == '' || $log_user_perm_address == '' || $log_user_diet == '' )
  {
    $info1 =  1;
  }
  
   if($log_user_fatherrname == '' || $log_user_father_presence == '' ||  $father_native_place11 == '' || $log_user_motherrname == '' || $log_user_mother_presence == '' || $mother_native_place11 == '' || $log_user_family_values == '' || $log_user_family_finacial_backg == '' || $log_user_family_annual_income == '')
  {
    $info2 =  2;
  }
  
   if($log_user_primary_edu == '' || $log_user_highest_education == '' ||  $log_user_education_field == '' || $log_user_education == '' || $log_user_college_univ == '' || $log_user_occup == '' || $log_user_money == '' || $log_user_family_finacial_backg == '' || $log_user_work_city == '')
  {
    $info3 =  3;
  }

  if($log_user_rashi == '')
  {
    $info4 =  4;
  }
  
    if($log_user_marital_status_pe == '')
  {
    $info5 =  5;
  }

if(isset($info1)){
    $info = $info1;
}else if(isset($info2)){
    $info = $info2;
}else if(isset($info3)){
    $info = $info3;
}else if(isset($info4)){
    $info = $info4;
}else if(isset($info5)){
    $info = $info5;
}else{
    $info = 0;
}

    
     $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'incomeplete_status' => $info,
                    'personalinfo' => $personal,
                    'contactinfo' => $contact,
                    'habbitinfo' => $habbit,
                    'educationinfo' => $education,
                    'familyinfo' => $family,
                    'horoscopeinfo' => $horoscope,
                    'partner_exception' => $partner_expection,
                ], REST_Controller::HTTP_OK);
    
  }
    //=============================================================//
 public function check_recent_visitor_post()
  {   
      $limit = 10; 
    $offset = $this->post('offset');
    $category = $this->post('category');
    $logged_user_profile  = strip_tags($this->post('logged_user_profile'));
    
    $this->db->where('profile',$logged_user_profile);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
    
 $this->db->select('user_register.id,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode' );
$this->db->from('recently_viewed_profiles');
$this->db->join('user_register', 'user_register.profile =  recently_viewed_profiles.logged_profile_id  ','left');

$this->db->join('memberships', 'memberships.member_profile_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('education_work', 'education_work.reg_profil_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = recently_viewed_profiles.logged_profile_id','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
// $this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');
// $this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->where('recently_viewed_profiles.viewed_profile_id',$logged_user_profile ); 
//$this->db->where('viewed_profile_id','user_register.profile' );
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 
$this->db->order_by('user_register.id','desc'); 
$this->db->group_by("recently_viewed_profiles.logged_profile_id");
 if ($offset == 0) {
        $this->db->limit($limit);
    } else if ($offset != 0) {
        $this->db->limit($limit, $offset);
    }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();
  $recent_view = $query11->result_array();
  
  
				 $this->response([
                    'status' => TRUE,
                    'message' => 'Fetch Successfully',
                    'profile' => $recent_view,
                   
                ], REST_Controller::HTTP_OK);
    } 
//===========================================================//
 public function update_on_off_post(){
       
    $profile = trim($this->post('profile'));   
    $on_off_status = trim($this->post('on_off_status')); 
    $today = date("Y-m-d");
   
    $this->db->set('online_offline_status', $on_off_status);
    $this->db->set('online_date', $today);
    $this->db->where('profile', $profile);
    $register =   $this->db->update('user_register');
      if($register){          
      $this->response([
                    'status' => true,
                    'message' => 'data updated',
                    
                ], REST_Controller::HTTP_OK);
      }else{
          $this->response([
                    'status' => false,
                    'message' => 'error',
                    
                ], REST_Controller::HTTP_OK);
      }
   }
   //===========================================================//
   public function update_app_version_post(){
       
    $profile = trim($this->post('profile'));   
    $version = trim($this->post('app_version')); 
    $today = date("Y-m-d");
   
    $this->db->set('app_version', $version);
    $this->db->set('app_version_update', $today);
    $this->db->where('profile', $profile);
    $register =   $this->db->update('user_register');
      if($register){          
      $this->response([
                    'status' => true,
                    'message' => 'App Version Updated',
                    
                ], REST_Controller::HTTP_OK);
      }else{
          $this->response([
                    'status' => false,
                    'message' => 'error',
                    
                ], REST_Controller::HTTP_OK);
      }
   }
//===========================================================//
 public function user_discount_plan_post()
  {
      $profile  = strip_tags($this->post('profile'));
      $todaydate = date('Y-m-d');
  
     $NewDate = Date('Y-m-d', strtotime('+3 days'));
     $anotherdate = Date('Y-m-d', strtotime('+15 days'));
      
        $this->db->select('*');
        $this->db->from('memberships');
        $this->db->where('member_profile_id', $profile);
        $query = $this->db->get();
        if($query->num_rows() > 0){
        foreach ($query->result() as $row13){ 
            
            $payment_mode =	$row13->payment_mode;
            $package_validity =	$row13->package_validity;
            $remaining_profiles =	$row13->remaining_profiles;
            $total_profiles_alloted =	$row13->total_profiles_alloted;
            
        }
        }
        
        $query3 = $this->db->query("SELECT profile, discount_percent, discount_validity, DATEDIFF(CURDATE(), login_session) AS 'no_of_days' from user_register where profile = '$profile' ");
       // $count_check = $query->result_array();
        foreach ($query3->result() as $row31){ 
            $userprofile =	$row31->profile;
            $no_of_days =	$row31->no_of_days;
            $discount_percent =	$row31->discount_percent;
            $discount_validity =	$row31->discount_validity;
        }
        
        if($no_of_days > 30 && $no_of_days < 60){
            $no_days = $no_of_days;
            $disocunt = '60';
            $validity = $NewDate;
        }else if($no_of_days > 60 && $no_of_days < 90) {
            
             $no_days = $no_of_days;
            $disocunt = '70';
            $validity = $NewDate;
        }else if($no_of_days > 90 ) {
             $no_days = $no_of_days;
            $disocunt = '80';
            $validity = $NewDate;
        }else if($no_of_days < 30 ){
            if($payment_mode == 'Paid'){
                $offer = '25'; 
                $no_days = $no_of_days;
                 $disocunt = $offer;
             $validity = $anotherdate;
            }else{
                $offer = '45';
             $no_days = $no_of_days;
             $disocunt = $offer;
             $validity = $anotherdate;
            }
             
        }else{
            $no_days = $no_of_days;
             $disocunt = '0';
             $validity = '0';
        }
        
        $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'membership' => 'Free',
                    'no_of_days' => $no_days,
                    'disocunt' => $disocunt,
                    'validity' => $validity,
                    
                ], REST_Controller::HTTP_OK);
        
        // if($payment_mode == 'Paid' &&  $remaining_profiles != $total_profiles_alloted && $package_validity >= $todaydate){
            
        //     $this->response([
        //             'status' => TRUE,
        //             'message' => 'Done Successfully',
        //             'membership' => 'Paid',
                    
        //         ], REST_Controller::HTTP_OK); 
        // }else{
            
            
                  
        // $this->response([
        //             'status' => TRUE,
        //             'message' => 'Done Successfully',
        //             'membership' => 'Free',
        //             'no_of_days' => $no_days,
        //             'disocunt' => $disocunt,
        //             'validity' => $validity,
                    
        //         ], REST_Controller::HTTP_OK);
            
        // }
      
  }
 //========================================================//
  public function membership_info_post(){   
      
        $profile  = strip_tags($this->post('profile'));
        
        $query = $this->db->query("SELECT member_profile_id AS  'profile', total_profiles_alloted AS 'alloted_profiles', remaining_profiles AS 'viewed_profiles', payment_mode AS 'user_type', package_validity AS 'membership_validity',created_date AS 'membership_date' from memberships where member_profile_id = '$profile' ");
        
        if($query->num_rows() > 0){
            
          $t = $query->result_array();
            
        }else{
          return 0;
        }
         $this->response([
                    'status' => TRUE,
                    'message' => 'Done Successfully',
                    'membership' => $t,
                ], REST_Controller::HTTP_OK);
       
    }
//=====================================================//
 public function looking_you_post()
  {
      
  }
 //=============================================================//
 public function userinfo_post()
  {
       $profile  = strip_tags($this->post('profile'));
       
       
        // Validate the post data
        if(!empty($profile)){
            
            // Check if any user exists with the given credentials
            $con['returnType'] = 'single';
            $con['conditions'] = array(
                'profile' => $profile,
                
                
            );
            $user = $this->user->getRows($con);
            
            if($user){
               
        
                $this->response([
                    'status' => TRUE,
                    'message' => 'Data Fetch Success',
                    'data' => $user
                ], REST_Controller::HTTP_OK);
            }else{
                
                 $this->response([
                    'status' => false,
                    'message' => 'No record found',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
                
                
            }
        }else{
             $this->response([
                    'status' => false,
                    'message' => 'Provide Profile id',
                  // 'data' => $user
                ], REST_Controller::HTTP_OK);
           
        }
       
      
        
  }
  
   //===========================================================//
   public function srf_status_post(){
    $another_profile = trim($this->post('another_rowid'));  
    $log_profile = trim($this->post('log_rowid')); 
   
    $countintrecac = $this->db->query("select * from  interest where logged_user_id = '$log_profile' AND profile_id = '$another_profile' OR logged_user_id = '$another_profile' AND profile_id = '$log_profile' AND reject = 0  group by logged_user_id");
    $tcount = $countintrecac->num_rows() ; 
    
   
    $countav = $this->db->query("select * from  favourites where profile_id = '$another_profile' AND user_logged_id = '$log_profile'  group by user_logged_id");
    $tcountfav = $countav->num_rows() ; 

if($tcount == '1' ){
     $this->response([
                    'status' => true,
                    'message' => 'success',
                    
                ], REST_Controller::HTTP_OK);
}else{
     $this->response([
                    'status' => false,
                    'message' => 'not success',
                    
                ], REST_Controller::HTTP_OK);
}
         
      
   
   }
//========================================================//
  public function userList_post(){

     // POST data
     $postData = $this->input->post();

     // Get data
     $data = $this->user->getUsers($postData);

     echo json_encode($data);
   }
   
  //===========================================================//
   public function contact_count_day_post(){
    $profile = trim($this->post('profile'));   
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
    //================================================================//
public function photo_request_post() {
          
     $log_user_profile = strip_tags($this->post('logged_user_profile'));
     $profile = strip_tags($this->post('another_profile'));
     
     $todaydate = date('Y-m-d H:i:s');
     
     if(!empty($log_user_profile) || !empty($profile) ){
    
     
    $message_body = "Profile id: ".$log_user_profile." requested you to upload your profile photo";
     $message_title = "Upload your profile photo";    	
  
  $this->db->where('profile', $profile);
    $query23 = $this->db->get('user_register');
  //  $t = $query23->row_array();
    
    foreach ($query23->result() as $row3){
        $tid =	$row3->id;
        $token =	$row3->token;
        
       
if(!empty($token)){   
$notification = array();
$arrNotification= array();			
$arrData = array();		
$arrNotification["profile"] = $log_user_profile;
//$arrNotification["row_id"] = $log_user_profile;
$arrNotification["message"] = $message_body;
$arrNotification["title"] = $message_title;
$arrNotification["msg_type"] = 'add_photo';
// $arrNotification["sound"] = "default";


$check = $this->user->fcm($token, $arrNotification, "Android"); 
if($check){
       
        $this->db->select("*");
        $this->db->from('notifications');  
        $this->db->where('logged_user',$profile);
        $this->db->where('second_user',$log_user_profile   );
         $this->db->where('action','add_photo' );
        $queryrw = $this->db->get();    
        if ($queryrw->num_rows() > 0){}else{ 
         $data_notify = array(
            'logged_user' => $profile,
            'second_user' => $log_user_profile ,
            'date_created' => date('Y-m-d'),
            'title' => $message_title,
            'msg' => $message_body,
             'read_unread' => 0,
            'action' => 'add_photo'
             );
 $notifications =   $this->db->insert('notifications', $data_notify); 
}
        }
}
        
    }
        
        $this->response([
                    'status' => true,
                    'message' => 'Done successfully',
                    
                ], REST_Controller::HTTP_OK);
     }
     
     
}
   //=================================================================// 
  public  function filter_data_post(){
      
     $return_arr = array();
      $limit = 100;
      $offset =   $this->input->post('offset');
     $section =   $this->input->post('section');
      $profile1 =   $this->input->post('profile');
      $searchid =    $this->getsearchid();
      
      $marital_status =   $this->input->post('marital_status');
      $age_from =   $this->input->post('age_from');
      $age_to =   $this->input->post('age_to');
      
      $height_from =   $this->input->post('height_from');
      $height_to =   $this->input->post('height_to');
       
      $state =   $this->input->post('state');
      $city =   $this->input->post('city');
      $caste =   $this->input->post('caste');
      $education_field =   $this->input->post('education_field');
      $highest_education =   $this->input->post('highest_education');
      $occup =   $this->input->post('occup');
     
      $diet =   $this->input->post('diet');
      
      $smooking =   $this->input->post('smooking');
      
      $drinking =   $this->input->post('drinking');
 
     $data_member = array(
            'profile_id' => $profile1,
            'search_id' => $searchid,
            'marital_status' => $marital_status,
            'age_from' => $age_from,
            'age_to' => $age_to,
            'height_from' => $height_from,
            'height_to' => $height_to,
            'state' => $state,
            'city' => $city,
            'caste' => $caste,
            'education_field' => $education_field,
            'highest_education' => $highest_education,
            'occup' => $occup,
            'diet' => $diet,
            'smooking' => $smooking,
            'drinking' => $drinking,
            
             );
            $member = $this->db->insert('quick_search', $data_member);
  
            if($member){
   	  
	$this->db->where('profile',$profile1);
    $query12 = $this->db->get('user_register');
    if($query12->num_rows()>0){
    foreach ($query12->result() as $row13){
    $user_id =	$row13->id;
    $user_caste =	$row13->caste;
    $gender =	$row13->gender;
    } }
    if($gender == 'F'){
    $show_gender = 'M';
    }else{
    $show_gender = 'F';
    }
    
        $this->db->select("*");
		$this->db->from('quick_search');  
		$this->db->where('search_id',$searchid ); 
		$querypci = $this->db->get();
		foreach($querypci->result() as $rowcf)
		{
		    
			$search_name =$rowcf->search_name;
			$marital =$rowcf->marital_status;
			$maritalstatus = explode(", ",$marital);
			if($maritalstatus == '' || $marital == 'Any'){
			    $marital_status = '';
			}else{
			    
			$maritalstatus1 = '"' . implode('", "', $maritalstatus) . '"';
			if($maritalstatus1 == '""'){
			    $marital_status = '';
			}else{
			   $marital_status = 'AND martial_status IN ('.$maritalstatus1.')'; 
			}
			}
			
			$age_from =$rowcf->age_from;
			$age_to =$rowcf->age_to;
			if($age_from != '' && $age_to != ''){
			    $age = "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_from'
AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
			}
			
			$height_from =$rowcf->height_from;
			$height_to =$rowcf->height_to;
	        if($height_from != '' && $height_to != ''){
			    $height = " AND height  >= '$height_from'
 AND height <= '$height_to'";
			}
			
			
			$caste_d = $rowcf->caste;
			$caste_s = explode(", ",$caste_d);
			 $incaste1 = '"' . implode('", "', $caste_s) . '"';
			if($caste_d == 'Any' || $caste_s == '' ){
			   $incaste = ''; 
			}else{
			  if($incaste1 == '""'){
			      $incaste = '';
			  }else{
			      $incaste = 'AND caste IN ('.$incaste1.')';  
			  }
			}
			
		    $city =$rowcf->city;
		    $city_s = explode(", ",$city);
			 $city1 = '"' . implode('", "', $city_s) . '"';
			if($city == 'Any' || $city_s == '' ){
			   $incity = ''; 
			}else{
			  if($city1 == '""'){
			      $incity = '';
			  }else{
			      $incity = 'AND perm_city IN ('.$city1.')';  
			  }
			}
			
			 $state =$rowcf->state;
		    $state_s = explode(", ",$state);
			 $state1 = '"' . implode('", "', $state_s) . '"';
			if($state == 'Any' || $state_s == '' ){
			   $instate = ''; 
			}else{
			  if($state1 == '""'){
			      $instate = '';
			  }else{
			      $instate = 'AND perm_state IN ('.$state1.')';  
			  }
			}

			
			$education_field =$rowcf->education_field;
			$education_field_s = explode(", ",$education_field);
			 $education_field1 = '"' . implode('", "', $education_field_s) . '"';
			if($education_field == 'Any' || $education_field_s == '' ){
			   $ineducation_field = ''; 
			}else{
			  if($education_field1 == '""'){
			      $ineducation_field = '';
			  }else{
			      $ineducation_field = 'AND education_field IN ('.$education_field1.')';  
			  }
			}
			
			$highest_education =$rowcf->highest_education;
			$highest_s = explode(", ",$highest_education);
			 $highest1 = '"' . implode('", "', $highest_s) . '"';
			if($highest_education == 'Any' || $highest_s == '' ){
			   $inhighest = ''; 
			}else{
			  if($highest1 == '""'){
			      $inhighest = '';
			  }else{
			      $inhighest = 'AND highest_education IN ('.$highest1.')';  
			  }
			}
			
			$occup =$rowcf->occup;
			$occup_s = explode(", ",$occup);
			 $occup1 = '"' . implode('", "', $occup_s) . '"';
			if($occup == 'Any' || $occup_s == '' ){
			   $inoccup = ''; 
			}else{
			  if($occup1 == '""'){
			      $inoccup = '';
			  }else{
			      $inoccup = 'AND occup IN ('.$occup1.')';  
			  }
			}
			
			$diet =$rowcf->diet;
			$diet_s = explode(", ",$diet);
			 $diet1 = '"' . implode('", "', $diet_s) . '"';
			if($diet == 'Any' || $diet_s == '' ){
			   $indiet = ''; 
			}else{
			  if($diet1 == '""'){
			      $indiet = '';
			  }else{
			      $indiet = 'AND diet IN ('.$diet1.')';  
			  }
			}
				
// 		
// 			$smooking =$rowcf->smooking;
// 			$drinking =$rowcf->drinking;
		}
		
	
// 	/*---------------------------------------------------------------*/
	$today = date('Y-m-d');
	if($section == 'newmatches'){
	    //---------------------------------------------------//
   
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height ORDER BY id DESC LIMIT $offset, 50");
//print_r($this->db->last_query()); 	
    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
         
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
     $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where member_profile_id = '$profile'  ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
	}}
   
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}}
       
//   }}
    /*---------------------------------------------------------------*/	
	}
	
		if($section == 'recently_visitor'){
	    //---------------------------------------------------//
  
   
	$querymp = $this->db->query("SELECT *  FROM user_register where status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height ORDER BY id DESC  ");

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
  
  	$queryre = $this->db->query("SELECT *  FROM recently_viewed_profiles where  viewed_profile_id = '$profile1' AND logged_profile_id = '$profile'  group by logged_profile_id ORDER BY id DESC LIMIT $offset, 50 ");
  foreach($queryre->result() as $rowre)
	{ 
	     
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "file_name" => $file_name,
        "created_user" => $created_user,
        "verified" => $verified,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	} 
	}
   }
    /*---------------------------------------------------------------*/	
	}
	
	
		if($section == 'recently_viewed'){
	    //---------------------------------------------------//
	     
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
  
 	$queryre = $this->db->query("SELECT *  FROM recently_viewed_profiles where  logged_profile_id = '$profile1' AND viewed_profile_id = '$profile' group by viewed_profile_id ORDER BY id DESC LIMIT $offset, 50");
  foreach($queryre->result() as $rowre)
	{
       
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
	
		if($section == 'favourite_me'){
	    //---------------------------------------------------//
  
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 
	    
    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
  
// 	if(($age_from >= $age && $age_to <= $age) && ($height_from >= $height && $height_to <= $height)){
   
  	$queryre = $this->db->query("SELECT *  FROM favourites where  profile_id = '$user_id' AND user_logged_id = '$id' group by user_logged_id ORDER BY id DESC LIMIT $offset, 50 ");
  foreach($queryre->result() as $rowre)
	{ 
	    
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
	
			if($section == 'favourite'){
	    //---------------------------------------------------//
 
	     
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
  
 	$queryre = $this->db->query("SELECT *  FROM favourites where  user_logged_id = '$user_id' AND profile_id = '$id' group by  profile_id ORDER BY id DESC LIMIT $offset, 50 ");
  foreach($queryre->result() as $rowre)
	{ 
       
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
		if($section == 'mutualmatches'){
	    //---------------------------------------------------//
 
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
        $created_user = $rowrev->created_user;
        $verified = $rowrev->verified;
  
 
 	$queryre = $this->db->query("SELECT *  FROM interest where  profile_id = '$user_id' AND logged_user_id = '$id' OR profile_id = '$id' AND logged_user_id = '$user_id'  AND accept = '1' AND sent = '1'  ORDER BY id DESC LIMIT $offset, 10");
  foreach($queryre->result() as $rowre)
	{ 
	    
       
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => 1,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
	
		if($section == 'interest_received'){
	    //---------------------------------------------------//
 
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
        $created_user = $rowrev->created_user;
        $verified = $rowrev->verified;
  
 
 	$queryre = $this->db->query("SELECT *  FROM interest where  profile_id = '$user_id' AND logged_user_id = '$id'  AND sent = '1'  ORDER BY id DESC LIMIT $offset, 50");
  foreach($queryre->result() as $rowre)
	{ 
	    
       
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => 1,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
	
		if($section == 'premium'){
	    //---------------------------------------------------//
 
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height");
//print_r($this->db->last_query()); 

    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
        $created_user = $rowrev->created_user;
        $verified = $rowrev->verified;
  
 
 	$queryre = $this->db->query("SELECT *  FROM memberships where  member_profile_id = '$profile' AND payment_mode = 'Paid' ORDER BY id DESC LIMIT $offset, 50");
  foreach($queryre->result() as $rowre)
	{ 
	    
       
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
 
   
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
         $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where   payment_mode ='Paid' ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
  }}
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => 1,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}} 
    
   }
    /*---------------------------------------------------------------*/	
	}
	
		if($section == 'match_of_day'){
	    //---------------------------------------------------//
   
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height ORDER BY id DESC LIMIT $offset, 10");
//print_r($this->db->last_query()); 	
    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
         
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
     $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where member_profile_id = '$profile'  ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
	}}
   
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}}
       
//   }}
    /*---------------------------------------------------------------*/	
	}
	
		if($section == 'looking_you'){
	    //---------------------------------------------------//
   
	$querymp = $this->db->query("SELECT *  FROM user_register where  status ='1' AND gender = '$show_gender'  $marital_status $incaste $age $height ORDER BY id DESC LIMIT $offset, 10");
//print_r($this->db->last_query()); 	
    foreach($querymp->result() as $rowrev)
	{
	    $profile = $rowrev->profile;
	    $id = $rowrev->id;
	   	$username = $rowrev->first_name;
	   	$dob = $rowrev->dob;
	   	$status = $rowrev->status;
	   	$diff = date_diff(date_create($dob), date_create($today));
	    $age = $diff->format('%y'); 
	    $height = $rowrev->height;
	    $caste = $rowrev->caste;
	    $martial_status = $rowrev->martial_status;
	    $string = "'"; 
        $position = '1'; 
         $created_user = $rowrev->created_user;
         $verified = $rowrev->verified;
         
	$quecon = $this->db->query("SELECT *  FROM contact_info WHERE reg_profil_id = '$profile'
	 $incity $instate	");
    foreach($quecon->result() as $rowcon)
	{ 
	    $perm_city = $rowcon->perm_city;
	
	$queedu = $this->db->query("SELECT *  FROM education_work WHERE reg_profil_id = '$profile'
	 	 $ineducation_field $inhighest $inoccup"); 
	 
    foreach($queedu->result() as $rowedu)
	{ 
	    $highest_education = $rowedu->highest_education;
	    $education_field = $rowedu->education_field;
	    $occupw = $rowedu->occup;
	
	$queediet = $this->db->query("SELECT *  FROM personal_habits WHERE reg_profil_id = '$profile'
	 	 $indiet"); 
	 
    foreach($queediet->result() as $rowediet)
	{   
	 $dietd = $rowediet->diet;   
  //-------------------------------------------------------------------------//
    $quint = $this->db->query("select profile_id, sent, logged_user_id  from  interest where profile_id = '$id' AND  logged_user_id = '$user_id' limit 1");
    if($quint->num_rows() > 0){
   foreach ($quint->result() as $roint){
   $sent =  $roint->sent;
   }}else{
       $sent = '0';
   } 
    //---------------------------------------------------------------------//
    $qufav = $this->db->query("select profile_id, user_logged_id  from  favourites where profile_id = '$id' AND  user_logged_id = '$user_id' limit 1");
    if($qufav->num_rows() > 0){
  
  $favourite =  '1';
   }else{
       $favourite = '0';
   }
   //-------------------------------------------------------------------------//
    $quimg = $this->db->query("select   reg_id, file_name  from  profile_images where reg_id = '$id'  limit 1");
    if($quimg->num_rows() > 0){
   foreach ($quimg->result() as $roimg){
   $file_name =  $roimg->file_name;
   }}else{
       $file_name = '';
   } 
   //-------------------------------------------------------------------------//
     $qumem = $this->db->query("select payment_mode, member_profile_id from  memberships where member_profile_id = '$profile'  ");
    if($qumem->num_rows() > 0){
  foreach ($qumem->result() as $romem){
  $payment_mode =  $romem->payment_mode;
   $profile_id =  $romem->member_profile_id;
  
	}}
   
	   	$return_arr[] = array(
        "profile" => $profile, 
        "id" => $id,
        "name" => $username,
        "age" => $age,
        "dob" => $dob,
        "height" => $height,
        "martial_status" => $martial_status,
        "caste" => $caste,
        "perm_city" => $perm_city,
        "education_field" => $education_field,
        "highest_education" => $highest_education,
        "occup" => $occupw,
        "diet" => $dietd,
        "created_user" => $created_user,
        "verified" => $verified,
        "file_name" => $file_name,
        "sent" => $sent,"favourite" => $favourite,"payment_mode" => $payment_mode,
	   	);
	}
	}
	}}
       
//   }}
    /*---------------------------------------------------------------*/	
	}
	
    $this->response([
                    'status' => true,
                    'message' => 'data fetched',
                    //'searchid' => $searchid,
                    'profile' => $return_arr,
                    
                ], REST_Controller::HTTP_OK); 
		
            }
         
            
            
  }
 //========================================================//
}