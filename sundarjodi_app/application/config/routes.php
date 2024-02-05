<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
$route['api/example/users/(:num)'] = 'api/example/users/id/$1'; // Example 4
$route['api/example/users/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'api/example/users/id/$1/format/$3$4'; // Example 8

$route['get_user_dt'] = 'api_3/authentication/get_user_dt';
$route['profile_percent'] = 'api_3/authentication/profile_percent';
$route['set_profile_photo'] = 'api_3/authentication/set_profile_photo';
$route['validity_pkg'] = 'api_3/authentication/validity_pkg';
$route['delete_user'] = 'api_3/authentication/delete_user';
$route['get_banners'] = 'api_3/authentication/get_banners';
$route['return_biodata'] = 'api_3/authentication/return_biodata';
$route['what_looking_for'] = 'api_3/authentication/what_looking_for';
$route['profile_count'] = 'api_3/authentication/profile_count';
$route['notification'] = 'api_3/authentication/notification';
$route['get_occupation'] = 'api_3/authentication/get_occupation';
$route['payment_success'] = 'api_3/authentication/payment_success';
$route['interestsent_pending_listing'] = 'api_3/authentication/interestsent_pending_listing';
$route['interestsent_accepted_listing'] = 'api_3/authentication/interestsent_accepted_listing';

$route['login'] = 'api_3/authentication/login';
$route['get_education_field'] = 'api_3/authentication/get_education_field';

// --------------------------------Forget password-----------------------

$route['forget_password'] = 'api_3/authentication/forget_password';
$route['verify_otp'] = 'api_3/authentication/verify_otp';
$route['new_password'] = 'api_3/authentication/new_password';
$route['resend_otp'] = 'api_3/authentication/resend_otp';

// // -----------------------Comman API----------------------------

$route['get_education'] = 'api_3/authentication/get_education';
$route['get_rashi'] = 'api_3/authentication/get_rashi';
$route['get_nakshtra'] = 'api_3/authentication/get_nakshtra';
$route['get_height'] = 'api_3/authentication/get_height';
$route['get_annual_income'] = 'api_3/authentication/get_annual_income';
$route['get_mother_tongue'] = 'api_3/authentication/get_mother_tongue';
$route['get_ages'] = 'api_3/authentication/get_ages';
$route['get_primary_education'] = 'api_3/authentication/get_primary_education';
$route['get_highest_education'] = 'api_3/authentication/get_highest_education';
// $route['get_rashi'] = 'api_3/authentication/get_rashi';
$route['get_caste'] = 'api_3/authentication/get_caste';
$route['get_country'] = 'api_3/authentication/get_country';
$route['get_state'] = 'api_3/authentication/get_state';
$route['get_city'] = 'api_3/authentication/get_city';
$route['get_live_city'] = 'api_3/authentication/get_live_city';

// // --------------------------Registration------------------------------

$route['registration'] = 'api_3/authentication/registration';
$route['add_basic_info'] = 'api_3/authentication/add_basic_info';
$route['add_family_info'] = 'api_3/authentication/add_family_info';
$route['add_education'] = 'api_3/authentication/add_education';
$route['add_horoscope'] = 'api_3/authentication/add_horoscope';
$route['add_partner_expectation'] = 'api_3/authentication/add_partner_expectation';
$route['add_profile_photo'] = 'api_3/authentication/add_profile_photo';
$route['upload_document'] = 'api_3/authentication/upload_document';

// //--------------------------------New matches--------------------------------

$route['new_matches'] = 'api_3/authentication/new_matches';
$route['view_profile'] = 'api_3/authentication/view_profile';
$route['get_user_data'] = 'api_3/authentication/get_user_data';
$route['get_partner_data'] = 'api_3/authentication/get_partner_data';
$route['view_profiles_by'] = 'api_3/authentication/view_profiles_by';
$route['recently_viewed_profiles'] = 'api_3/authentication/recently_viewed_profiles';
$route['recent_visitor'] = 'api_3/authentication/recent_visitor';
$route['match_of_the_day'] = 'api_3/authentication/match_of_the_day';
$route['premium_profiles'] = 'api_3/authentication/premium_profiles';
$route['favourite_unfavorite'] = 'api_3/authentication/favourite_unfavorite';
$route['user_interest'] = 'api_3/authentication/user_interest';
$route['education_wise_profile'] = 'api_3/authentication/education_wise_profile';

$route['logout'] = 'api_3/authentication/logout';

$route['received_interestpending_listing'] = 'api_3/authentication/received_interestpending_listing';
$route['received_accepted_listing'] = 'api_3/authentication/received_accepted_listing';
$route['interestsent_declined_listing'] = 'api_3/authentication/interestsent_declined_listing';
$route['received_declined_listing'] = 'api_3/authentication/received_declined_listing';
$route['received_archive_listing'] = 'api_3/authentication/received_archive_listing';
$route['sendinterest_pending_listing'] = 'api_3/authentication/sendinterest_pending_listing';
$route['favourite_profiles_listing'] = 'api_3/authentication/favourite_profiles_listing';
$route['search_by_id'] = 'api_3/authentication/search_by_id';
$route['search_by_caste'] = 'api_3/authentication/search_by_caste';
$route['quick_search'] = 'api_3/authentication/quick_search';
$route['do_you_want_to_search'] = 'api_3/authentication/do_you_want_to_search';
$route['saved_search'] = 'api_3/authentication/saved_search';
$route['saved_search_view_action'] = 'api_3/authentication/saved_search_view_action';
$route['accept_interest'] = 'api_3/authentication/accept_interest';
$route['reject_interest'] = 'api_3/authentication/reject_interest';
$route['timeago'] = 'api_3/authentication/timeago';
$route['change_password'] = 'api_3/authentication/change_password';
$route['send_reminder'] = 'api_3/authentication/send_reminder';
// ....PENDING API AS PER DISCUSS WITH NIKHIL
// $route['archive_listing'] = 'api_3/authentication/archive_listing';

//-------dashboard search by profile location profession,

$route['profession_wise_profile'] = 'api_3/authentication/profession_wise_profile';
$route['location_wise_profile'] = 'api_3/authentication/location_wise_profile';

// ---------UPDATE User details--------------------------

$route['update_basic_info'] = 'api_3/authentication/update_basic_info';

// -------------------------------------------------------------------




$route['user/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'api/authentication/user/id/$1/format/$3$4';