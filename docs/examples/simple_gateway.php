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
$client->gatewayAuthentication();
?>
<?php if ($client->isAuthenticated()): ?>
    <?php if (isset($_GET['logout'])) {
        $client->logout();
    } ?>
    <h1>Authentication Successful!</h1>
    <p>The user's login is <?php echo $client->getUsername() ?></p>
    <p>View the <a href="simple.php">authenticated home page</a></p>
    <a href="?logout">Logout</a>
<?php else: ?>
    <h1>NOT Authenticated</h1>
    <p><a href="simple.php">Login</a> to see more</p>
<?php endif; ?>
