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

use Tivoka\Client\Request;
use Tivoka\Client\NativeInterface;

/**
 * Connection interface
 * @package Tivoka
 */
interface ConnectionInterface {
    /**
     * Sets the spec version to use for this connection
     * @param string $spec The spec version (e.g.: "2.0")
     */
    public function useSpec($spec);

    /**
     * Sets any backend-specific options for this connection
     * @param string $options The backend-specific options.
     */
    public function setOptions($options);

    /**
     * Sends a JSON-RPC request
     * @param Request $request A Tivoka request
     * @return Request if sent as a batch request the BatchRequest object will be returned
     */
    public function send(Request $request);
    
    /**
     * Send a request directly
     * @param string $method
     * @param array $params
     */
    public function sendRequest($method, $params=null);
    
    /**
     * Send a notification directly
     * @param string $method
     * @param array $params
     */
    public function sendNotification($method, $params=null);
    
    /**
     * Creates a native remote interface for the target server
     * @return NativeInterface
     */
    public function getNativeInterface();
}
