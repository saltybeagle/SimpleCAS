<?php
ini_set('display_errors', true);
require_once __DIR__ . '/../../vendor/autoload.php';

$options = array('hostname' =>'login.unl.edu',
                 'port'     => 443,
                 'uri'      => 'cas');
$protocol = new SimpleCAS_Protocol_Version2($options);

$protocol->getRequest()->setConfig('ssl_verify_peer', false);

$client = SimpleCAS::client($protocol);
$client->handleSingleLogOut();
$client->forceAuthentication();

if (isset($_GET['logout'])) {
	$client->logout();
}

if ($client->isAuthenticated()) {
    echo '<h1>Authentication Successful!</h1>';
    echo '<p>The user\'s login is '.$client->getUsername().'</p>';
}
?>
<a href="simple_renew.php">Super Sensitive Page</a>
<a href="?logout">Logout</a>