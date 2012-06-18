<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */

namespace Tivoka;

/**
 * The public interface on the server-side 
 * @package Tivoka
 */
abstract class Server
{
    
    /**
     * Starts processing the HTTP input
     * Notice: Calling this method will stop further execution of the script!
     * @param object $host An object whose methods will be provided for invokation
     * @return Tivoka_Server
     */
    static function provide($host)
    {
        return new Server\Server($host);
    }
}
?>