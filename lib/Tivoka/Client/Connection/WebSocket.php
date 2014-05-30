<?php

namespace Tivoka\Client\Connection;
use Tivoka\Client\BatchRequest;
use Tivoka\Exception;
use Tivoka\Client\Request;

use WebSocket\Client;

/**
 * WebSocket connection
 * @package Tivoka
 *
 * @author Fredrik Liljegren <fredrik.liljegren@textalk.se>
 *
 * The WebSocket itself is handled by textalk/websocket package.
 */
class WebSocket extends AbstractConnection {
    protected $url, ///< Given URL
        $ws_client; ///< The WebSocket\Client instance

    /**
     * Constructs connection.
     *
     * @param string $host Server host.
     * @param int $port Server port.
     */
    public function __construct($url)
    {
        $this->url       = $url;
        $this->ws_client = new Client($url, array('timeout' => $this->timeout));
    }

    /**
     * Changes timeout.
     * @param int $timeout
     * @return WebSocket Self reference.
     */
    public function setTimeout($timeout)
    {
        $this->ws_client->setTimeout($timeout);
        return parent::setTimeout($timeout);
    }

    /**
     * Sends a JSON-RPC request over plain WebSocket.
     * @param Request $request,... A Tivoka request.
     * @return Request|BatchRequest If sent as a batch request the BatchRequest object will be returned.
     */
    public function send(Request $request)
    {
        if (func_num_args() > 1)   $request = func_get_args();
        if (is_array($request))    $request = new BatchRequest($request);

        if (!($request instanceof Request)) {
            throw new Exception\Exception('Invalid data type to be sent to server');
        }

        // sending request
        $this->ws_client->send($request->getRequest($this->spec), 'text', true);

        // read server respons
        $response = $this->ws_client->receive();

        if (($opcode = $this->ws_client->getLastOpcode()) !== 'text') {
            throw new Exception\ConnectionException(
                "Received non-text frame of type '$opcode' with text: " . $response
            );
        }
        $request->setResponse($response);
        return $request;
    }
}
