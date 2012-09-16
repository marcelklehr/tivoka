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

namespace Tivoka\Server;
use Tivoka\Exception;
use Tivoka\Tivoka;

/**
 * Processes the  JSON-RPC input
 * @package Tivoka
 */
class Server
{
    /**
    * @var object The object given to __construct()
    * @see Tivoka\Server\Server::__construct()
    * @access private
    */
    public $host;
    
    /**
     * @var array The parsed json input as an associative array
     * @access private
     */
    private $input;
    
    /**
     * @var array A list of associative response arrays to json_encode
     * @access private
     */
    private $response;
    
    /**
     * The spec version the serve will use
     * @var int
     * @access private
     */
    public $spec = Tivoka::SPEC_2_0;
    
    /**
     * This is modified by Server::hideErrors()
     * @var bool
     * @access private
     */
    public $hide_errors = false;
    
    /**
     * Constructss a Server object
     * @param object $host An object whose methods will be provided for invokation
     */
    public function __construct($host) {
        if(is_array($host)) {
            $methods = $host;
            $host = new MethodWrapper();
            foreach($methods as $name => $method)
            {
                if($host->register($name, $method)) continue;
                throw new Exception\Exception('Given value for "'.$name.'" is no valid callback.');
            }
        }
        
        $this->host = $host;
    }
    
    /**
     * Sets the spec version to use for this server
     * @param string $spec The spec version (e.g.: "2.0")
     */
    public function useSpec($spec) {
        $this->spec = Tivoka::validateSpecVersion($spec);
        return $this;
    }
    
    /**
     * If invoked, the server will try to hide all PHP errors, to prevent them from obfuscating the output.
     */
    public function hideErrors() {
        $this->hide_errors = true;
        return $this;
    }
    
    /**
     * Starts processing of the HTTP input. This will stop further execution of the script.
     */
    public function dispatch() {
        // disable error reporting?
        if($this->hide_errors) error_reporting(0);// prevents messing up the response
        
        $this->input = file_get_contents('php://input');
        
        $json_errors = array(
            JSON_ERROR_NONE => '',
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error'
        );
        
        // set header if not already sent...
        if(headers_sent() === FALSE) header('Content-type: application/json');
        
        
        // any request at all?
        if(trim($this->input) === '')
        {
            $this->returnError(null,-32600);
            $this->respond();
        }
        
        // decode request...
        $this->input = json_decode($this->input,true);
        if($this->input === NULL)
        {
            $this->returnError(null,-32700, 'JSON parse error: '.$json_errors[json_last_error()] );
            $this->respond();
        }
        
        // batch?
        if(($batch = self::interpretBatch($this->input)) !== FALSE)
        {
            foreach($batch as $request)
            {
                $this->process($request);
            }
            $this->respond();
        }
        
        //process request
        $this->process($this->input);
        $this->respond();
    }
    
    /**
     * Processes the passed request
     * @param array $request the parsed request
     */
    public function process($request) {
        $server = $this;
        $params = (isset($request['params']) === FALSE) ? array() : $request['params'];
        $id = (isset($request['id']) === FALSE) ? null : $request['id'];
        $isNotific = $this::interpretRequest($this->spec, $request) === FALSE;
        
        // utility closures
        $error = function($code, $msg='', $data=null) use ($server, $id, $isNotific) {
            if($isNotific) return;
            $server->returnError($id, $code, $msg, $data);
        };
        
        $result = function($result) use ($server, $id, $isNotific){
            if($isNotific) return;
            $server->returnResult($id, $result);
        };
        
        //validate...
        if(($req = self::interpretRequest($this->spec, $request)) === FALSE)
        {
            if(($req = self::interpretNotification($this->spec, $request)) === FALSE)
            {
                return $error($id, -32600, 'Invalid Request', $request);
                
            }
        }
        
        //search method...
        if(!is_callable(array($this->host, $request['method'])))
        {
            return $error(-32601, 'Method not found', $request['method']);
        }
        
        //invoke...
        try {
            return $result( $this->host->{$request['method']}($params) );
        }catch(Tivoka\Exception\ProcedureException $e) {
            if($e instanceof Tivoka\Exception\InvalidParamsException) return $error(-32602, ($e->getMessage() != "") ? $e->getMessage() : 'Invalid parameters');
            return $error(-32603, ($e->getMessage() != "") ? $e->getMessage() : 'Internal error invoking method');
        }
    }
    
    /**
     * Receives the computed result
     * @param mixed $id The id of the original request
     * @param mixed $result The computed result
     * @access private
     */
    public function returnResult($id, $result)
    {
        switch($this->spec) {
        case Tivoka::SPEC_2_0:
            $this->response[] = array(
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => $result
            );
            break;
        case Tivoka::SPEC_1_0:
            $this->response[] = array(
                                'id' => $id,
                                'result' => $result,
                                'error' => null
            );
            break;
        }
    }
    
    /**
     * Receives the error from computing the result
     * @param mixed $id The id of the original request
     * @param integer $code The error code
     * @param string $message The error message
     * @param mixed $data Additional data
     * @access private
     */
    public function returnError($id, $code, $message='', $data=null)
    {
        $msg = array(
            -32700 => 'Parse error',
            -32600 => 'Invalid Request',
            -32601 => 'Method not found',
            -32602 => 'Invalid params',
            -32603 => 'Internal error'
        );
        switch($this->spec) {
        case Tivoka::SPEC_2_0:
            $response = array(
                'jsonrpc'=>'2.0',
                'id'=>$id,
                'error'=> array(
                    'code'=>$code,
                    'message'=>$message,
                    'data'=>$data
            ));
            break;
        case Tivoka::SPEC_1_0:
            $response = array(
                'id'=>$id,
                'result' => null,
                'error'=> array(
                    'code'=>$code,
                    'message'=>$message,
                    'data'=>$data
            ));
            break;
        }
        if($message === '')$response['error']['message'] = $msg[$code];
        $this->response[] = $response;
    }
    
    /**
     * Outputs the processed response
     * @access private
     */
    public function respond()
    {
        if(!is_array($this->response))//no array
            exit;
        
        $count = count($this->response);
        
        if($count == 1)//single request
            die(json_encode($this->response[0]));
    
        if($count > 1)//batch request
            die(json_encode($this->response));
    
        if($count < 1)//no response
            exit;
    }
    
    /**
    * Validates and sanitizes a normal request
    * @param array $assoc The json-parsed JSON-RPC request
    * @static
    * @return array Returns the sanitized request and if it was invalid, a boolean FALSE is returned
    */
    public static function interpretRequest($spec, array $assoc)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['id'], $assoc['method']) === FALSE) return FALSE;
                if($assoc['jsonrpc'] != '2.0' || !is_string($assoc['method'])) return FALSE;
                $request = array(
                            'id' =>  &$assoc['id'],
                            'method' => &$assoc['method']
                );
                if(isset($assoc['params'])) {
                    if(!is_array($assoc['params'])) return FALSE;
                    $request['params'] = $assoc['params'];
                }
                return $request;
            case Tivoka::SPEC_1_0:
                if(isset($assoc['id'], $assoc['method']) === FALSE) return FALSE;
                if(!is_string($assoc['method'])) return FALSE;
                $request = array(
                                        'id' =>  &$assoc['id'],
                                        'method' => &$assoc['method']
                );
                if(isset($assoc['params'])) {
                    if(!is_array($assoc['params']) || (bool)count(array_filter(array_keys($assoc['params']), 'is_string'))) return FALSE;// if not associative
                    $request['params'] = &$assoc['params'];
                }
                return $request;
        }
    }
    
    /**
     * Validates and sanitizes a notification
     * @param array $assoc The json-parsed JSON-RPC request
     * @static
     * @return array Returns the sanitized request and if it was invalid, a boolean FALSE is returned
     */
    public static function interpretNotification($spec, array $assoc)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['method']) === FALSE || isset($assoc['id']) !== FALSE) return FALSE;
                if($assoc['jsonrpc'] != '2.0' || !is_string($assoc['method'])) return FALSE;
                $request = array(
                    'method' => &$assoc['method']
                );
                if(isset($assoc['params'])) {
                    if(!is_array($assoc['params'])) return FALSE;
                    $request['params'] = $assoc['params'];
                }
                return $request;
            case Tivoka::SPEC_1_0:
                if(isset($assoc['method']) === FALSE || isset($assoc['id']) !== FALSE) return FALSE;
                if(!is_string($assoc['method'])) return FALSE;
                $request = array(
                    'method' => &$assoc['method']
                );
                if(isset($assoc['params'])) {
                    if(!is_array($assoc['params']) || (bool)count(array_filter(array_keys($assoc['params']), 'is_string'))) return FALSE;// if not associative
                    $request['params'] = $assoc['params'];
                }
                return $request;
        }
    }
    
    /**
     * Validates a batch request
     * @param array $assoc The json-parsed JSON-RPC request
     * @static
     * @return array Returns the original request and if it was invalid, a boolean FALSE is returned
     * @access private
     */
    public static function interpretBatch(array $assoc)
    {
        if(count($assoc) <= 1)
        return FALSE;
    
        foreach($assoc as $req)
        {
            if(!is_array($req))
                return FALSE;
        }
        return $assoc;
    }
}
?>