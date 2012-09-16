<?php
/**
 * Tivoka - JSON-RPC done right!
 * Copyright (c) 2011-2012 by Marcel Klehr <mklehr@gmx.net>
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package  Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011-2012, Marcel Klehr
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
     * @throws Tivoka\Exception\RemoteProcedureException
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