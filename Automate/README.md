This folder is a set of php scripts that allow for automatic revoking and unsubscription of users from a clients master list. revoke.inc.php runs an operation where it makes a GET request to the MailChimp API and filters for the required users. It then creates a batch which it POST's back to the master list to update Revoked and
Unsubscribed members. Per client create a file with an array of information (follow the format of 'settings_template.php') under the clients name, and include the revoke.inc.php in the
script. This will allow operations to be done on that new client.
