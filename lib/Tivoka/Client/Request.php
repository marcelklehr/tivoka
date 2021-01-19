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

namespace Tivoka\Client;
use RuntimeException;
use Tivoka\Exception;
use Tivoka\Tivoka;
use Tivoka\Client\Connection\AbstractConnection;

/**
 * A JSON-RPC request
 * @package Tivoka
 */
class Request
{
    public $id;
    public $method;
    public $params;
    public $request;
    public $response;
    
    public $result;
    public $error;
    public $errorMessage;
    public $errorData;

    public $responseHeaders;
    public $responseHeadersRaw;

    /**
     * Constructs a new JSON-RPC request object
     * @param string $method The remote procedure to invoke
     * @param mixed $params Additional params for the remote procedure (optional)
     * @see Tivoka_Connection::send()
     */
    public function __construct($method, $params=null) {
        $this->id = self::uuid();
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Get the raw, JSON-encoded request
     *
     * @param int $spec
     *
     * @return false|string
     * @throws Exception\SpecException
     * @throws RuntimeException
     */
    public function getRequest($spec) {
        $this->spec = $spec;

        $requestString = self::prepareRequest($spec, $this->id, $this->method, $this->params);
        $this->request = json_encode($requestString);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_UTF8:
                $encodeList = $this->getEncodeList();
                $requestString = mb_convert_encoding($requestString, "ASCII", $encodeList);
                $this->request = json_encode($requestString);
                break;
            case JSON_ERROR_DEPTH:
                throw new RuntimeException('Maximum stack depth was reached');
            case JSON_ERROR_STATE_MISMATCH:
                throw new RuntimeException('Incorrect digits or mode mismatch are exist');
            case JSON_ERROR_CTRL_CHAR:
                throw new RuntimeException('Invalid control character');
            case JSON_ERROR_SYNTAX:
                throw new RuntimeException('Syntax error, invalid JSON');
            default:
                throw new RuntimeException('Unknown error');
        }

        return $this->request;
    }
    
    /**
     * Send this request to a remote server directly
     * @param mixed $target Remote end-point definition
     */
    public function sendTo($target) {
        $connection = AbstractConnection::factory($target);
        $connection->send($this);
    }

    /**
     * Parses headers as returned by magic variable $http_response_header
     * @param array $headers array of string coming from $http_response_header
     * @return array associative array linking a header label with its value
     */
    protected static function http_parse_headers($headers) {
      // rfc2616: The first line of a Response message is the Status-Line
      $headers = array_slice($headers, 1); // removing status-line

      $header_array = array();
      foreach($headers as $header) {
        preg_match('/(?P<label>[^ :]+):(?P<body>(.|\r?\n(?= +))*)$/', $header, $matches);
        $headers_array[$matches["label"]] = trim($matches["body"]);
      };
      return $headers_array;
    }

    /**
     * Interprets the response
     *
     * @param string $response json data
     *
     * @return void
     * @throws Exception\ConnectionException
     * @throws Exception\SyntaxException
     */
    public function setResponse($response) {
        $this->response = $response;

        //no response?
        if(trim($response) == '') {
            throw new Exception\ConnectionException('No response received');
        }
    
        //decode
        $resparr = json_decode($response,true);
        if($resparr == NULL) {
            throw new Exception\SyntaxException('Invalid response encoding');
        }
        
        $this->interpretResponse($resparr);
    }
    
    /**
     * Save and parse the HTTP headers
     * @param array $raw_headers array of string coming from $http_response_header magic var
     * @return void
     */
    public function setHeaders($raw_headers) {
        $this->responseHeadersRaw = $raw_headers;
        $this->responseHeaders = self::http_parse_headers($raw_headers);
    }

    /**
     * Interprets the parsed response
     *
     * @param array $json_struct
     *
     * @throws Exception\SyntaxException
     */
    public function interpretResponse($json_struct) {
        //server error?
        if(($error = self::interpretError($this->spec, $json_struct, $this->id)) !== FALSE) {
            $this->error        = $error['error']['code'];
            $this->errorMessage = $error['error']['message'];
            $this->errorData    = (isset($error['error']['data'])) ? $error['error']['data'] : null;
            return;
        }
    
        //valid result?
        if(($result = self::interpretResult($this->spec, $json_struct, $this->id)) !== FALSE)
        {
            $this->result = $result['result'];
            return;
        }
    
        throw new Exception\SyntaxException('Invalid response structure');
    }
    
    /**
     * Determines whether an error occured
     * @return bool
     */
    public function isError()
    {
        return ($this->error != NULL);
    }
    
    /**
     * Checks whether the given response is a valid result
     * @param array $assoc The parsed JSON-RPC response as an associative array
     * @param mixed $id The id of the original request
     * @return array the parsed JSON object
     */
    protected static function interpretResult($spec, array $assoc, $id)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['id']) === FALSE || 
                   !array_key_exists('result', $assoc)) return FALSE;
                if($assoc['id'] !== $id || $assoc['jsonrpc'] != '2.0') return FALSE;
                return array(
                        'id' => $assoc['id'],
                        'result' => $assoc['result']
                );
            case Tivoka::SPEC_1_0:
                if(isset($assoc['result'], $assoc['id']) === FALSE) return FALSE;
                if($assoc['id'] !== $id && $assoc['result'] === null) return FALSE;
                return array(
                    'id' => $assoc['id'],
                    'result' => $assoc['result']
                );
        }
    }
    
    /**
     * Checks whether the given response is valid and an error
     * @param array $assoc The parsed JSON-RPC response as an associative array
     * @param mixed $id The id of the original request
     * @return array parsed JSON object
     */
    protected static function interpretError($spec, array $assoc, $id)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['error']) == FALSE) return FALSE;
                if($assoc['id'] != $id && $assoc['id'] != null && isset($assoc['id']) OR $assoc['jsonrpc'] != '2.0') return FALSE;
                if(isset($assoc['error']['message'], $assoc['error']['code']) === FALSE) return FALSE;
                return array(
                        'id' => $assoc['id'],
                        'error' => $assoc['error']
                );
            case Tivoka::SPEC_1_0:
                if(isset($assoc['error'], $assoc['id']) === FALSE) return FALSE;
                if($assoc['id'] != $id && $assoc['id'] !== null) return FALSE;
                if(isset($assoc['error']) === FALSE) return FALSE;
                return array(
                    'id' => $assoc['id'],
                    'error' => array(
                        'data' => $assoc['error'],
                        'code' => $assoc['error'],
                        'message' => $assoc['error']
                    )
                );
        }
    }
    
    /**
     * Encodes the request properties
     * 
     * @param mixed $id The id of the request
     * @param string $method The method to be called
     * @param array $params Additional parameters
     *
     * @return mixed the prepared assotiative array to encode
     * @throws Exception\SpecException
     */
    protected static function prepareRequest($spec, $id, $method, $params=null) {
        switch($spec) {
        case Tivoka::SPEC_2_0:
            $request = array(
                    'jsonrpc' => '2.0',
                    'method' => $method,
            );
            if($id !== null) $request['id'] = $id;
            if($params !== null) $request['params'] = $params;
            return $request;
        case Tivoka::SPEC_1_0:
            $request = array(
                'method' => $method,
                'id' => $id
            );
            if($params !== null) {
                if((bool)count(array_filter(array_keys($params), 'is_string'))) throw new Exception\SpecException('JSON-RPC 1.0 doesn\'t allow named parameters');
                $request['params'] = $params;
            }
            return $request;
        }
    }
    
    /**
    * @return string A v4 uuid
    */
    public static function uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), // time_low
        mt_rand(0, 0xffff), // time_mid
        mt_rand(0, 0x0fff) | 0x4000, // time_hi_and_version
        mt_rand(0, 0x3fff) | 0x8000, // clk_seq_hi_res/clk_seq_low
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) // node
        );
    }

    /**
     * @return string[]
     */
    private function getEncodeList()
    {
        return array(
            'UCS-4*',
            'UCS-4BE',
            'UCS-4LE*',
            'UCS-2',
            'UCS-2BE',
            'UCS-2LE',
            'UTF-32*',
            'UTF-32BE*',
            'UTF-32LE*',
            'UTF-16*',
            'UTF-16BE*',
            'UTF-16LE*',
            'UTF-7',
            'UTF7-IMAP',
            'UTF-8*',
            'ASCII*',
            'EUC-JP*',
            'SJIS*',
            'eucJP-win*',
            'SJIS-win*',
            'ISO-2022-JP',
            'ISO-2022-JP-MS',
            'CP932',
            'CP51932',
            'SJIS-mac** (alias: MacJapanese)',
            'SJIS-Mobile#DOCOMO** (alias: SJIS-DOCOMO)',
            'SJIS-Mobile#KDDI** (alias: SJIS-KDDI)',
            'SJIS-Mobile#SOFTBANK** (alias: SJIS-SOFTBANK)',
            'UTF-8-Mobile#DOCOMO** (alias: UTF-8-DOCOMO)',
            'UTF-8-Mobile#KDDI-A**',
            'UTF-8-Mobile#KDDI-B** (alias: UTF-8-KDDI)',
            'UTF-8-Mobile#SOFTBANK** (alias: UTF-8-SOFTBANK)',
            'ISO-2022-JP-MOBILE#KDDI** (alias: ISO-2022-JP-KDDI)',
            'JIS',
            'JIS-ms',
            'CP50220',
            'CP50220raw',
            'CP50221',
            'CP50222',
            'ISO-8859-1*',
            'ISO-8859-2*',
            'ISO-8859-3*',
            'ISO-8859-4*',
            'ISO-8859-5*',
            'ISO-8859-6*',
            'ISO-8859-7*',
            'ISO-8859-8*',
            'ISO-8859-9*',
            'ISO-8859-10*',
            'ISO-8859-13*',
            'ISO-8859-14*',
            'ISO-8859-15*',
            'byte2be',
            'byte2le',
            'byte4be',
            'byte4le',
            'BASE64',
            'HTML-ENTITIES',
            '7bit',
            '8bit',
            'EUC-CN*',
            'CP936',
            'GB18030**',
            'HZ',
            'EUC-TW*',
            'CP950',
            'BIG-5*',
            'EUC-KR*',
            'UHC (CP949)',
            'ISO-2022-KR',
            'Windows-1251 (CP1251)',
            'Windows-1252 (CP1252)',
            'CP866 (IBM866)',
            'KOI8-R*'
        );
    }
}
?>
