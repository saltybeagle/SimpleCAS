<?php
ini_set('display_errors', true);
set_include_path(dirname(dirname(__DIR__)).'/src'.PATH_SEPARATOR.dirname(dirname(__DIR__)).'/vendor/php');
require_once 'SimpleCAS/Autoload.php';
require_once 'HTTP/Request2.php';

$options = array('hostname' =>'login.unl.edu',
                 'port'     => 443,
                 'uri'      => 'cas');
$protocol = new SimpleCAS_Protocol_Version2($options);

$protocol->getRequest()->setConfig('ssl_verify_peer', false);

$client = SimpleCAS::client($protocol);

if (isset($_GET['logout'])) {
	$client->logout();
}

$client->renewAuthentication();

if ($client->isAuthenticated()) {
    echo '<h1>Authentication Successful!</h1>';
    echo '<p>Welcome to the renewed auth page.</p>';
}
?>
<a href="?logout">Logout</a>