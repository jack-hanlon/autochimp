# Autochimp
MailChimp API Automation for CASL Consent Legislation 

Canadian GDPR Equivalent Laws

This Package contains two individual Functionalities.

Developed by <a href="https://github.com/jack-hanlon">Jack Hanlon</a>

<h2>Automate</h2>
Automate is an autochimp feature which scans through a MASTER list on Mailchimp and purges members whose CASL Consent has expired. It does this automatically via Cron Jobs. Can handle lists of any size. Has been ran on client lists of 25,000+ people.
<h2>Subscribe</h2>
Subscribe is the second autochimp feature which gives you a link that you can put in your MC Campaign emails, to set the users consent to Express and protect them from the operations completed by Automate.

<h2>Get Started</h2>
  
* Git clone the repo into autochimp folder
 
* Modify settings_template.php for Automate & Subcribe Packages based on your information found in MailChimp. Fill out items such as your MC  List ID, API Key and Data Center.

* Use http://www.miraclesalad.com/webtools/md5.php to generate a Security key to protect your script from being activated without your authorization. Insert this security key into the key field in your $settings[] array. 

* Once all of your settings are complete. Upload all of your files to your server of choice.

* Login to your Servers cpanel via http://yourwebsite.com:2082 with the same login & pwd as your FTP Client.

* Go to Cron Jobs and follow the layout of the code to add a new cron job.

Should look something like this : 

/usr/local/bin/php /home/yourlogin/public_html/settings_template.php 1 your_security_key

The 1 tells the AutoChimp Script that you're running the script via Cron Jobs, and you must add the security key for the script to work.


<h1>Drop AutoChimp a Star!</h1>
<h2And Get AutoChimping!!!</h2>
