<?php

include_once 'razorpay/Razorpay.php';
use Razorpay\Api\Api;

$api = new Api('rzp_live_wTmRY0pkJFhycc', 'eYE8QqawNyxQmtNfU4iR8TpJ');

 $payment_id =   $this->input->post('payment_id'); 
 $profile =   $this->input->post('profile'); 
 

$payment = $api->payment->fetch($payment_id);

  $amount = $payment->amount;
 $status = $payment->status;
 $email = $payment->email;
 $contact = $payment->contact;
 $method = $payment->method;
//$payment->capture(array('amount' => $amount, 'currency' => 'INR'));
 
$this->db->select("*");
$this->db->from('user_register');  
$this->db->where('profile',$profile); 
$query1 = $this->db->get();
 $this->db->limit(1);
if(!empty($query1)){
foreach($query1->result() as $row1)
{
  	$profile = $row1->profile;
  	$userid = $row1->id;
}}
$this->db->select("*");
$this->db->from('memberships');  
$this->db->where('member_profile_id',$profile); 
$query1 = $this->db->get();
 $this->db->limit(1);
if(!empty($query1)){
foreach($query1->result() as $row1)
{
  	$mem_id = $row1->id;
}}

        if($mem_id){   
          $member_profile_id = $profile;
            $amount11 = $amount/100;
       
			        $this->db->select("*");
			        $this->db->from('packages_membership'); 
			        $this->db->where('discounted_price',$amount11); 
			        $query1 = $this->db->get();
			        if(!empty($query1)){
			         foreach($query1->result() as $row1)
  				    {
  				    	$package_name = $row1->package_name;
						$actual_amount = $row1->actual_amount;
						$discount = $row1->discount;
						$discounted_price = $row1->discounted_price;
						$package_duration_days = $row1->package_duration_days;
						$package_duration_year = $row1->package_duration_year;
						$package_duration_month = $row1->package_duration_month;
						$alloted_contacts = $row1->alloted_contacts;
						$status = $row1->status;
						$sequence_order = $row1->sequence_order;
						
  				    }}
            
           
            
            $total_profiles_alloted = $alloted_contacts;
          // $remaining_profiles = '143';
            $package_id = $discounted_price;
            
            $package_validity = date('Y-m-d', strtotime('+'.$package_duration_year.' years '.$package_duration_month.' months '.$package_duration_days.' days'));
            
            
            $payment_mode = 'Paid';
            $payment_status = 'Success';
            $status = '1';
            $created_date = date('Y-m-d H:i:s');
            
            
            $this->db->set('member_profile_id', $member_profile_id);
            $this->db->set('total_profiles_alloted', $total_profiles_alloted);
            $this->db->set('remaining_profiles', '0');
            $this->db->set('package_id', $package_id);
            $this->db->set('package_validity', $package_validity);
            $this->db->set('payment_mode', $payment_mode);
            $this->db->set('payment_status', $payment_status);
            $this->db->set('payment_id', $payment_id);
            $this->db->set('status', $status);
            $this->db->set('call_check', '1');
            $this->db->set('created_date', $created_date);
    
             $this->db->where('id', $mem_id);
            $result=$this->db->update('memberships');
          // $this->db->insert('memberships', $data_cont);
           
            $data = array(
           
            'price' => $package_id,
            'from_date' => $created_date,
            'to_date' => $package_validity,
            'total_alloted_contact' => $total_profiles_alloted,
            'total_viewed_contact' => 0,
            'profile_id' => $member_profile_id,
            'payment_mode' => 'Pay_Gateway',
            'payment_id' => $payment_id,
             );
            $partner_program = $this->db->insert('payment_history', $data);
           
            $this->db->set('status', '1');
            $this->db->where('id', $userid);
            $result1=$this->db->update('user_register');
            
            echo 'Payment done';
            //  $this->response([
            //         'status' => TRUE,
            //         'message' => 'Done Successfully',
            //         'amount' => $amount11,
            //         'payment_id' => $payment_id,
            //         'contact' => $contact,
            //         'method' => $method,
                    
            //     ], REST_Controller::HTTP_OK);
                
}
?>