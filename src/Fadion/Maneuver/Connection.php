<?php namespace Fadion\Maneuver;

use Exception;

/**
 * Class Connection
 *
 * Reads the connections from the config file
 * and builds an array of servers.
 *
 * @package Fadion\Maneuver\Connection
 * @author Fadion Dashi <jonidashi@gmail.com>
 * @author Baki Goxhaj <banago@gmail.com>
 * @licence MIT
 * @version 1.0
 */
class Connection
{

    /**
     * @var array List of servers
     */
    protected $servers = [];

    /**
     * Constructor
     *
     * @param null $server
     * @throws Exception if connection list empty
     * @throws Exception if specified server doesn't exist
     * @throws Exception if default server doesn't exist
     */
    public function __construct($server = null)
    {
        // Load connections array from config.
        $connections = app()->config['maneuver::config.connections'];
        $default = app()->config['maneuver::config.default'];

        if (! $connections)
        {
            throw new Exception("Connections list not set or empty. Please fill it with servers in Maneuver's config.");
        }

        // Create connection(s) from cli options
        if ($server)
        {
            // The server option is an array, even when
            // there's only one server passed.
            foreach ($server as $s)
            {
                if (! isset($connections[$s]))
                {
                    throw new Exception("Connection '$s' doesn't exist. Please create it in Maneuver's config.");
                }

                $this->servers[$s] = $connections[$s];
            }
        }
        // Create a single server connection when the
        // default server is defined.
        elseif (isset($default))
        {
            if (! isset($connections[$default]))
            {
                throw new Exception();
            }

            $this->servers[$default] = $connections[$default];
        }
        // Otherwise add all servers.
        else
        {
            $this->servers = $connections;
        }
    }

    /**
     * Returns server list
     *
     * @return array
     */
    public function servers()
    {
        return $this->servers;
    }

}