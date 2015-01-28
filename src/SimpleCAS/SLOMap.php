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
class SimpleCAS_SLOMap extends SimpleCAS_SLOMapInterface
{
    protected $pool = false;
    
    public function __construct($cache_driver = NULL)
    {
        if (!$cache_driver) {
            // Create Driver with default options
            $cache_driver = new \Stash\Driver\FileSystem();
            
            $cache_driver->setOptions(array(
                //Scope the cache to the current application only.
                'path' => sys_get_temp_dir() . '/simpleCAS_map_' . md5($this->getApplicationID())
            ));
        }
        
        // Inject the driver into a new Pool object.
        $this->pool = new \Stash\Pool($cache_driver);
    }

    /**
     * Generate a unique ID for the current application.  This is based on the session cookie's domain/path.
     * 
     * @return string
     */
    public function getApplicationID()
    {
        $cookie_params  = session_get_cookie_params();

        if (empty($cookie_params['domain'])) {
            //By default, the domain will be empty, so if it is empty, lets use the current server_name.
            if (isset($_SERVER['SERVER_NAME'])) {
                $cookie_params['domain'] = $_SERVER['SERVER_NAME'];
            } else {
                $cookie_params['domain'] = 'cli';
            }
        }
        return $cookie_params['domain'] . '-' . $cookie_params['path'];
    }

    /**
     * get the session id by a cas ticket
     * 
     * @param $cas_ticket
     * @return bool
     */
    public function get($cas_ticket)
    {
        $item = $this->pool->getItem($cas_ticket);

        if ($item->isMiss()) {
            return false;
        }
        
        return $item->get();
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
        $item = $this->pool->getItem($cas_ticket);
        return $item->set($session_id);
    }

    /**
     * Remove a CAS ticket from the registry
     * 
     * @param $cas_ticket
     * @return mixed|void
     */
    public function remove($cas_ticket)
    {
        $item = $this->pool->getItem($cas_ticket);
        
        if ($item->isMiss()) {
            return false;
        }
        
        return $item->clear();
    }
}
