<?php
//session_start();
/*****************************************************************************************************
Fname  : revoke.inc.php
Package: AutoChimp
filetype: INCLUDE
Sub.   : Part 2 - automation
Version: 1.0.1
Author : Jack Hanlon
Summary: Action file for auto-unsubscribing expired, implied consent users.
******************************************************************************************************/

global $DEV_MODE;
global $ACTION_MODE;
global $END_TASK;
global $PRELIM_MODE;
//Dev mode sends no modifying post request so you can test on G3 or Sea to Sky gondola client list.
//Action Mode DOES write to mailchimp, used for debugging . DO NOT USE ON a Client list!
//No globals set will execute the true functionality of this script and will write to the clients MC list.
$DEV_MODE = $settings['DEV_MODE'];
$ACTION_MODE = $settings['ACTION_MODE'];
$END_TASK = $settings['END_TASK'];
$PRELIM_MODE = $settings['PRELIM_MODE'];

//Kills script if developer wants to stop it running but not kill cron jobs setup
if($END_TASK == true){
	die;
}

//MAIN
/*****************************************************************************************************/
//Initializing API info
$log_info = array();
//create array of api info
$api = array(
		'login' => $settings['login'],
		'key' => $settings['key'],
		'url' => $settings['url'],
    'listID' => $settings['listID'],
    'client' => $settings['client'],
		'listSize' => $settings['listSize']
		);
/*******************************************************************************************************/
//Some temporary variables
$apiListID = $api['listID'];
$apiListSize = $api['listSize'];
$apiCli = $api['client'];

//sets the local path prefix based on if script is run through cron or browser
$local_path_prefix = "";
if($settings['cron']) {
	$local_path_prefix = "/home/sandboxdevzone/public_html/";
}

//LOGFILE Creation and Iteration
/****************************************************************************/
//check if logfile.txt exists
$headers = get_headers('http://sandboxdevzone.vwclients.net/logfiles/logfile.'.$api['client'].'.txt', 1);
$file_found = stristr($headers[0], '200');

//if logfile.txt exists loop until logile#.txt doesnt exist
if($file_found == '200 OK'){

    $counter = 2;
    while($file_found == '200 OK'){
      $headers = get_headers('http://sandboxdevzone.vwclients.net/logfiles/logfile.'.$api['client']."." . $counter . '.txt', 1);
      $file_found = stristr($headers[0],'200');
      if($file_found != '200 OK'){
        continue;
      }
      $counter = $counter + 1;

    }

    //set the log_type bit
		$log_type = 1;
}else{
		//set the log_type bit
		$log_type  = 0;
}
//Appends date to front of each new LOGFILE
$log_info[] = get_date_time(1) . "\n";
/*******************************************************************************************************/
//Set target for get request
$target = set_target('six_month_revoke',$apiListID,$apiListSize,get_six_ago(false),'','');
//fill if doing a post/put request N.B.: post/put no longer supported here
$data = "";
//output of http query
$result = mc_request($api,'GET',$target,$data);

//ERROR LOG FILE CREATED TO CATCH EMPTY GET REQUEST
/**************************************************/
if(empty($result)) {
		$logs[] = "\n the GET request was empty ... script terminated";
		create_error_log($logs,$local_path_prefix,$apiCli);
		die("the GET request was empty so the script terminated");

}

$decoded = json_decode($result);
//error checking to ensure that we've got what we need to proceed
if(empty($decoded->members)) {
	//if there are no members in decoded_two_set output a log
		if(!empty($decoded->status) && $decoded->status == 404){
			$logs[] = "\n the list has no operations to be completed";
			create_error_log($logs,$local_path_prefix,$apiCli);
		}
		//kill the script
		die('no operations needed to be completed!');
}

//counts number of member operations completed
$count = 0;
$count_2 = 0;
//A loop to do the main check on whether users have expired their implied consent
foreach($decoded->members as $member) {
	$email = $member->email_address;
	if($settings['HAS_ORDERS'] == true){
	//only add to batch if Consent field is implied (BLANK)
	if($member->merge_fields->CONSENT != 'Express' &&
	 	$member->merge_fields->CONSENT != 'Revoked' &&
	 	($member->merge_fields->{$settings['ORDERS_FIELD_NAME']} == '0' ||
	  $member->merge_fields->{$settings['ORDERS_FIELD_NAME']} == '')){
			$count = $count + 1;
			$log_info[] = "6 month --- email: " . $member->email_address . "\n";
			if($DEV_MODE == true){
					$actions = "CASL Consent would be set to Revoked & Members Unsubscribed. Members reached end of 6 month implied consent";
			}else{
  		$actions ="User CASL Consent Revoked, Unsubscribed. Members reached end of 6 month implied consent";
		  }

			$consent = 'Revoked';
	}
		/***********************************************************/
		if(
			$member->merge_fields->CONSENT != 'Express' &&
			$member->merge_fields->CONSENT != 'Revoked' &&
			$member->merge_fields->{$settings['ORDERS_FIELD_NAME']} != '0' &&
			$member->merge_fields->{$settings['ORDERS_FIELD_NAME']} != ''){
				$two_ago = strtotime('-2 years');
				$member_timestamp = $member->timestamp_opt;
				$member_timestamp = strtotime($member_timestamp);
				if($member_timestamp < $two_ago){
						$count_2 = $count_2 + 1;
						$log_info[] = "2 yr --- email: " . $member->email_address . "\n";

						if($DEV_MODE == true){
							$actions_2 = "CASL Consent would be set to revoked & Member Unsubscribed. Member reached end of 2 year implied consent";
						}else{
							$actions_2 = "User CASL Consent Revoked, Unsubscribed. Member reached end of 2 year implied consent";
						}
						$consent = 'Revoked';
				}
		}
	}else if($settings['HAS_ORDERS'] == false){
		//Client doesn't have eCommerce functionality and thus only has 6 month functionality
		if($member->merge_fields->CONSENT != 'Express' &&
		 	$member->merge_fields->CONSENT != 'Revoked'){
				$count = $count + 1;
				$log_info[] = "6 month --- email: " . $member->email_address . "\n";
				if($DEV_MODE == true){
						$actions = "CASL Consent would be set to Revoked & Members Unsubscribed. Members reached end of 6 month implied consent";
				}else{
	  		$actions ="User CASL Consent Revoked, Unsubscribed. Members reached end of 6 month implied consent";
			  }

				$consent = 'Revoked';
		}
	}


		if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){

			$listID = $api['listID'];
			$memberID = md5(strtolower($email));

			$batch_operations[] = array(
			  'method' => 'PUT',
			  'path' => 'lists/' . $listID . '/members/' . $memberID,
			  'body' => json_encode(
							array(
								'email_address' => $email,
								'status' => 'unsubscribed',
								'merge_fields' => array(
									'CONSENT' => $consent
								),
							)
						)
					);
				}
}

if(!empty($actions) || !empty($actions_2)){
	if(!empty($actions)){
		$log_info[] = "action taken: " . $actions . "\n";
		$log_info[] = "Number of 6 months revoked: " . $count . "\n";
		$log_info[] = "No actions taken for 2 year implied consent\n";
	}else if(!empty($actions_2)){
			$log_info[] = "action taken: " . $actions_2 . "\n";
			$log_info[] = "Number of 2 years revoked: " . $count_2 . "\n";
			$log_info[] = "No actions taken for 6 month implied consent\n";
	}
}
/*****************************************************************************************/
//Send it bro!
/*****************************************************************************************/
$batch_operations = array('operations' => $batch_operations);
$encoded = json_encode($batch_operations);


if(empty($batch_operations) || $DEV_MODE) {
	$log_info[] = "dev mode or no ops to process ... Done";
	if($log_type == 1){
		create_num_log_file($log_info,$local_path_prefix,$counter,$apiCli);
	}else if($log_type == 0){
		create_log_file($log_info,$local_path_prefix,$apiCli);
	}

	die;
}


/******************************************************
* Warning! this section sends the POST request.
* Uncommented testing of this section could cause content modification
******************************************************/

$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/batches';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

//Logfile closing remarks
$log_info[] = "Number of actions completed: " . $count;
$log_info[] = "httpCode: " . $httpCode . "\n";
if($log_type == 1){
	create_num_log_file($log_info,$local_path_prefix,$counter,$apiCli);
}else if($log_type == 0){
	create_log_file($log_info,$local_path_prefix,$apiCli);
}
?>
