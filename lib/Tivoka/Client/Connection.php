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
use Tivoka\Tivoka;

/**
 * JSON-RPC connection
 * @package Tivoka
 */
class Connection {

    public $target;
    public $headers;
    
    public $spec = Tivoka::SPEC_2_0;
    
    /**
     * Constructs connection
     * @access private
     * @param string $target URL
     */
    public function __construct($target) {
        //validate url...
        if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
            throw new Exception\Exception('Valid URL (scheme://domain[/path][/file]) required.');
        
        //validate scheme...
        $t = parse_url($target);
        if(strtolower($t['scheme']) != 'http' && strtolower($t['scheme']) != 'https')
            throw new Exception\Exception('Unknown or unsupported scheme given.');
        
        $this->target = $target;
        $this->headers = array();
    }
    
    /**
     * Sets the spec version to use for this connection
     * @param string $spec The spec version (e.g.: "2.0")
     */
    public function useSpec($spec) {
        $this->spec = Tivoka::validateSpecVersion($spec);
        return $this;
    }
    
    
    /**
     * Sets the HTTP headers to use for upcoming send requests
     * @param string label of header
     * @param string value of header
     */
    public function setHeader($label, $value) {
      $this->headers[$label] = $value;
      return $this;
    }

    /**
     * Sends a JSON-RPC request
     * @param Tivoka_Request $request A Tivoka request
     * @return Tivoka_Request if sent as a batch request the BatchRequest object will be returned
     */
    public function send($request) {
        if(func_num_args() > 1 ) $request = func_get_args();
        if(is_array($request)) {
            $request = new BatchRequest($request);
        }
        
        if(!($request instanceof Request)) throw new Exception\Exception('Invalid data type to be sent to server');
        
        // preparing connection...
        $context = array(
                'http' => array(
                    'content' => $request->getRequest($this->spec),
                    'header' => "Content-Type: application/json\r\n".
                                "Connection: Close\r\n",
                    'method' => 'POST',
                    'timeout' => 10.0
                )
        );
        foreach($this->headers as $label => $value) {
          $context['http']['header'] .= $label . ": " . $value . "\r\n";
        }
        //sending...
        $response = @file_get_contents($this->target, false, stream_context_create($context));
        if($response === FALSE) {
            throw new Exception\ConnectionException('Connection to "'.$this->target.'" failed');
        }
        $request->setResponse($response);
        $request->setHeaders($http_response_header);
        return $request;
    }
    
    /**
     * Send a request directly
     * @param string $method
     * @param array $params
     */
    public function sendRequest($method, $params=null) {
        $request = new Request($method, $params);
        $this->send($request);
        return $request;
    }
    
    /**
     * Send a notification directly
     * @param string $method
     * @param array $params
     */
    public function sendNotification($method, $params=null) {
        $this->send(new Notification($method, $params));
    }
    
    /**
     * Creates a native remote interface for the target server
     * @return Tivoka\Client\NativeInterface
     */
    public function getNativeInterface()
    {
        return new NativeInterface($this);
    }
}
?>
