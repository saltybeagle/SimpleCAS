<?php
class SimpleCAS_Protocol_Version2_ValidationResponse
{
    protected $authenticationSuccess = false;
    protected $user    = false;
    protected $pgtiou  = false;
    protected $proxies = array();
    
    protected $attributes = array();

    /**
     * Construct a validation repsonse object from the CAS server's response.
     * 
     * @param string $response
     */
    public function __construct($response)
    {
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        
        if ($xml->loadXML($response)) {
            if ($success = $xml->getElementsByTagName('authenticationSuccess')) {
                if ($success->length > 0
                    && $uid = $success->item(0)->getElementsByTagName('user')) {
                    
                    $this->readExtraAttributes($success->item(0));
                    
                    // check for PGTIOU
                    if ($iou = $success->item(0)->getElementsByTagName('proxyGrantingTicket')) {
                        if ($iou->length) {
                            $this->pgtiou = $iou->item(0)->nodeValue;
                        }
                    }
                    $this->authenticationSuccess = true;
                    $this->user = $uid->item(0)->nodeValue;
                }
            }
        }
    }
    
    /**
     * Parses the response for released attributes.
     * 
     * Only supporting JASIG style
     *
     * @param DOMElement $success
     */
    protected function readExtraAttributes($success)
    {
        $attributes = $success->getElementsByTagName('attributes');
        if ($attributes->length && $attributes->item(0)->hasChildNodes()) {
            foreach ($attributes->item(0)->childNodes as $attr) {
                $this->addAttribute($attr->localName, $attr->nodeValue);
            }
        }
    }
    
    protected function addAttribute($name, $value)
    {
        if (isset($this->attributes[$name])) {
            if (!is_array($this->attributes[$name])) {
                $this->attributes[$name] = array($this->attributes[$name]);
            }
            
            $this->attributes[$name][] = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }
    
    public function authenticationSuccess()
    {
        return $this->authenticationSuccess;
    }
    
    public function getPGTIOU()
    {
        return $this->pgtiou;
    }
    
    public function getUser()
    {
        return $this->userid;
    }

    public function __toString()
    {
        if ($this->authenticationSuccess()) {
            return $this->user;
        }
        throw new Exception('Validation was not successful');
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
}