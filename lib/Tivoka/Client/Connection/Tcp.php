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
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright (c) 2011-2012, Marcel Klehr
 */

namespace Tivoka\Client\Connection;
use Tivoka\Client\BatchRequest;
use Tivoka\Exception;
use Tivoka\Client\Request;

/**
 * Raw TCP connection
 * @package Tivoka
 */
class Tcp extends AbstractConnection {
    

    /**
     * Server host.
     * @var string
     */
    protected $host;

    /**
     * Server port.
     * @var int
     */
    protected $port;

    /**
     * Connection stream.
     * @var resource
     */
    protected $socket;

    /**
     * Constructs connection.
     * @param string $host Server host.
     * @param int $port Server port.
     */
    public function __construct($host, $port)
    {
        //validate url...
        if (!is_numeric($port)) {
            throw new Exception\Exception('Invalid port specified: "' . $port . '".');
        }

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Closes connection on finalization.
     */
    public function __destruct()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
    
    /**
     * Changes timeout.
     * @param int $timeout
     * @return Tcp Self reference.
     */
    public function setTimeout($timeout)
    {
    	$this->timeout = $timeout;
    
    	// change timeout for already initialized connection
    	if (isset($this->socket)) {
    		stream_set_timeout($this->socket, $timeout);
    	}
    
    	return $this;
    }

    /**
     * Sends a JSON-RPC request over plain TCP.
     * @param Request $request,... A Tivoka request.
     * @return Request|BatchRequest If sent as a batch request the BatchRequest object will be returned.
     */
    public function send(Request $request)
    {
        // connect on first call
        if (!isset($this->socket)) {
            $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            // check for success
            if ($this->socket === false) {
                throw new Exception\ConnectionException('Connection to "' . $this->host . ':' . $this->port . '" failed (errno ' . $errno . '): ' . $errstr);
            }

            stream_set_timeout($this->socket, $this->timeout);
        }

        if (func_num_args() > 1) {
            $request = func_get_args();
        }
        if (is_array($request)) {
            $request = new BatchRequest($request);
        }

        if (!($request instanceof Request)) {
            throw new Exception\Exception('Invalid data type to be sent to server');
        }

        // sending request
        fwrite($this->socket, $request->getRequest($this->spec));
        fwrite($this->socket, "\n");
        fflush($this->socket);

        // read server respons
        $response = fgets($this->socket);

        if ($response === false) {
            throw new Exception\ConnectionException('Connection to "' . $this->host . ':' . $this->port . '" failed');
        }

        $request->setResponse($response);
        return $request;
    }
}
