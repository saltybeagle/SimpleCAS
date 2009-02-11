<?php
/**
 * Class representing a CAS server which supports the CAS2 protocol.
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
class SimpleCAS_Server_Version2 extends SimpleCAS_Server_Version1 implements SimpleCAS_SingleSignOut, SimpleCAS_ProxyGranting
{
    const VERSION = '2.0';
    
    /**
     * Returns the URL used to validate a ticket.
     *
     * @param string $ticket  Ticket to validate
     * @param string $service URL to the service requesting authentication
     *
     * @return string
     */
    function getValidationURL($ticket, $service, $pgtUrl = null)
    {
        return 'https://' . $this->server_hostname . '/'
                          . $this->server_uri . '/serviceValidate?'
                          . 'service=' . urlencode($service)
                          . '&ticket=' . $ticket
                          . '&pgtUrl=' . urlencode($service);
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
        
        $http_request = clone $this->getRequest();
        
        $http_request->setURL($validation_url);
        
        $response = $http_request->send();
        
        if ($response->getStatus() == 200) {
            $validationResponse = new SimpleCAS_Server_Version2_ValidationResponse($response->getBody());
            if ($validationResponse->authenticationSuccess()) {
                return (string)$validationResponse;
            }
        }
        return false;
    }
    
    /**
     * Validates a single sign out logout request.
     *
     * @param mixed $post $_POST data
     *
     * @return bool
     */
    function validateLogoutRequest($post)
    {
        if (false) {
            return $ticket;
        }
        return false;
    }
    
    function getProxyTicket()
    {
        throw new Exception('not implemented');
    }
    
    function validateProxyTicket($ticket)
    {
        throw new Exception('not implemented');
    }
}
?>