<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model_api_3 extends CI_Model {

    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        $this->userTbl = 'user_register';
        $this->contactTbl = 'contact_info';
        $this->habbitTbl = 'personal_habits';
        $this->familyTbl = 'family_information';
        $this->eduworkTbl = 'education_work';
        $this->horoscopeTbl = 'horoscope_details';
        $this->partnerexpTbl = 'partner_expection';
    }
    
    function get_user_all_data($args=array()){
        
         $cond = "";
        
        if(!empty($args['profile_id'])){
            $profile = $args['profile_id'];
            $cond.= " AND register.profile = '$profile'";
        }
        if(!empty($args['id'])){
            $id = $args['id'];
            $cond.= " AND register.id = '$id'";
        }
        
        if(!empty($args['gender'])){
            $gender = $args['gender'];
            $cond.= " AND register.gender = '$gender'";
        }
        
        if(!empty($args['caste_name'])){
            $caste_name = $args['caste_name'];
            $cond.= " AND register.caste = '$caste_name'";
        }
        
        $PageSql = $this->db->query("SELECT register.id AS user_id,register.* ,contact.*,education_work.*,family_information.*,
        personal_habits.diet,personal_habits.smooking,personal_habits.drinking,
        personal_habits.party_pub,personal_habits.hobbie,horoscope_details.rashi,
        horoscope_details.charan,horoscope_details.nadi,horoscope_details.mangal,horoscope_details.birth_city,
        horoscope_details.nakshtra,horoscope_details.gan,horoscope_details.birth_hrs,
        horoscope_details.birth_min,horoscope_details.birth_ampm,
        horoscope_details.devak,horoscope_details.gotra
                FROM `user_register` AS register 
                LEFT JOIN contact_info AS contact
                ON(contact.reg_profil_id=register.profile)
                LEFT JOIN education_work AS education_work
                ON(education_work.reg_profil_id =register.profile)
                 LEFT JOIN family_information AS family_information
                ON(family_information.reg_profil_id =register.profile)
                 LEFT JOIN personal_habits AS personal_habits
                ON(personal_habits.reg_profil_id =register.profile)
                LEFT JOIN horoscope_details AS horoscope_details
                ON(horoscope_details.reg_profil_id =register.profile)
                
                WHERE register.status='1' $cond   GROUP BY register.id");
              
            //  echo $this->db->last_query();  die;
             
        return $PageSql->result();
     
    }
    
    
     function get_user_profil_data($args=array()){
        
         $cond = "";
        
       
        if(!empty($args['profile_id'])){
            $profile = $args['profile_id'];
            $cond.= " AND register.profile = '$profile'";
        }
        
        $PageSql = $this->db->query("SELECT register.*, register.id AS regester_id ,contact.*,education_work.*
                FROM `user_register` AS register 
                LEFT JOIN contact_info AS contact
                ON(contact.reg_profil_id=register.profile)
                 LEFT JOIN education_work AS education_work
                ON(education_work.reg_profil_id =register.profile)
                WHERE register.status='1' $cond   GROUP BY register.id ");
                
             
        return $PageSql->result();
        
        
    }
    
    function get_user_con_profile_data($args=array()){
        
         $cond = $reg_id =  "";
        
        if(!empty($args['reg_id'])){
            $reg_id = $args['reg_id'];
        
        
        if(!empty($args['profile_id'])){
            $profile = $args['profile_id'];
            $cond.= " AND register.profile = '$profile'";
        }
        
        $PageSql = $this->db->query("SELECT register.id AS regester_id,register.* ,contact.*,education_work.*
                FROM `user_register` AS register 
                LEFT JOIN contact_info AS contact
                ON(contact.reg_profil_id=register.profile)
                 LEFT JOIN education_work AS education_work
                ON(education_work.reg_profil_id =register.profile)
                WHERE register.id IN ($reg_id) AND register.status='1' $cond   GROUP BY register.id ");
                
              
             
        return $PageSql->result();
        
        }
    }
    
    function preminum_matched($args=array()){
        
         $cond = $reg_id =  "";
        
        if(!empty($args['payment_mode'])){
            $reg_id = $args['payment_mode'];
            $cond.= " AND memberships.payment_mode = '$reg_id'";
        }
        
        if(!empty($args['caste'])){
            $caste = $args['caste'];
            $cond.= " AND register.caste = '$caste'";
        }
        if(!empty($args['gender'])){
            $caste = $args['gender'];
            $cond.= " AND register.gender = '$caste'";
        }
        
        
        
         $PageSql = $this->db->query("SELECT register.* ,contact.*,profile_images.file_name,education_work.*,memberships.*
                FROM `user_register` AS register 
                LEFT JOIN contact_info AS contact
                ON(contact.reg_profil_id=register.profile)
                 LEFT JOIN education_work AS education_work
                ON(contact.reg_profil_id =register.profile)
                LEFT JOIN profile_images AS profile_images
                ON(profile_images.reg_id=register.id)
                LEFT JOIN memberships AS memberships
                ON(memberships.member_profile_id =register.profile)
                WHERE register.status='1' $cond   GROUP BY register.id ");
             
        return $PageSql->result();
    }
    
    function get_interest($args=array()){
        
            $this->db->select("*");
    		$this->db->from('interest');  
        
        	if(!empty($args['id'])){
    			$this->db->where('id',$args['id']); 
    		}
    		if(!empty($args['profile_id'])){
    			$this->db->where('profile_id',$args['profile_id']); 
    		}
    		if(!empty($args['logged_user_id'])){
    			$this->db->where('logged_user_id',$args['logged_user_id']); 
    		}
    		
    		$query1 = $this->db->get();
	     	return $query1->result();
    }
    
     function get_favourites($args=array()){
        
            $this->db->select("*");
    		$this->db->from('favourites');  
        
        	if(!empty($args['user_logged_id'])){
    			$this->db->where('user_logged_id',$args['user_logged_id']); 
    		}
    		
    		if(!empty($args['limit']) && $args['limit']!=""){
    		  $limit =$args['limit'];
    		}
    		if($args['offset']!=""){
    		  $offset = $args['offset'];
    		}
    		$this->db->limit($limit, $offset);
    		$this->db->order_by('id','desc' );
			$this->db->group_by('profile_id');
    		$query1 = $this->db->get();
    	
	     	return $query1->result();
    }
    
    
    function get_interest_data($args=array()){
        
   
        $condition = $order_by = "";
       
	
        if(!empty($args['reg_id'])){
          $condition.=" AND profile_id = '".$args['reg_id']."' ";
        }
	    if($args['sent']!=""){
          $condition.=" AND sent = '".$args['sent']."' ";
        }
		
		if($args['accept']!=""){
          $condition.=" AND accept = '".$args['accept']."' ";
        }
        if($args['reject']!=""){
          $condition.=" AND reject = '".$args['reject']."' ";
        }
	

        $querys = "SELECT * FROM interest WHERE 1=1 $condition  GROUP BY logged_user_id ORDER BY id desc";

        $result = $this->db->query($querys);
        
       // $this->db->last_query();die;
        return ($result) ? $result->result() : false; 

    }
    
//     function get_interest_data($args=array()){
        
        
       
        
//         $this->db->select("*");
// 		$this->db->from('interest');  
		
// 		if(!empty($args['reg_id'])){
// 			$this->db->where('profile_id',$args['reg_id']); 
// 		}
		
// 		if(!empty($args['sent'])){
// 			$this->db->where('sent',$args['sent']); 
// 		}
		
// 		if(!empty($args['reject'])){
// 			$this->db->where('reject',$args['reject']); 
// 		}
// 		if(!empty($args['accept'])){
// 			$this->db->where('accept',$args['accept']); 
// 		}
// 		if(!empty($args['sent_date'])){
// 			$this->db->order_by('sent_date',$args['sent_date']); 
// 		}
// 		if(!empty($args['accept_date'])){
// 			$this->db->order_by('accept_date',$args['accept_date']); 
// 		}
	
// 		$this->db->group_by('logged_user_id'); 
// 		$query1 = $this->db->get();
// 		echo $this->db->last_query();die;
		
// 		return $query1->result();
			        
//     }
    
    
    function get_educationwise_search_data($args=array()){
        
        
         $show_gender = $par_ex_caste = $heightfrom = $par_ex_marital_sts = $get_educ = "";
        $heightto = $agefrom = $ageto = "";
        
        if($args['highest_education']!=""){
            $get_educ = $args['highest_education'];
        }
        if($args['gender']!=""){
            $show_gender = $args['gender'];
        }
        
        if($args['marital_sts']!=""){
            $par_ex_marital_sts = $args['marital_sts'];
            $par_ex_marital_sts = "AND martial_status = '$par_ex_marital_sts' ";
        }
         if($args['caste']!=""){
            $caste = $args['caste'];
           $par_ex_caste = "AND caste IN ($caste)";
        }
           if($args['height_from']!="" && $args['height_to']!=""){
               $height_froms = $args['height_from'];
               $height_tos = $args['height_to'];
             $heightfrom.= "AND height >= '$height_froms'";
             $heightto.= "AND height <= '$height_tos'";
           }
           
           if($args['age_from']!="" && $args['age_to']!=""){
               $age_froms = $args['age_from'];
               $age_tos = $args['age_to'];
             $agefrom.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_froms'";
             $ageto.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_tos'";
           }
        
        $PageSql = $this->db->query("select user_register.id,user_register.gender,user_register.profile, user_register.status,user_register.first_name,user_register.created_user,user_register.dob,user_register.height,user_register.caste,user_register.martial_status, user_register.verified,contact_info.perm_city,education_work.highest_education,education_work.education_field,education_work.occup  from user_register
        JOIN contact_info ON contact_info.reg_profil_id = user_register.profile
        JOIN education_work ON education_work.reg_profil_id = user_register.profile
        where status = '1' AND gender = '$show_gender'  $par_ex_marital_sts $par_ex_caste  $heightfrom $heightto  $agefrom  $ageto AND highest_education LIKE '$get_educ'	ORDER BY id DESC ");
        return $PageSql->result();
        
    }
    
    function get_city_search_data($args=array()){
        
        
        $show_gender = $par_ex_caste = $heightfrom = $par_ex_marital_sts = $get_city = "";
        $heightto = $agefrom = $ageto = "";
        
        if($args['perm_city']!=""){
            $get_city = $args['perm_city'];
        }
        if($args['gender']!=""){
            $show_gender = $args['gender'];
        }
        
        if($args['marital_sts']!=""){
            $par_ex_marital_sts = $args['marital_sts'];
            $par_ex_marital_sts = "AND martial_status = '$par_ex_marital_sts' ";
        }
         if($args['caste']!=""){
            $caste = $args['caste'];
           $par_ex_caste = "AND caste IN ($caste)";
        }
           if($args['height_from']!="" && $args['height_to']!=""){
               $height_froms = $args['height_from'];
               $height_tos = $args['height_to'];
             $heightfrom.= "AND height >= '$height_froms'";
             $heightto.= "AND height <= '$height_tos'";
           }
           
           if($args['age_from']!="" && $args['age_to']!=""){
               $age_froms = $args['age_from'];
               $age_tos = $args['age_to'];
             $agefrom.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_froms'";
             $ageto.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_tos'";
           }
        
        $PageSql = $this->db->query("select user_register.id,user_register.gender,user_register.profile, user_register.status,user_register.first_name,user_register.created_user,user_register.dob,user_register.height,user_register.caste,user_register.martial_status, user_register.verified,contact_info.perm_city,education_work.highest_education,education_work.education_field,education_work.occup  from user_register
        JOIN contact_info ON contact_info.reg_profil_id = user_register.profile
        JOIN education_work ON education_work.reg_profil_id = user_register.profile
        where status = '1' AND gender = '$show_gender' $par_ex_marital_sts $par_ex_caste $heightfrom $heightto  $agefrom  $ageto AND perm_city like '$get_city'	ORDER BY id DESC");
        return $PageSql->result();
        
    }
    
    function get_occup_wise_data($args=array()){
        
        
        $show_gender = $par_ex_caste = $heightfrom = $par_ex_marital_sts = $get_occp = "";
        $heightto = $agefrom = $ageto = "";
        
        if($args['occup']!=""){
            $get_occp = $args['occup'];
        }
        if($args['gender']!=""){
            $show_gender = $args['gender'];
        }
        
        if($args['marital_sts']!=""){
            $par_ex_marital_sts = $args['marital_sts'];
            $par_ex_marital_sts = " AND martial_status = '$par_ex_marital_sts' ";
        }
         if($args['caste']!=""){
            $caste = $args['caste'];
           $par_ex_caste = "AND caste IN ($caste)";
        }
           if($args['height_from']!="" && $args['height_to']!=""){
               $height_froms = $args['height_from'];
               $height_tos = $args['height_to'];
             $heightfrom.= "AND height >= '$height_froms'";
             $heightto.= "AND height <= '$height_tos'";
           }
           
           if($args['age_from']!="" && $args['age_to']!=""){
               $age_froms = $args['age_from'];
               $age_tos = $args['age_to'];
             $agefrom.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= '$age_froms'";
             $ageto.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_tos'";
           }
           
           
           
      $PageSql = $this->db->query("select user_register.id,user_register.gender,user_register.profile, user_register.status,user_register.first_name,user_register.created_user,user_register.dob,user_register.height,user_register.caste,user_register.martial_status, user_register.verified,contact_info.perm_city,education_work.highest_education,education_work.education_field,education_work.occup
	 from user_register
        JOIN contact_info ON contact_info.reg_profil_id = user_register.profile
        JOIN education_work ON education_work.reg_profil_id = user_register.profile
        
        where status = '1' AND gender = '$show_gender'  $par_ex_marital_sts $par_ex_caste $heightfrom $heightto  $agefrom  $ageto	AND occup like '$get_occp' ORDER BY id DESC ");
        
        
        return $PageSql->result();
        
    }
    
    function update_profile($args=array()){
        
           if(isset($args['profile']) && @$args['profile']!=""){
               
                $this->db->where("profile",$args['profile']);
                $update = $this->db->update('user_register',$args);
                
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    function update_memberships($args=array()){
        
           if(isset($args['member_profile_id']) && @$args['member_profile_id']!=""){
               
                $this->db->where("member_profile_id",$args['member_profile_id']);
                $update = $this->db->update('memberships',$args);
                
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    function update_personal_habits($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('personal_habits',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    function update_partnerdata_info($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('partner_expection',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    function update_family_info($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('family_information',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    function update_horoscope_info($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('horoscope_details',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    function update_education_info($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('education_work',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
     function get_country($id=array()){
        $this->db->select('*');
        $this->db->from("countries");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    // function get_work_city($id=array()){
        
    //     $this->db->select('*');
    //     $this->db->from("cities");
    //     if(!empty($id['id']) && $id['id']!= ''){
    //       $this->db->where('id', $id['id']);
    //     }
    //     $this->db->order_by('name', 'ASC');
    //     return $this->db->get()->result();
    // }
    
    function get_mother_tongue($id=array()){
        
        $this->db->select('*');
        $this->db->from("mother_tounge");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_ages($id=array()){
        
        $this->db->select('*');
        $this->db->from("age");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_primary_education($id=array()){
        
        $this->db->select('*');
        $this->db->from("primary_education");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_highest_education($id=array()){
        
        $this->db->select('*');
        $this->db->from("highest_education_level");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_education($id=array()){
        $this->db->select('*');
        $this->db->from("education_degree");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_caste($id=array()){
        $this->db->select('*');
        $this->db->from("caste");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_state($id=array()){
        $this->db->select('*');
        $this->db->from("states");
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
        if(!empty($id['country_id']) && $id['country_id']!= ''){
          $this->db->where('country_id', $id['country_id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_city($id=array()){
        $this->db->select('*');
        $this->db->from("cities");
        
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
        if(!empty($id['state_id']) && $id['state_id']!= ''){
          $this->db->where('state_id', $id['state_id']);
        }
       return  $this->db->get()->result();
    }
    
    function get_occupation($id=array()){
       
       
        $this->db->select('*');
        $this->db->from("occupation");
        
        if(!empty($id['id']) && $id['id']!= ''){
          $this->db->where('id', $id['id']);
        }
      
       return  $this->db->get()->result();
    }
    
    
    function update_contact_info($args=array()){
        
           if(isset($args['reg_profil_id']) && @$args['reg_profil_id']!=""){
               
                $this->db->where("reg_profil_id",$args['reg_profil_id']);
                $update = $this->db->update('contact_info',$args);
                return ($update) ? true : false ;
            }else{
                return false;
            }
    }
    
    
     function update_user($args=array()){
         
            if(isset($args['mobile']) && @$args['mobile']!=""){
                
                $this->db->where("mobile",$args['mobile']);
                
                if(isset($args['otp']) && @$args['otp']!=""){
                  $ar['otp'] = $args['otp'];
                }
                if(isset($args['password']) && @$args['password']!=""){
                 $ar['password'] = $args['password'];
                }
                $update = $this->db->update('user_register',$ar);
                
                return ($update) ? true : false ;
            }else{
                return false;
            }
        
    }
    
    /*
     * Get rows from the horoscope_details table
     */
    function getpartner_details($params = array()){
        
        $this->db->select('*');
        $this->db->from("partner_expection");
      
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("reg_profil_id",$params)){
            $this->db->where('reg_profil_id',$params['reg_profil_id']);
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

        return $result;
    }
    
     /*
     * Get rows from the horoscope_details table
     */
    function gethoroscope_details($params = array()){
        
        $this->db->select('*');
        $this->db->from("horoscope_details");
      
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("reg_profil_id",$params)){
            $this->db->where('reg_profil_id',$params['reg_profil_id']);
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

        return $result;
    }
    
     /*
     * Get rows from the contact_info table
     */
    function getcontactinfo_details($params = array()){
        
        $this->db->select('*');
        $this->db->from("contact_info");
        
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("reg_profil_id",$params)){
            $this->db->where('reg_profil_id',$params['reg_profil_id']);
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

        return $result;
    }

    
    
     /*
     * Get rows from the education_work table
     */
    function geteducation_details($params = array()){
        
        $this->db->select('*');
        $this->db->from("education_work");
        
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("reg_profil_id",$params)){
            $this->db->where('reg_profil_id',$params['reg_profil_id']);
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

        return $result;
    }

     /*
     * Get rows from the users table
     */
    function getfamily_details($params = array()){
        
        $this->db->select('*');
        $this->db->from("family_information");
        
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
        
        if(array_key_exists("reg_profil_id",$params)){
            $this->db->where('reg_profil_id',$params['reg_profil_id']);
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

        return $result;
    }

    /*
     * Get rows from the users table
     */
    function getusers_details($params = array()){
        $this->db->select('*');
        $this->db->from($this->userTbl);
        
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
         //$this->db->where('status',"1");
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

        return $result;
    }
    
    
    //use this api new_matches,view_profile, view_profiles_by
    
    function get_new_macthed_data($args=array()){
   
        $limit = $offset = $condition = $like = "";
        
        if(!empty($args['gender'])){
          $condition.=" AND user_register.gender = '".$args['gender']."' ";
        }
        if(!empty($args['marital_status'])){
          $condition.=" AND user_register.martial_status = '".$args['marital_status']."' ";
        }
        if(!empty($args['caste'])){
          $condition.=" AND user_register.caste IN (".$args['caste'].") ";
        }
        if(!empty($args['age_from']) && !empty($args['age_to'])){
            $age_from = $args['age_from'];$age_to = $args['age_to'];
            $condition.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >='$age_from' AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
        }
        
        if(!empty($args['profile_id'])){
          $condition.=" AND user_register.profile = '".$args['profile_id']."' ";
        }
        if(!empty($args['id'])){
          $condition.=" AND user_register.id = '".$args['id']."' ";
        }
        
        if(!empty($args['height_from']) && !empty($args['height_to'])){
          $condition.= " AND (user_register.height >= '".$args['height_from']."' AND user_register.height <= '".$args['height_to']."')";
        }
             if(!empty($args['limit']) && $args['limit']!=""){
              $limit = $args['limit'];
    		  $limit = "LIMIT $limit";
    		}
    		if(@$args['offset']!=""){
    		   $offset = $args['offset'];
    		  $offset = "OFFSET $offset";
    		}


        $querys = "SELECT user_register.*
                   FROM user_register AS user_register
                   WHERE user_register.status=1 $condition $limit $offset ";

        $result = $this->db->query($querys);
        return ($result) ? $result->result() : false; 

       

    }
    
    
    function get_view_profiles_by($args=array()){
   
        $limit = $condition = $like = "";
        
        if(!empty($args['gender'])){
          $condition.=" AND user_register.gender = '".$args['gender']."' ";
        }
        if(!empty($args['marital_status'])){
          $condition.=" AND user_register.martial_status = '".$args['marital_status']."' ";
        }
        if(!empty($args['caste'])){
          $condition.=" AND user_register.caste IN (".$args['caste'].") ";
        }
        if(!empty($args['age_from']) && !empty($args['age_to'])){
            $age_from = $args['age_from'];$age_to = $args['age_to'];
            $condition.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >='$age_from' AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
        }
        
        // if(!empty($args['profile_id'])){
        //   $condition.=" AND user_register.profile = '".$args['profile_id']."' ";
        // }
        // if(!empty($args['id'])){
        //   $condition.=" AND user_register.id = '".$args['id']."' ";
        // }
        
        
        

        $this->db->select("*");
		$this->db->from('partner_expection');  
	    $this->db->where('reg_profil_id',$args['profile_id'] ); 
		$this->db->limit(1);
		$query3 = $this->db->get();
		
		if(!empty($query3->result())){
		    $caste = $query3->result()[0]->caste;
		    $log_user_caste_pe = explode(", ",$caste);
            $input = $caste; // Your comma-separated input values
            $valuesArray = explode(', ', $input); // Split the input into an array
            $arrayCount = count($valuesArray);
            $cleanedValues = array_map(function($value) {
            $value = trim($value); // Remove leading/trailing whitespace
            $value = stripslashes($value); // Remove slashes
            return "'" . addslashes($value) . "'"; // Add single quotes and properly escape
            }, $valuesArray);
    
            $finalString = implode(',', $cleanedValues);
        
        
        
	       $caste= ($query3->result()[0]->caste=="Any") ? "" : "AND caste IN ($finalString)";
	       
	       $height_form = ($query3->result()[0]->height_from!="") ? $query3->result()[0]->height_from : "";
	       $height_to = ($query3->result()[0]->height_to!="") ? $query3->result()[0]->height_to : "";
	       
	       $age_from = ($query3->result()[0]->age_from!="") ? $query3->result()[0]->age_from : "";
	       $age_to = ($query3->result()[0]->age_to!="") ? $query3->result()[0]->age_to : "";
	       
	       $condition.= ($caste!="") ? "AND caste IN ($finalString) " : "" ;
	       
	       	if(!empty($height_form) && !empty($height_to)){
                $condition.= "AND height >= '$height_form'";
                $condition.= "AND height <= '$height_to'";
            }
            
            if(!empty($age_from) && !empty($age_to)){
              $condition.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >='$age_from'";
              
              $condition.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
            }
	       
		}else{
		    $caste = "";
		}

        $querys = "SELECT user_register.*,contact.perm_city,education_work.occup,education_work.highest_education
                   FROM user_register AS user_register
                   LEFT JOIN contact_info AS contact
                ON(contact.reg_profil_id=user_register.profile)
                LEFT JOIN education_work AS education_work
                ON(education_work.reg_profil_id =user_register.profile)
                   WHERE user_register.status=1 $condition ORDER BY user_register.id DESC ";

        $result = $this->db->query($querys);
     
        return ($result) ? $result->result() : false; 

       

    }
    
    function get_profile_image($args=array()){
        
        if($args['reg_id']!=""){
        
        $this->db->select("*");
		$this->db->from('profile_images');  
		$this->db->where('reg_id',$args['reg_id'] );
		$this->db->order_by('id','DESC' );
		$this->db->limit(1); 
		$querypp = $this->db->get();
		return $querypp->result();
		
        }else{
            return false;
        }
    }
    
    function get_user_city($args=array()){
        
        if($args['reg_profil_id']!=""){
        
            $this->db->select("*");
			$this->db->from('contact_info');  
			$this->db->where('reg_profil_id',$args['reg_profil_id'] ); 
			$querypeco = $this->db->get();
			return $querypeco->result();
        }else{
            return false;
        }
    }
    
    
    function check_membership($args=array()){
        
        if($args['member_profile_id']!=""){
        	        $this->db->select("*");
            		$this->db->from('memberships');  
            		$this->db->where('member_profile_id',$args['member_profile_id']); 
            		$this->db->where('payment_mode',$args['payment_mode'] );
            		$this->db->limit(1);
            		$query3 = $this->db->get();
            		return $query3->result();
            		
            		
        }else{
            return false;
        }
        
    }
    
    
     function get_recently_visitor($args=array()){
        
        if($args['profile']!=""){
            $profile = $args['profile'];
         
         
            $querymp = $this->db->query("SELECT recently_profile.* ,register.id as user_id
            FROM recently_viewed_profiles  AS recently_profile
            LEFT JOIN user_register AS register
            ON recently_profile.logged_profile_id = register.profile
            WHERE register.status='1' AND recently_profile.viewed_profile_id = '$profile' 
            GROUP BY recently_profile.logged_profile_id 
            ORDER BY recently_profile.id DESC ");	
	        return $querymp->result();
        }else{
            return false;
        }
    }
    
     function get_recently_profile($args=array()){
         
            $limit = $offset ="";
            if(!empty($args['limit']) && $args['limit']!=""){
    		  $limit =$args['limit'];
    		}
    		if($args['offset']!=""){
    		  $offset = $args['offset'];
    		}
        
        if($args['profile']!=""){
            $profile = $args['profile'];
            $user_id = $args['user_id'];
            $show_gender = $args['gender'];
                
 $this->db->select('user_register.id,user_register.verified,user_register.created_user,user_register.first_name,user_register.profile,user_register.caste,user_register.dob,user_register.height,user_register.martial_status,user_register.status,contact_info.perm_city,profile_images.file_name,education_work.occup,education_work.education_field,memberships.payment_mode,interest.sent,favourites.profile_id as favourite' );
$this->db->from('recently_viewed_profiles');
$this->db->join('user_register', 'user_register.profile = recently_viewed_profiles.viewed_profile_id','left');

$this->db->join('memberships', 'memberships.member_profile_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('education_work', 'education_work.reg_profil_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('contact_info', 'contact_info.reg_profil_id = recently_viewed_profiles.viewed_profile_id','left');
$this->db->join('profile_images', 'profile_images.reg_id = user_register.id','left');
$this->db->join('favourites', 'favourites.profile_id = user_register.id AND favourites.user_logged_id = '.$user_id, 'left');

$this->db->join('interest', 'interest.profile_id = user_register.id AND interest.logged_user_id = '.$user_id, 'left');
$this->db->where('logged_profile_id',$profile ); 
//$this->db->where('viewed_profile_id','user_register.profile' );
$this->db->where('user_register.gender', $show_gender); 
$this->db->where('user_register.status', 1); 


$this->db->limit($limit, $offset);
$this->db->order_by('recently_viewed_profiles.last_view_profile','desc'); 
$this->db->group_by("recently_viewed_profiles.viewed_profile_id");
//  if ($offset == 0) {
//         $this->db->limit($limit);
//     } else if ($offset != 0) {
//         $this->db->limit($limit, $offset);
//     }
//$this->db->limit($limit,$offset);
$query11 = $this->db->get();

// echo $this->db->last_query();die;
 return $recent_view = $query11->result_array();	
	       // return $querymp->result();
        }else{
            return false;
        }
    }
    
    
    function get_match_of_the_day_data($args=array()){
        
        $limit = $offset = $cast =$user_marital_status= "";
        $gender = (!empty($args['gender']) && $args['gender']!="") ? $args['gender']  :"";
        if(!empty($args['caste']) && $args['caste']!=""){
         $cast =  'AND user_register.caste IN('.$args['caste'].')';
        }
        if(!empty($args['user_marital_status']) && $args['user_marital_status']!=""){
         $user_marital_status =  $args['user_marital_status'];
         $user_marital_status = "AND user_register.martial_status LIKE  '$user_marital_status'";
        }
       
       
       if(!empty($args['limit'])  && $args['limit']!=""){
           $limit = $args['limit'];
           $limit = "LIMIT $limit";
       }
       if(@$args['offset']!=""){
           $offset = $args['offset'];
           $offset = "OFFSET $offset";
       }
      
       
       $query1 = $this->db->query("SELECT user_register.id AS user_id,user_register.*,contact_info.*,education_work.*  
       FROM user_register AS user_register
       LEFT JOIN contact_info AS contact_info
       ON contact_info.reg_profil_id = user_register.profile 
       LEFT JOIN education_work AS education_work
       ON education_work.reg_profil_id = user_register.profile 
       WHERE user_register.gender = '$gender'  $cast $user_marital_status AND user_register.status = '1' 
       ORDER BY user_register.id DESC $limit $offset");
    //  echo $this->db->last_query();die;
        return $query1->result();
    }
    
    function get_personal_habits($args=array()){
        
        if($args['reg_id']!=""){
        
            $this->db->select("*");
    		$this->db->from('personal_habits');  
    		$this->db->where('reg_id',$args['reg_id'] ); 
    		$querypph = $this->db->get();
    		return $querypph->result();
        }else{
            return false;
        }
    }
    
    function get_family_info($args=array()){
        
        if($args['reg_id']!=""){
            $this->db->select("*");
    		$this->db->from('family_information');  
    		$this->db->where('reg_id',$args['reg_id'] ); 
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
     function get_education_details($args=array()){
        
        if($args['reg_id']!=""){
            $this->db->select("*");
    		$this->db->from('education_work');  
    		$this->db->where('reg_id',$args['reg_id'] ); 
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    function get_horoscope_details($args=array()){
        
        if($args['reg_id']!=""){
            $this->db->select("*");
    		$this->db->from('horoscope_details');  
    		$this->db->where('reg_id',$args['reg_id'] ); 
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
     function get_partner_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
    		$this->db->from('partner_expection');  
    		$this->db->where('reg_profil_id',$args['profile'] ); 
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
      function get_edit_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            
    		if($args['edit_form_name']  == 'basic_info'){
    		    
            $this->db->from('user_register');  
    		$this->db->where('profile ',$args['profile'] ); 
           
           }else if($args['edit_form_name']  == 'family_info'){
               
            $this->db->from('family_information');  
    		$this->db->where('reg_profil_id ',$args['profile'] ); 
               
           }else if($args['edit_form_name']  == 'education_info'){
               $this->db->from('education_work ');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
    		
           }else if($args['edit_form_name']  == 'horoscope_info'){
               $this->db->from('horoscope_details');  
    		$this->db->where('reg_profil_id ',$args['profile'] ); 
    		
           }else if($args['edit_form_name']  == 'partner_expectation_info'){
               $this->db->from('partner_expection');  
    		$this->db->where('reg_profil_id ',$args['profile'] ); 
    		
           }else if($args['edit_form_name']  == 'user_profile_photo'){
              $this->db->from('profile_images');  
    		$this->db->where('reg_id ',$args['row_id'] ); 
           }
           
           
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    function get_basic_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            	    
            $this->db->from('user_register');  
    		$this->db->where('profile ',$args['profile'] );
            
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    function get_family_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            $this->db->from('family_information');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    function get_contact_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            	    
            $this->db->from('contact_info');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
           
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    
    function get_habbit_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            	    
            $this->db->from('personal_habits');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
           
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
     function get_education_work_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            	    
            $this->db->from('education_work');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
           
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    function get_horoscope_info_data($args=array()){
        
        if($args['profile']!=""){
            $this->db->select("*");
            	    
            $this->db->from('horoscope_details');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
           
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
     function get_partner_expectation_info_data($args=array()){
         
         
            $limit = $offset = "";
        
             if(!empty($args['limit']) && $args['limit']!=""){
    		  $limit =$args['limit'];
    		}
    		if($args['offset']!=""){
    		  $offset = $args['offset'];
    		}
    		

        
        if($args['profile']!=""){
            
            $this->db->select("*");
            $this->db->from('partner_expection');  
    		$this->db->where('reg_profil_id ',$args['profile'] );
             $this->db->limit($limit, $offset);
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
     function get_user_profile_photo_info_data($args=array()){
        
        if($args['row_id']!=""){
            $this->db->select("*");
            	    
            $this->db->from('profile_images');  
    		$this->db->where('reg_id ',$args['row_id'] );
    		$this->db->order_by('id ','desc');
            $this->db->limit('1' );
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
        
    }
    
    function get_membership_data($args=array()){
        
        if($args['payment_mode']!=""){
            $this->db->select("*");
    		$this->db->from('memberships');  
    		$this->db->where('payment_mode',$args['payment_mode'] ); 
    		$this->db->order_by('id','DESC' );
    		$query1 = $this->db->get();
    		return $query1->result();
        }else{
            return false;
        }
    }
    
    
    
    // function get_new_macthed_data($args=array()){
        
   
    //     $limit = $condition = $like = "";
        
    //     if(!empty($args['gender'])){
    //       $condition.=" AND user_register.gender = '".$args['gender']."' ";
    //     }
    //     if(!empty($args['marital_status'])){
    //       $condition.=" AND user_register.martial_status = '".$args['marital_status']."' ";
    //     }
    //     if(!empty($args['caste'])){
    //       $condition.=" AND user_register.caste IN (".$args['caste'].") ";
    //     }
    //     if(!empty($args['age_from']) && !empty($args['age_to'])){
    //         $age_from = $args['age_from'];$age_to = $args['age_to'];
    //         $condition.= "AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >='$age_from' AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= '$age_to'";
    //     }
     
        
    //     if(!empty($args['height_from']) && !empty($args['height_to'])){
    //       $condition.= " AND (user_register.height >= '".$args['height_from']."' AND user_register.height <= '".$args['height_to']."')";
    //     }
        
    //     // if(!empty($args['primary_education'])){
    //     //   $condition.=" OR education_work.primary_edu = '".$args['primary_education']."' ";
    //     // }
    //     // if(!empty($args['highest_education'])){
    //     //   $condition.=" OR education_work.highest_education = '".$args['highest_education']."' ";
    //     // }
    //     // if(!empty($args['education_field'])){
    //     //   $condition.=" OR education_work.education_field = '".$args['education_field']."' ";
    //     // }
        
        
    //     // if(!empty($args['occupation'])){
    //     //   $condition.=" OR education_work.occup = '".$args['occupation']."' ";
    //     // }
        
    //     // if(!empty($args['diet'])){
    //     //   $condition.=" OR personal_habits.diet = '".$args['diet']."' ";
    //     // }
    //     // if(!empty($args['smooking'])){
    //     //   $condition.=" AND personal_habits.smooking = '".$args['smooking']."' ";
    //     // }
    //     // if(!empty($args['drinking'])){
    //     //   $condition.=" OR personal_habits.drinking = '".$args['drinking']."' ";
    //     // }
    //     // if(!empty($args['looking_for'])){
    //     //   $condition.=" AND personal_habits.looking_for = '".$args['looking_for']."' ";
    //     // }
        
        

    //     $querys = "SELECT user_register.*,education_work.*,personal_habits.*

    //                                 FROM user_register AS user_register

    //                                 LEFT JOIN education_work AS education_work

    //                                 ON(user_register.profile=education_work.reg_profil_id)
                                    
    //                                 LEFT JOIN personal_habits AS personal_habits

    //                                 ON(user_register.profile=personal_habits.reg_profil_id)

    //                                 WHERE user_register.status=1 $condition  ";
        
            

    //     $result = $this->db->query($querys);
        
    //     echo $this->db->last_query();die;

    //     return ($result) ? $result->result() : false; 

       

    // }

    
}