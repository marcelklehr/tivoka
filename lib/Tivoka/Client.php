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

namespace Tivoka;

/**
 * The public interface to all tivoka functions
 * @package Tivoka
 */
abstract class Client
{
    
    /**
     * Initializes a Connection to a remote server
     * @param string $target the URL of the target server
     * @return Tivoka\Client\Connection
     */
    public static function connect($target) {
        return new Client\Connection($target);
    }
    
    /**
     * Creates a request
     * @param string $method The method to invoke
     * @param array $params The parameters
     * @return Tivoka\Client\Request
     */
    public static function createRequest($method, $params=null) {
        return new Client\Request($method, $params);
    }
    
    /**
     * alias of Tivoka\Client::createRequest
     */
    public static function request($method, $params=null) {
        return self::createRequest($method, $params);
    }
    
    /**
     * Creates a notification
     * @param string $method The method to invoke
     * @param array $params The parameters
     */
    public static function createNotification($method, $params=null) {
        return new Client\Notification($method, $params);
    }
    
    /**
     * alias of Tivoka\Client::createNotification
     */
    public static function notification($method, $params=null) {
        return self::createNotification($method, $params);
    }
    
    /**
     * Creates a batch request
     * @param mixed $request either an array of requests or a comma-seperated list of requests
     * @throws Tivoka\Exception\Exception
     * @return Tivoka\Client\BatchRequest
     */
    public static function createBatch($request) {
        if(func_num_args() > 1 ) $request = func_get_args();
        if(!is_array($request)) throw new Exception\Exception('Object of invalid data type passed to Tivoka::createBatch.');
        return new Client\BatchRequest($request);
    }
    
    /**
     * alias of Tivoka\Client::createBatch
     */
    public static function batch($request) {
        if(func_num_args() > 1 ) $request = func_get_args();
        return self::createBatch($request);
    }
}
?>