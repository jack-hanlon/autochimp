<?php session_start();
/*************************************
F_name: consent.php
Version: 1.0.1
F_type: Main Functionality file
Author: Jack Hanlon
Summary: Performs the http requests and functionality of AutoChimp - Subscribe
/*************************************/



//FUNCTIONS
/******************************************************************************************/
//Returns todays date and time
/**************************************************/
function get_date_time(){
	date_default_timezone_set('America/Vancouver');
	$dat = date('l F jS Y');
	return $dat;
}
//Pretty prints an array for debugging purposes
/**************************************************/
function pretty_print_r($arr) {
   echo "<pre>"; print_r($arr); echo "</pre>";
}
/******************************************************************************************/




//MAIN
/******************************************************************************************/
$url_redirect = $settings['redirect'];
$email = "";
if (!empty($_GET['email'])) {
    $email = filter_input(INPUT_POST | INPUT_GET, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
}



$dat = get_date_time();
$consent = 'Express';
if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
	try {
		//MailChimp API credentials
		$apiKey = $settings['key'];
		$listID = $settings['listID'];

		//MailChimp API URL
		$memberID = md5(strtolower($email));
		$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
		$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberID;

		//Member info
		$json = json_encode([
			'email_address' => $email,
			'status'        => 'subscribed',
			'merge_fields'  => [
				//'FNAME'     => $fname,
				//'LNAME'     => $lname,
				'CONSENT' => $consent,
				'CONSTDATE' => $dat

			]
		]);

		//Send a HTTP POST request with curl (webhook)
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == 200){
			//Put request approved... member subscribed with Express consent
			header('Location:https://'.$url_redirect);
		}else if($httpCode != 200){
			//A code based error has occured
			header('location:api_error.php');
		}
	} catch(Exception $e) {
		//Some weird unexpected error has occured
		header('location:some_went_wrong.php');
	}
}else{
	//The user entered an invalid email address
	header('location:email_invalid.php');
}
//Redirect to homepage
header('Location:https://'.$url_redirect);
/*************************************/
?>
