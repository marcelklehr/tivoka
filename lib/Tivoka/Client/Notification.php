<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */

namespace Tivoka\Client;

/**
 * JSON-RPC notification
 * @package Tivoka
 */
class Notification extends Request
{
    /**
     * Constructs a new JSON-RPC notification object
     * @param string $method The remote procedure to invoke
     * @param mixed $params Additional params for the remote procedure
     * @see Tivoka_Connection::send()
     */
    public function __construct($method, $params=null)
    {
        $this->id = null;
    }
}
?>