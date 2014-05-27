<?php

namespace Tivoka\Client\Connection;
use Tivoka\Client\BatchRequest;
use Tivoka\Exception;
use Tivoka\Client\Request;

/**
 * WebSocket connection
 * @package Tivoka
 *
 * @author Fredrik Liljegren <fredrik.liljegren@textalk.se>
 *
 * Much here is just a modified version of the Tivoka\Client\Connection\Tcp class.
 *
 * The WebSocket handling code (connect, hybi10Encode/Decode) is taken mostly from
 * https://github.com/lemmingzshadow/php-websocket/blob/master/client/lib/class.websocket_client.php
 * Author: Simon Samtleben <web@lemmingzshadow.net>
 * License: DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 */
class WebSocket extends AbstractConnection {
    protected $url, ///< Given URL
        $host,      ///< Host, from URL
        $port,      ///< Port, from URL
        $path,      ///< Path, from URL
        $query,     ///< Query, from URL
        $fragment,  ///< Fragment, from URL
        $socket;    ///< The TCP socket

    /**
     * Constructs connection.
     * @param string $host Server host.
     * @param int $port Server port.
     */
    public function __construct($url)
    {
        $url_parts = parse_url($url);

        //validate url...
        if (!in_array($url_parts['scheme'], array('ws', 'wss'))) {
            throw new Exception\Exception('Not a valid WebSocket url: "' . $url . '".');
        }

        $this->url      = $url;
        $this->scheme   = $url_parts['scheme'];
        $this->host     = $url_parts['host'];
        $this->port     = isset($url_parts['port']) ?
            $url_parts['port'] : ($this->scheme === 'wss' ? 443 : 80);
        $this->path     = $url_parts['path'];
        $this->query    = isset($url_parts['query'])    ? $url_parts['query'] : '';
        $this->fragment = isset($url_parts['fragment']) ? $url_parts['fragment'] : '';
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
     * @return WebSocket Self reference.
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
     * Sends a JSON-RPC request over plain WebSocket.
     * @param Request $request,... A Tivoka request.
     * @return Request|BatchRequest If sent as a batch request the BatchRequest object will be returned.
     */
    public function send(Request $request)
    {
        if (!isset($this->socket)) $this->connect(); // connect on first call
        if (func_num_args() > 1)   $request = func_get_args();
        if (is_array($request))    $request = new BatchRequest($request);

        if (!($request instanceof Request)) {
            throw new Exception\Exception('Invalid data type to be sent to server');
        }

        // sending request
        $res = fwrite(
            $this->socket, $this->_hybi10Encode($request->getRequest($this->spec), 'text', true)
        );
        if ($res === 0 || $res === false) {
            throw new Exception\ConnectionException('Connection to "' . $this->url . '" failed.');
        }

        // read server respons
        $response = $this->receiveData();
        if ($response === false) {
            throw new Exception\ConnectionException('Connection to "' . $this->url . '" failed.');
        }

        $request->setResponse($response);
        return $request;
    }

    /**
     * WebSocket handshake; consists of a HTTP call with an upgrade request.
     */
    private function connect()
    {
        $path_with_query = $this->path;
        if (!empty($this->query))    $path_with_query .= '?' . $this->query;
        if (!empty($this->fragment)) $path_with_query .= '#' . $this->fragment;

        $key     = base64_encode($this->_generateRandomString());
        $header  = "GET " . $path_with_query . " HTTP/1.1\r\n";
        $header .= "Origin: null\r\n";
        $header .= "Host: " . $this->host . "\r\n";
        $header .= "Sec-WebSocket-Key: " . $key . "\r\n";
        $header .= "User-Agent: twapi-php\r\n";
        $header .= "Upgrade: websocket\r\n";
        $header .= "Connection: Upgrade\r\n";
        $header .= "Sec-WebSocket-Version: 8\r\n\r\n";

        $host = ($this->scheme === 'wss' ? 'ssl' : 'tcp') . '://' . $this->host;
        $this->socket = fsockopen($host, $this->port, $errno, $errstr, $this->timeout);

        if ($this->socket === false) {
            throw new Exception\ConnectionException(
                "Could not open socket to \"$host:$this->port\": $errstr ($errno)."
            );
        }

        stream_set_timeout($this->socket, $this->timeout);
        fwrite($this->socket, $header);

        $response = '';
        do {
            $response .= fgets($this->socket);
            $metadata = stream_get_meta_data($this->socket);
        } while (!feof($this->socket) && $metadata['unread_bytes'] > 0);

        if (!preg_match('#Sec-WebSocket-Accept:\s(.*)$#mUi', $response, $matches)) {
            throw new Exception\ConnectionException(
                'Connection to "' . $this->url . '" failed: Server sent invalid upgrade response.'
            );
        }

        $keyAccept = trim($matches[1]);
        $expectedResonse
            = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        if ($keyAccept !== $expectedResonse) {
            throw new Exception\ConnectionException('Server sent bad upgrade response.');
        }
    }

    /**
     * Reads data from socket until there is no more to read.
     */
    private function receiveData() {
        $response = '';
        do {
            $response .= fread($this->socket, 2048);
            $metadata = stream_get_meta_data($this->socket);
        } while (!feof($this->socket) && $metadata['unread_bytes'] > 0);

        $result = $this->_hybi10Decode($response);
        if (!is_array($result)) return false;

        if ($result['type'] === 'text') return $result['payload'];

        // Unexpected type.
        return false;
    }


    private function _generateRandomString($length = 16) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters)-1)];
        }

        // Add numbers
        array_push($useChars, rand(0,9), rand(0,9), rand(0,9));

        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, $length);
        return $randomString;
    }

    private function _hybi10Encode($payload, $type = 'text', $masked = true) {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch($type) {
            case 'text':  $frameHead[0] = 129; break;
            case 'close': $frameHead[0] = 136; break;
            case 'ping':  $frameHead[0] = 137; break;
            case 'pong':  $frameHead[0] = 138; break;
            default:
                /// @todo Use specific exception
                throw new Exception\Exception("Bad type in hybi10Encode: $type");
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;

            for ($i = 0; $i < 8; $i++) $frameHead[$i+2] = bindec($payloadLengthBin[$i]);

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                throw new Exception\Exception("Frame too big.");
                fclose($this->socket);
                return false;
            }
        }
        elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        }
        else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) $frameHead[$i] = chr($frameHead[$i]);

        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) $mask[$i] = chr(rand(0, 255));

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        $framePayload = array();
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    private static function _hybi10Decode($data) {
        $payloadLength = '';
        $mask = '';
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        switch($opcode) {
            case 1:  $decodedData['type'] = 'text';   break;
            case 2:  $decodedData['type'] = 'binary'; break;
            case 8:  $decodedData['type'] = 'close';  break;
            case 9:  $decodedData['type'] = 'ping';   break;
            case 10: $decodedData['type'] = 'pong';   break;

            default:
                return false;
                break;
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength
                = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3])))
                + $payloadOffset;
        }
        elseif($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) $tmp .= sprintf('%08b', ord($data[$i+2]));
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        }
        else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        if ($isMasked === true) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if(isset($data[$i])) $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
            }
            $decodedData['payload'] = $unmaskedPayload;
        }
        else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }
}
