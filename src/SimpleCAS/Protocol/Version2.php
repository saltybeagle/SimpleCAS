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
class SimpleCAS_Protocol_Version2 extends SimpleCAS_Protocol_Version1 implements SimpleCAS_SingleSignOut, SimpleCAS_ProxyGranting
{
    const VERSION = '2.0';
    
    protected $lastValidationResponse;

    /**
     * Returns the URL used to validate a ticket.
     *
     * @param string $ticket  Ticket to validate
     * @param string $service URL to the service requesting authentication
     * @param string $pgtUrl The URL of the proxy callback
     *
     * @return string
     */
    function getValidationURL($ticket, $service, $pgtUrl = null)
    {
        $options = array(
            'service' => $service,
            'ticket' => $ticket,
        );
        if ($pgtUrl) {
            $options['pgtUrl'] = $pgtUrl;
        }
        if (isset($this->renew)) {
            $options['renew'] = 'true';
        }

        return $this->_buildCASURL('serviceValidate', $options);
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

        $defaultClass = SimpleCAS_Protocol::DEFAULT_REQUEST_CLASS;
        if ($http_request instanceof $defaultClass) {
            $http_request->setURL($validation_url);

            $response = $http_request->send();
        } else {
            $http_request->setUri($validation_url);

            $response = $http_request->request();
        }

        if ($response->getStatus() == 200) {
            $this->lastValidationResponse = $validationResponse = new SimpleCAS_Protocol_Version2_ValidationResponse($response->getBody());
            if ($validationResponse->authenticationSuccess()) {
                return $validationResponse->__toString();
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
    
    public function getAttributes()
    {
        if ($this->lastValidationResponse) {
            return $this->lastValidationResponse->getAttributes();
        }
        
        return false;
    }
}