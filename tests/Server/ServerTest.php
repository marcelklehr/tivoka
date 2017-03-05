<?php

namespace tests\Server;

use PHPUnit_Framework_TestCase;
use Tivoka\Server\MethodWrapper;
use Tivoka\Server\Server;

/**
 * Class Server
 * @package tests\Server
 */
class ServerTest extends PHPUnit_Framework_TestCase
{
    public function test_construct_HostIsInstanceOfMethodWrapper()
    {
        $mw = new MethodWrapper();
        $server = new Server($mw);

        self::assertEquals($mw, $server->host);
    }

    public function test_construct_HostIsArray()
    {
        $foo = new FooServer();

        $methods = array(
            'method' => function() {}, // anonymous function
            'baz' => array($foo, 'baz'), // Method of class
        );
        $server = new Server($methods);

        self::assertInstanceOf('\\Tivoka\\Server\\MethodWrapper', $server->host);

        self::assertTrue($server->host->exist('method'));
        self::assertTrue($server->host->exist('baz'));
    }

    /**
     * @expectedException \Tivoka\Exception\Exception
     * @expectedExceptionMessageRegExp /Given value for "\w+" is no valid callback./
     */
    public function test_construct_HostIsArray_InvalidCallback()
    {
        new Server(array(
            'method1' => 'callback',
        ));
    }
}

class FooServer
{
    public function baz() {}
}
