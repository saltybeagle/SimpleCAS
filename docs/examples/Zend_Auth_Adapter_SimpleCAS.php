<?php
/**
 * This is a Zend_Auth adapter library for CAS.
 * It uses SimpleCAS.
 *
 * <code>
 * public function casAction()
 * {
 *     $auth = Zend_Auth::getInstance();
 *     $authAdapter = new UNL_CasZendAuthAdapter(
 *         Zend_Registry::get('config')->auth->cas
 *     );
 * 
 *     # User has not been identified, and there's a ticket in the URL
 *     if (!$auth->hasIdentity() && isset($_GET['ticket'])) {
 *         $authAdapter->setTicket($_GET['ticket']);
 *         $result = $auth->authenticate($authAdapter);
 * 
 *         if ($result->isValid()) {
 *             Zend_Session::regenerateId();
 *         }
 *     }
 * 
 *     # No ticket or ticket was invalid. Redirect to CAS.
 *     if (!$auth->hasIdentity()) {
 *         $this->_redirect($authAdapter->getLoginURL());
 *     }
 * }
 * </code>
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

require_once('SimpleCAS/Server/Version2.php');

class Zend_Auth_Adapter_SimpleCAS implements Zend_Auth_Adapter_Interface
{
    /**
     * CAS client
     */
    private $_protocol;

    /**
     * Service ticket
     */
    private $_ticket;

    /**
     * Constructor
     *
     * @param string $server_hostname
     * @param string $server_port
     * @param string $server_uri
     * @return void
     */ 
    public function __construct($options)
    {
        $this->_protocol = new SimpleCAS_Protocol_Version2($options);
    }

    public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
        return $this;
    }

    /**
     * Authenticates ticket
     *
     * The ticket is provided with setTicket
     *
     * @param return boolean
     */ 
    public function authenticate()
    {
        if ($id = $this->_protocol->validateTicket($this->_ticket, self::getURL())) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $id,
                array("Authentication successful"));
        } else {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE,
                null,
                array("Authentication failed"));
        }
    }
    
    /**
     * Returns the current URL without CAS affecting parameters.
     * Copied directly from SimpleCAS.php 0.1.0
     * 
     * @return string url
     */
    static public function getURL()
    {
        if (isset($_SERVER['HTTPS'])
            && !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
    
        $url = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        
        $replacements = array('/\?logout/'        => '',
                              '/&ticket=[^&]*/'   => '',
                              '/\?ticket=[^&;]*/' => '?',
                              '/\?%26/'           => '?',
                              '/\?&/'             => '?',
                              '/\?$/'             => '');
        
        $url = preg_replace(array_keys($replacements),
                            array_values($replacements), $url);
        
        return $url;
    }

    /**
     * Returns the URL to login form on the CAS server.
     *
     * @return string
     */
    public function getLoginURL()
    {
        return $this->_protocol->getLoginURL(self::getURL());
    }
 
}
