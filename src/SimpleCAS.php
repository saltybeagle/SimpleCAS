<?php
/**
 * This is a CAS client authentication library for PHP 5.
 *
 * <code>
 * <?php
 * $protocol = new SimpleCAS_Protocol_Version2('login.unl.edu', 443, 'cas');
 * $client = SimpleCAS::client($protocol);
 * $client->forceAuthentication();
 *
 * if (isset($_GET['logout'])) {
 *     $client->logout();
 * }
 *
 * if ($client->isAuthenticated()) {
 *     echo '<h1>Authentication Successful!</h1>';
 *     echo '<p>The user\'s login is '.$client->getUsername().'</p>';
 *     echo '<a href="?logout">Logout</a>';
 * }
 * </code>
 *
 * PHP version 5
 *
 * @category  Authentication
 * @package   SimpleCAS
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 */
class SimpleCAS
{
    /**
     * Version of the CAS library.
     */
    const VERSION = '0.0.1';

    /**
     * Singleton CAS object
     *
     * @var SimpleCAS
     */
    static private $_instance;

    /**
     * (Optional) alternative service URL to return to after CAS authentication.
     *
     * @var string
     */
    static protected $url;

    /**
     * Is user authenticated?
     *
     * @var bool
     */
    private $_authenticated = false;

    /**
     * Should a redirect be done after ticket validation?
     *
     * @var bool
     */
    protected $validateRedirect = true;

    /**
     * Protocol for the server running the CAS service.
     *
     * @var SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * User's login name if authenticated.
     *
     * @var string
     */
    protected $username;

    /**
     * A CAS ticket from the request
     *
     * @var string
     */
    protected $_ticket;

    /**
     * The namespace to store internal session data in
     *
     * @var string
     */
    protected $_sessionNamespace = '__SIMPLECAS';

    /**
     * Singleton interface, returns SimpleCAS object.
     *
     * @param SimpleCAS $server
     *
     * @return SimpleCAS
     */
    static public function client(SimpleCAS_Protocol $protocol)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($protocol);
        } else {
            self::$_instance->protocol = $protocol;
        }

        return self::$_instance;
    }

    /**
     * Returns the current URL without CAS affecting parameters.
     *
     * @return string url
     */
    static public function getURL()
    {
        if (!empty(self::$url)) {
            return self::$url;
        }

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        if (empty($host)) {
            $host = $_SERVER['SERVER_NAME'];
            $port = $_SERVER['SERVER_PORT'];

            if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)) {
                $host .= ':' . $port;
            }
        }

        $url = $scheme . '://' . $host . $_SERVER['REQUEST_URI'];

        $replacements = array(
                '/\?logout/'        => '',
                '/&ticket=[^&]*/'   => '',
                '/\?ticket=[^&;]*/' => '?',
                '/\?%26/'           => '?',
                '/\?&/'             => '?',
                '/\?$/'             => ''
        );
        $url = preg_replace(array_keys($replacements), array_values($replacements), $url);

        return $url;
    }

    /**
     * Set an alternative return URL
     *
     * @param string $url alternative return URL
     */
    public static function setURL($url)
    {
        self::$url = $url;
    }

    /**
     * Send a header to redirect the client to another URL.
     *
     * @param string $url URL to redirect the client to.
     */
    public static function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    /**
     * Get the version of the CAS library
     *
     * @return string
     */
    static public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Construct a CAS client object.
     *
     * @param SimpleCAS_Protocol $protocol Protocol to use for authentication.
     */
    private function __construct(SimpleCAS_Protocol $protocol)
    {
        $this->protocol = $protocol;

        if (isset($_GET['ticket'])) {
            $this->setTicket($_GET['ticket']);
        }
    }

    /**
     * Handle any potential Single Log Out requests.
     */
    public function handleSingleLogOut()
    {
        if (!$session_map = $this->protocol->getSessionMap()) {
            return;
        }
        
        if ($slo_ticket = $session_map->validateLogoutRequest($_POST)) {
            $session_map->logout($slo_ticket);
            exit();
        }
    }

    /**
     * Returns the session namespace or the named session member
     *
     * @param string $name [optional] The name of the member to get
     * @return mixed
     */
    protected function & _getSession($name = null)
    {
        if (session_id() == '') {
            session_start();
        }

        if (!is_null($name)) {
            $value = null;
            if (isset($_SESSION[$this->_sessionNamespace][$name])) {
                $value = $_SESSION[$this->_sessionNamespace][$name];
            }
            return $value;
        }

        return $_SESSION[$this->_sessionNamespace];
    }

    /**
     * Returns the session namespace name (the offset into the session)
     *
     * @return string
     */
    public function getSessionNamespace()
    {
        return $this->_sessionNamespace;
    }

    /**
     * Returns the used protocol object
     *
     * @return SimpleCAS_Protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Gets the ticket to be checked for authentication
     *
     * @return string
     */
    public function getTicket()
    {
        if (!empty($this->_ticket)) {
            return $this->_ticket;
        }

        $ticket = $this->_getSession('TICKET');
        if (!is_array($ticket)) {
            return $ticket;
        }
        
        return null;
    }

    /**
     * Set the ticket to be checked for authentication
     *
     * @param string $ticket
     */
    public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
    }
    
    /**
     * Returns the extra attributes from Version 2 protocol validation
     *
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->_getSession('ATTRIBUTES');
    }

    /**
     * Set the flag controlling the after ticket validation redirect
     *
     * @param bool $value
     */
    public function setValidateRedirect($value)
    {
        $this->validateRedirect = (bool)$value;
    }

    /**
     * Checks a ticket to see if it is valid.
     *
     * If the CAS server verifies the ticket, a session is created and the user
     * is marked as authenticated.
     *
     * @param string $ticket Ticket from the CAS Server
     *
     * @return bool
     */
    protected function validateTicket($ticket)
    {
        if ($uid = $this->protocol->validateTicket($ticket, self::getURL())) {
            $this->setAuthenticated($uid);
            
            if ($this->protocol instanceof SimpleCAS_Protocol_Version2) {
                $session =& $this->_getSession();
                $session['ATTRIBUTES'] = $this->protocol->getAttributes();
            }

            if ($this->validateRedirect) {
                $this->redirect(self::getURL());
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Marks the current session as authenticated.
     *
     * @param string $uid User name returned by the CAS server.
     *
     * @return void
     */
    protected function setAuthenticated($uid)
    {
        $session =& $this->_getSession();
        $session['TICKET'] = $this->getTicket();
        $session['UID']    = $uid;
        if (isset($this->protocol->renew)) {
            $session['FROM_RENEW'] = true;
        }
        $this->_authenticated = true;
        
        if ($session_map = $this->protocol->getSessionMap()) {
            $session_map->set($this->getTicket(), session_id());
        }
    }

    /**
     * Initializes the protocol and session with the gateway option
     *
     * @return SimpleCAS
     */
    protected function _setupGateway()
    {
        $session =& $this->_getSession();
        $session['GATEWAY'] = $this->protocol->gateway = true;

        return $this;
    }

    /**
     * Initializes the protocol with the renew option
     *
     * @return SimpleCAS
     */
    protected function _setupRenew()
    {
        $this->protocol->renew = true;

        return $this;
    }

    /**
     * Clears session and protocol information for a gateway request and returns if
     * the previous request was from a gateway response.
     *
     * @return bool
     */
    protected function _cleanupGateway()
    {
        $session =& $this->_getSession();
        $wasGateway =  isset($session['GATEWAY']) ? (bool) $session['GATEWAY'] : false;
        unset($session['GATEWAY'], $this->protocol->gateway);

        return $wasGateway;
    }

    /**
     * Clears session and protocol information for a renew request and returns if
     * the previous request was from a renew response.
     *
     * @return boolean
     */
    protected function _cleanupRenew()
    {
        $session =& $this->_getSession();
        $fromRenew = isset($session['FROM_RENEW']) ? (bool) $session['FROM_RENEW'] : false;
        unset($session['FROM_RENEW'], $this->protocol->renew);

        return $fromRenew;
    }

    /**
     * Return the authenticated user's login name.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_getSession('UID');
    }

    /**
     * If client is not authenticated, this will redirecting to login and exit.
     *
     * Otherwise, return the CAS object.
     *
     * @return SimpleCAS
     */
    function forceAuthentication()
    {
        $this->_cleanupGateway();

        if (!$this->isAuthenticated()) {
            self::redirect($this->protocol->getLoginURL(self::getURL()));
        }

        return $this;
    }

    /**
     * Forces client to re-enter credentials at CAS login
     *
     * @return SimpleCAS
     */
    public function renewAuthentication()
    {
        $this->_cleanupGateway();
        $fromRenew = $this->_cleanupRenew();

        if (!$this->isAuthenticated(!$fromRenew)) {
            self::redirect($this->protocol->getLoginURL(self::getURL()));
        }

        return $this;
    }

    /**
     * If client is not authenticated, attempt a gateway (transparent) CAS login.
     *
     * @return SimpleCAS
     */
    public function gatewayAuthentication()
    {
        $wasGateway = $this->_cleanupGateway();
        if (!$wasGateway && !$this->isAuthenticated()) {
            $this->_setupGateway();
            self::redirect($this->protocol->getLoginURL(self::getURL()));
        }

        return $this;
    }

    /**
     * Check if this user has been authenticated or not.
     *
     * @param bool $forRenew Ensures the ticket came from a CAS renew request
     * @return bool
     */
    function isAuthenticated($forRenew = false)
    {
        if ($forRenew) {
            $this->_setupRenew();
            $this->_authenticated = false;
        } elseif (!$this->_authenticated && $this->_getSession('TICKET')) {
            $this->_authenticated = true;
        }

        if ($this->_authenticated == false && $this->getTicket()) {
            $this->validateTicket($this->getTicket());
        }

        return $this->_authenticated;
    }

    /**
     * Destroys session data for this client, redirects to the server logout
     * url.
     *
     * @param string $url URL to provide the client on logout.
     */
    public function logout($url = '')
    {
        session_destroy();
        if (empty($url)) {
            $url = self::getURL();
        }
        self::redirect($this->protocol->getLogoutURL($url));
    }
}
