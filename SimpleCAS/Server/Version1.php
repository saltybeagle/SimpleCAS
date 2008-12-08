<?php
/**
 * Class representing a CAS server which supports the CAS1 protocol.
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
class SimpleCAS_Server_Version1 implements SimpleCAS_Server
{
    const VERSION = '1.0';
    
    /**
     * Construct a new SimpleCAS server object.
     *
     * @param string $server_hostname Server hostname - login.unl.edu
     * @param int    $server_port     Server port number - 443 (ssl)
     * @param string $server_uri      Server uri - cas
     */
    function __construct($server_hostname,
                         $server_port,
                         $server_uri)
    {
        $this->server_hostname = $server_hostname;
        $this->server_port     = $server_port;
        $this->server_uri      = $server_uri;
    }
    
    /**
     * Returns the URL used to validate a ticket.
     *
     * @param string $ticket  Ticket to validate
     * @param string $service URL to the service requesting authentication
     * 
     * @return string
     */
    function getValidationURL($ticket, $service)
    {
        return 'https://' . $this->server_hostname . '/'
                          . $this->server_uri . '/validate?'
                          . 'service=' . urlencode($service)
                          . '&ticket=' . $ticket;
    }
    
    /**
     * Returns the URL to login form for the CAS server.
     *
     * @param string $service Service url requesting authentication.
     * 
     * @return string
     */
    function getLoginURL($service)
    {
        return 'https://' . $this->server_hostname
                          . '/'.$this->server_uri
                          . '/login?service='
                          . urlencode($service);
    }
    
    /**
     * Returns the URL to logout of the CAS server.
     *
     * @param string $service Service url provided to the user.
     * 
     * @return string
     */
    function getLogoutURL($service = '')
    {
        if (isset($service)) {
            $service = '?url='.urlencode($service);
        }
        
        return 'https://' . $this->server_hostname
                          . '/'.$this->server_uri
                          . '/logout'
                          . $service;
    }
    
    /**
     * Function to validate a ticket and service combination.
     *
     * @param string $ticket  Ticket given by the CAS Server
     * @param string $service Service requesting authentication
     * 
     * @return false|string False on failure, user name on success.
     */
    function validateTicket($ticket, $service)
    {
        $validation_url = $this->getValidationURL($ticket, $service);
        
        $http_request = new HTTP_Request2($validation_url);
        
        $response = $http_request->send();
        
        if ($response->getStatus() == 200
            && substr($response->getBody(), 0, 3) == 'yes') {
            list($message, $uid) = explode("\n", $response->getBody());
            return $uid;
        }
        return false;
    }
    
    /**
     * Returns the CAS server protocol this object implements.
     *
     * @return string
     */
    function getVersion()
    {
        return self::VERSION;
    }
}
?>