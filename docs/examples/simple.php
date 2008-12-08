<?php
ini_set('display_errors', true);
chdir(dirname(dirname(dirname(__FILE__))));

require_once 'SimpleCAS/Autoload.php';
require_once 'HTTP/Request2.php';

$server = new SimpleCAS_Server_Version2('login.unl.edu', 443, 'cas');

$client = SimpleCAS::client($server);
$client->forceAuthentication();

if (isset($_GET['logout'])) {
	$client->logout();
}

if ($client->isAuthenticated()) {
    echo '<h1>Authentication Successful!</h1>';
    echo '<p>The user\'s login is '.$client->getUsername().'</p>';
}
?>
<a href="?logout">Logout</a>