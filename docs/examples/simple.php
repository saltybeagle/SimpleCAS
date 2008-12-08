<?php
ini_set('display_errors', true);
chdir(dirname(dirname(dirname(__FILE__))));

require_once 'SimpleCAS.php';
require_once 'SimpleCAS/Server.php';
require_once 'SimpleCAS/SingleSignOut.php';
require_once 'SimpleCAS/ProxyGranting.php';
require_once 'SimpleCAS/ProxyGranting/Storage.php';
require_once 'SimpleCAS/ProxyGranting/Storage/File.php';
require_once 'SimpleCAS/Server/Version1.php';
require_once 'SimpleCAS/Server/Version2.php';
require_once 'SimpleCAS/Server/Version2/ValidationResponse.php';
require_once 'HTTP/Request.php';
require_once 'HTTP/Request/Uri.php';
require_once 'HTTP/Request/Headers.php';
require_once 'HTTP/Request/Response.php';
require_once 'HTTP/Request/Adapter.php';
require_once 'HTTP/Request/Adapter/Phpstream.php';

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