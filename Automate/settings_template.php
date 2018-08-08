<?php
/***********************************************************************
filename: settings_template.php
version: v 1.0.0
filetype: MAIN
Author: Jack Hanlon
Summary: Contains all template MailChimp data to interface with revoke.inc.php (Main file)
***********************************************************************/

require_once('func.php');
$logs = array();
$settings = array(
      'cron' => false,
      'security_key' => 'varpassedinurl',
      'client' => 'clientname',
      'login' => 'client_login',
      'key' => 'apikey_mailchimp',
      'url' => 'https://datacenter.api.mailchimp.com/3.0/',
      'listID' => 'listID',
      'listSize' => 50,
      'DEV_MODE' => true,
      'ACTION_MODE' => false,
      'END_TASK' => false,
      'HAS_ORDERS' => true,
      'ORDERS_FIELD_NAME' => 'ORDERS',
      'PRELIM_MODE' => true
);

//if argv[1] == 1 then a cron script is running
if($argv[1] == 1) {
  $settings['cron'] = true;
}

$logs[] = '--- SETTINGS ---';
$logs[] =  implode("\n",$settings); //copy settings into logs
$apiClient = $settings['client'];
//echo 'api client:' . $apiClient;
$logs[] =  '--- MESSAGES ---';
if(
    ($_GET['key'] && $_GET['key'] == $settings['security_key']) || //for browser only
    ($argv[2] && $argv[2] == $settings['security_key']) //for cron job only
) {
  $proceed = true;
} else {
   $logs[] = "\n" . 'Incorrect key' . "\n";
}


if($proceed) {
  include 'revoke.inc.php';

} else {
    $logs[] = "\n" . 'did not run revoke.inc.php'. "\n";
    create_error_log($logs,'/home/path/to/public_html/',$apiClient);
    die;
}




?>
