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
 *
 * @property string $hostname The hostname of the CAS server
 * @property string $port [optional] The port the CAS server is running on
 * @property string $uri The URI path to the CAS server
 * @property boolean $gateway If the gateway protocol option is in use
 * @property boolean $renew If the renew protocol option is in use
 */
class SimpleCAS_Protocol_Version1 extends SimpleCAS_Protocol
{
    const VERSION = '1.0';

    /**
     * Construct a new SimpleCAS server object.
     *
     *  <code>
     *  $options = array('hostname' => 'login.unl.edu',
     *                   'port'     => 443,
     *                   'uri'      => 'cas');
     *  $protocol = new SimpleCAS_Protocol_Version1($options);
     *  </code>
     *
     * @param array()
     */
    function __construct($options)
    {
        foreach ($options as $option=>$val) {
            $this->$option = $val;
        }
    }

    /**
     * Returns a url to the CAS server endpoint
     *
     * @param string $endpoint The CAS server endpoint
     * @param string|array $querystring [optional] Extra querystring params to send to the endpoint
     * @return string
     */
    protected function _buildCASURL($endpoint, $querystring = '')
    {
        $url = "https://{$this->hostname}";

        if (isset($this->port) && $this->port != 443) {
            $url .= ":{$this->port}";
        }

        /**
        * For the case where the CAS server has no URI (service URL is cas.example.edu as opposed
        * to the cas.example.edu/cas convention), simply add the trailing slash and endpoint to the
        * service URL.
        */
        if (empty($this->uri)) {
            $url .= "/{$endpoint}";
        } else {
            $url .= "/{$this->uri}/{$endpoint}";
        }

        if ($querystring) {
            if (is_array($querystring)) {
                $querystring = http_build_query($querystring);
            }

            $url .= "?{$querystring}";
        }

        return $url;
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
        $options = array(
            'service' => $service,
            'ticket' => $ticket,
        );
        if (isset($this->renew)) {
            $options['renew'] = 'true';
        }

        return $this->_buildCASURL('validate', $options);
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
        $options = array('service' => $service);
        if (isset($this->gateway)) {
            $options['gateway'] = 'true';
        } elseif (isset($this->renew)) {
            $options['renew'] = 'true';
        }

        return $this->_buildCASURL('login', $options);
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
        if ($service) {
            $service = ($this->logoutServiceRedirect ? 'service=' : 'url=') . urlencode($service);
        }

        return $this->_buildCASURL('logout', $service);
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