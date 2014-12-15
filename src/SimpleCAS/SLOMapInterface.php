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
abstract class SimpleCAS_SLOMapInterface implements SimpleCAS_SingleSignOut
{
    /**
     * Determines if the posted request is a valid single sign out request.
     *
     * @param mixed $post $_POST data sent to the service.
     *
     * @return bool
     */
    public function validateLogoutRequest($post)
    {
        if (isset($_POST['logoutRequest']) && ($ticket = $this->parseLogoutRequest($_POST['logoutRequest']))) {
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

        if (session_id()) {
            //If a current session exists, save it and close it.
            session_commit();
        }

        //Start the session for this ticket.
        session_id($session_id);
        session_start();
        $result = session_destroy();

        $this->remove($cas_ticket);

        return $result;
    }

    /**
     * @param $xml - the XML from the single sign out request
     * @return bool|string - the CAS ticket to sign out, false if no ticket was found.
     */
    protected function parseLogoutRequest($xml)
    {
        $xml = new \SimpleXMLElement($xml);
        $element = $xml->xpath('//samlp:SessionIndex');

        if (empty($element)) {
            return false;
        }

        return (string)$element[0];
    }

    /**
     * Get a session id for a given CAS ticket
     * 
     * @param string $cas_ticket
     * @return string mixed
     */
    abstract public function get($cas_ticket);

    /**
     * Save a mapping between a cas ticket and session id
     * 
     * @param $cas_ticket
     * @param $session_id
     * @return mixed
     */
    abstract public function set($cas_ticket, $session_id);

    /**
     * Remove a CAS ticket
     * 
     * @param $cas_ticket
     * @return mixed
     */
    abstract public function remove($cas_ticket);
}