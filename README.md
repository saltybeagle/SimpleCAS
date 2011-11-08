# SimpleCAS PHP 5 CAS Client

This is a PHP 5 client library for [JA-SIG Central Authentication Service (CAS)](http://www.ja-sig.org/products/cas/).

Compatible with servers using version 1 or 2 of the CAS protocol.

Install with:

```
pear channel-discover simplecas.googlecode.com/svn
pear install simplecas/SimpleCAS-alpha
```

Manually install by downloading the latest release and extracting the files,
along with [HTTP_Request2](http://pear.php.net/package/HTTP_Request2/) from PEAR.

Quick Example:

```php
<?php

require_once 'SimpleCAS/Autoload.php';
require_once 'HTTP/Request2.php';

$options = array('hostname' => 'login.unl.edu',
                 'port'     => 443,
                 'uri'      => 'cas');
$protocol = new SimpleCAS_Protocol_Version2($options);

$client = SimpleCAS::client($protocol);
$client->forceAuthentication();

if (isset($_GET['logout'])) {
    $client->logout();
}

if ($client->isAuthenticated()) {
    echo '<h1>Authentication Successful!</h1>
          <p>The user\'s login is '.$client->getUsername().'</p>
          <a href="?logout">Logout</a>';
}
?>
```
