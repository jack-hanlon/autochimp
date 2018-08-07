<?php
/***********************************************************************
filename: func.php
version: v 1.0.0
filetype: FUNCTIONS
Author: Jack Hanlon
Summary: Contains all MailChimp functions
***********************************************************************/

//Get today's Date and Time
/****************************************************/
function get_date_time($log){
	/**returns the Date and Time
		Date tags:
		h: 12hr format H: 24hr format
		i: minutes s: seconds
		u: microseconds a: lowercase am or pm
		l: Full text for the day F: full text for the month
		j: day of the month S: suffix for the day (st, nd ,rd , th)
		Y: 4 digit year
	**/
	date_default_timezone_set('America/Vancouver');
  if($log == 1) $dat = date('h:i:s a l jS F Y');
  else{$dat = date('d m Y');}

	return $dat;
}
//Get date and time 6 months ago
/****************************************************/
function get_six_ago($two_year_status){
	global $DEV_MODE;
	global $ACTION_MODE;
	date_default_timezone_set('America/Vancouver');
	$date = date("Y-m-d",strtotime("-6 months"));

	if($DEV_MODE) $date = date("Y-m-d",strtotime("-6 months"));

	if($ACTION_MODE) $date = date("Y-m-d",strtotime("10 months"));

	if($two_year_status == true) $date = date("Y-m-d",strtotime("-2 years"));

	$ret_date = $date . 'T00:00:00+00:00';
	return $ret_date;
}
//Pretty Print for Debugging
/****************************************************/
function pretty_print_r($arr) {
   echo "<pre>";
   print_r($arr);
   echo "</pre>";
}
//API Curl Request Function
/****************************************************/
function mc_request( $api, $type, $target, $data = false ){
	$ch = curl_init( $api['url'] . $target );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array
	(
		'Content-Type: application/json',
		'Authorization: ' . $api['login'] . ' ' . $api['key']
//		'X-HTTP-Method-Override: ' . $type,
	) );

//	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $type );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, 'YOUR-USER-AGENT' );

	if( $data )
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

	$response = curl_exec( $ch );
	curl_close( $ch );

	return $response;
}

//Set the target to be used for the GET request
/****************************************************/
function set_target($type,$apiListID,$apiListSize,$date_to_append,$offset,$segmentID){
	 if($type == 'six_month_revoke'){
						$target = 'lists/' . $apiListID . '/members?count='. $apiListSize .'&before_timestamp_opt=' . $date_to_append .'&status=subscribed';
			}else if($type == 'prelim_implied'){
						$target = 'lists/' . $apiListID . '/segments' . '/' . $segmentID .'/members?count=' . $apiListSize . '&offset=' . $offset ;
			}else if($type == 'prelim_express'){
						$target = 'lists/' . $apiListID . '/members?count=' . $apiListSize . '&status=subscribed' . '&offset=' . $offset;
			}
			return $target;
}
/****************************************************/
//Writes a log file with an array of messages
/****************************************************/
function create_log_file($log_info, $local_path_prefix,$apiCli) {
	$file = fopen($local_path_prefix . 'logfiles/logfile.' .$apiCli.".txt", "w" ) or die("Unable to open file!");
	for($i = 0 ; $i < count($log_info); $i++){
		fwrite($file,$log_info[$i]);
	}
	fclose($file);
}
//Write a new log file if log files already exist
/****************************************************/
function create_num_log_file($log_info,$local_path_prefix,$counter,$apiCli){
	$file = fopen($local_path_prefix. 'logfiles/logfile.'.$apiCli . "." . $counter . ".txt","w") or die("Unable to open file!");
	for($i = 0; $i < count($log_info); $i++ ){
		fwrite($file,$log_info[$i]);
	}fclose($file);
}
//Writes an error_log file for debugging
/***************************************************/
function create_error_log($logs,$local_path_prefix,$apiClient){

	$error_log = fopen($local_path_prefix . 'logfiles/error_log.'. $apiClient .'.txt', 'a') or die("Unable to open file!");
	fwrite($error_log,"\n" . '[' . get_date_time(1) . ']' . "\n");
	for($i = 0 ; $i < count($logs); $i++){
		fwrite($error_log,$logs[$i]);
	}
	fclose($file);
}
