<?php
/***********************************************************************
filename: g3.php
version: v 1.0.0
filetype: MAIN (settings)
Author: Jack Hanlon
Summary: Contains all data to interface with consent.php (Main file)
***********************************************************************/

$settings = array(
      'client' => 'client_name',
  		'login' => 'client_login',
      'key' => 'apikey-datacenter',
      'url' => 'https://datacenter.api.mailchimp.com/3.0/',
      'listID' => 'listID',
      'redirect' => 'www.your-onsuccess-redirect-page.com/thank-you'
);

include 'consent.inc.php';

?>
