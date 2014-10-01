<?php
/**
 * A class that implments Single Log out (SLO) and maintains a mapping between php session IDs and CAS tickets.
 *
 * PHP version 5
 *
 * @category  Authentication
 * @package   SimpleCAS
 * @author    Michael Fairchild <mfairchild365@gmail.com>
 * @copyright 2014 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 */
class SimpleCAS_SLOMap implements SimpleCAS_SingleSignOut
{
    protected $data          = array();
    protected $file_name     = false;
    protected $tmp_directory = false;
    
    public function __construct($file_name = false, $tmp_directory = false)
    {
        if (!$tmp_directory) {
            $this->tmp_directory = sys_get_temp_dir();
        }
        if (!$file_name) {
            $this->file_name     = 'simplecas_map_' . md5(SimpleCas::getURL())  . '.php.ser';
        }
    }

    /**
     * Get the tmp directory path
     * 
     * @return bool|string
     */
    protected function getTmpDirectory()
    {
        return $this->tmp_directory;
    }

    /**
     * Get the session map file name's path
     * 
     * @return string
     */
    protected function getMapFilePath()
    {
        return $this->getTmpDirectory() . '/' . $this->file_name;
    }

    /**
     * Load the map file
     * 
     * @return bool
     */
    protected function loadMapFile()
    {
        if (file_exists($this->getMapFilePath())) {
            $data = file_get_contents($this->getMapFilePath());
            $this->data = unserialize($data);
            
            return true;
        }
        
        return false;
    }

    /**
     * Save the map file
     * 
     * @return int
     */
    protected function saveMapFile()
    {
        return file_put_contents($this->getMapFilePath(), serialize($this->data));
    }

    /**
     * get the session id by a cas ticket
     * 
     * @param $cas_ticket
     * @return bool
     */
    protected function get($cas_ticket)
    {
        $this->loadMapFile();
        
        if (isset($this->data[$cas_ticket])) {
            return $this->data[$cas_ticket];
        }

        return false;
    }

    /**
     * Set the session id for a cas ticket
     * 
     * @param $cas_ticket
     * @param $session_id
     * @return bool
     */
    public function set($cas_ticket, $session_id)
    {
        $this->loadMapFile();
        $this->data[$cas_ticket] = $session_id;
        $this->saveMapFile();

        return true;
    }

    /**
     * @param $xml - the XML from the single sign out request
     * @return bool|string - the CAS ticket to sign out, false if no ticket was found.
     */
    protected function getTicket($xml)
    {
        $xml = new \SimpleXMLElement($xml);
        $element = $xml->xpath('//samlp:SessionIndex');
        
        if (empty($element)) {
            return false;
        }
        
        return (string)$element[0];
    }

    /**
     * Determines if the posted request is a valid single sign out request.
     *
     * @param mixed $post $_POST data sent to the service.
     *
     * @return bool
     */
    public function validateLogoutRequest($post)
    {
        if (isset($_POST['logoutRequest']) && ($ticket = $this->getTicket($_POST['logoutRequest']))) {
            return $ticket;
        }
        
        return false;
    }

    /**
     * Log out a session by the single log out ticket.
     * 
     * @param $cas_ticket
     * @return bool
     */
    public function logout($cas_ticket)
    {
        if (!$session_id = $this->get($cas_ticket)) {
            return false;
        }
        
        $current_session_id = session_id();
        session_id($session_id);
        $result = session_destroy();
        session_id($current_session_id);
        
        $this->loadMapFile();
        unset($this->data[$cas_ticket]);
        $this->saveMapFile();
        
        return $result;
    }
}