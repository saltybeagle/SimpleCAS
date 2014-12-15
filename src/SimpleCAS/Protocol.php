<?php
/**
 * Interface all CAS servers must implement.
 * 
 * Each concrete class which implements this server interface must provide
 * all the following functions.
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
abstract class SimpleCAS_Protocol
{
    const DEFAULT_REQUEST_CLASS = 'HTTP_Request2';

    protected $requestClass;
    protected $request;
    protected $sessionMap = false;

    /**
     * Option to request the CAS server redirect after processing a logout URL.
     *
     * This option instructs the getLogoutURL() method to rename the "url" query
     * parameter to "service", so that a LogoutController on the CAS server that
     * has followServiceRedirects support enabled can redirect to our URL
     * instead of rendering a logout template and linking to it.
     *
     * @link http://tp.its.yale.edu/pipermail/cas/2008-August/009508.html
     * @var boolean
     */
    protected $logoutServiceRedirect;

    /**
     * Returns the login URL for the CAS server.
     *
     * @param string $service The URL to the service requesting authentication.
     *
     * @return string
     */
    abstract function getLoginURL($service);

    /**
     * Returns the logout URL for the CAS server.
     *
     * @param string $service A URL to provide the user upon logout.
     *
     * @return string
     */
    abstract function getLogoutURL($service = null);

    /**
     * Returns the version of this CAS server.
     *
     * @return string
     */
    abstract function getVersion();

    /**
     * Function to validate a ticket and service combination.
     *
     * @param string $ticket  Ticket given by the CAS Server
     * @param string $service Service requesting authentication
     *
     * @return false|string False on failure, user name on success.
     */
    abstract function validateTicket($ticket, $service);
    
    /**
     * Get the HTTP_Request2 object.
     *
     * @return HTTP_Request
     */
    function getRequest()
    {
        $class = empty($this->requestClass) ? self::DEFAULT_REQUEST_CLASS : $this->requestClass;
        if (!$this->request instanceof $class) {
            $this->request = new $class();
        }
        return $this->request; 
    }

    /**
     * Set the HTTP Request object.
     *
     * @param $request
     */
    function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Get the logoutServiceRedirect option.
     *
     * @return boolean
     */
    function getLogoutServiceRedirect()
    {
        return $this->logoutServiceRedirect;
    }

    /**
     * Set the logoutServiceRedirect option.
     *
     * @param boolean
     */
    function setLogoutServiceRedirect($logoutServiceRedirect)
    {
        $this->logoutServiceRedirect = (boolean) $logoutServiceRedirect;
    }

    /**
     * Set the session map.  The session map is used for single log out.
     * 
     * To disable SLO support, set the session map to null.
     * 
     * @param SimpleCAS_SLOMapInterface|null $sessionMap
     */
    function setSessionMap(SimpleCAS_SLOMapInterface $sessionMap = NULL)
    {
        $this->sessionMap = $sessionMap;
    }

    /**
     * Get the sessions map.  If one is not set yet, it will return the default SimpleCAS_SLOMap
     * 
     * @return SimpleCAS_SLOMapInterface|null (null if SLO is disabled)
     */
    function getSessionMap()
    {
        if ($this->sessionMap === false) {
            $this->setSessionMap(new SimpleCAS_SLOMap());
        }
        
        return $this->sessionMap;
    }
}