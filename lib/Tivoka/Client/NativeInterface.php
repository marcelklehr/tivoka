<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */

namespace Tivoka\Client;
use Tivoka\Exception;

/**
 * JSON-RPC native remote interface
 * @package Tivoka
 */
class NativeInterface {
    
    /**
     * Holds the last request
     * @var Tivoka\Client\Request
     */
    public $last_request;
    
    /**
     * Holds the connection to the remote server
     * @var Tivoka\Client\Connection
     */
    public $connection;
    
    /**
     * Construct a native remote interface
     * @param Tivoka\Client\Connection $connection The connection to use
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    /**
     * Sends a JSON-RPC request
     * @param Tivoka\Client\Request $request A Tivoka request
     * @throws Tivoka\Exception\RemoteException
     * @return mixed
     */
    public function __call($method, $args) {
        $this->last_request = new Request($method, $args);
        $this->connection->send($this->last_request);
        
        if($this->last_request->isError()) {
            throw new Exception\RemoteProcedureException($this->last_request->errorMessage, $this->last_request->error);
        }
        return $this->last_request->result;
    }

}
?>