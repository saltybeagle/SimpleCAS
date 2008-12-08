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
interface SimpleCAS_Server
{
    /**
     * Returns the login URL for the cas server.
     *
     * @param string $service The URL to the service requesting authentication.
     * 
     * @return string
     */
    function getLoginURL($service);
    
    /**
     * Returns the logout url for the CAS server.
     *
     * @param string $service A URL to provide the user upon logout.
     * 
     * @return string
     */
    function getLogoutURL($service = null);
    
    /**
     * Returns the version of this cas server.
     * 
     * @return string
     */
    function getVersion();
    
    /**
     * Function to validate a ticket and service combination.
     *
     * @param string $ticket  Ticket given by the CAS Server
     * @param string $service Service requesting authentication
     * 
     * @return false|string False on failure, user name on success.
     */
    function validateTicket($ticket, $service);
}
?>