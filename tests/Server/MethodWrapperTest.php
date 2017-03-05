<?php

namespace tests\Server;

use PHPUnit_Framework_TestCase;
use Tivoka\Server\MethodWrapper;

/**
 * Class MethodWrapper
 * @package tests\Server
 */
class MethodWrapperTest extends PHPUnit_Framework_TestCase
{
    const METHOD = 'testMethod';

    /**
     * @var MethodWrapper
     */
    protected $methodWrapper;

    public function setUp()
    {
        parent::setUp();

        $this->methodWrapper = new MethodWrapper();
    }

    public function test_RegisterSuccess()
    {
        self::assertTrue($this->methodWrapper->register(self::METHOD, function() {}));
        self::assertTrue($this->methodWrapper->exist(self::METHOD));
    }

    public function test_RegisterSuccessObjectMethod()
    {
        $foo = new Foo();

        self::assertTrue($this->methodWrapper->register(self::METHOD, array($foo, 'bar')));
        self::assertTrue($this->methodWrapper->exist(self::METHOD));
    }

    public function test_RegisterFail()
    {
        self::assertFalse($this->methodWrapper->register(self::METHOD, 'callback'));
    }

    public function test_Register_OverrideExistsMethod()
    {
        self::assertTrue($this->methodWrapper->register(self::METHOD, function() {
            return 'method1';
        }));
        self::assertEquals('method1', $this->methodWrapper->call(self::METHOD));

        // Override exists method
        self::assertTrue($this->methodWrapper->register(self::METHOD, function() {
            return 'method2';
        }));
        self::assertEquals('method2', $this->methodWrapper->call(self::METHOD));
    }

    public function test_CallWithParams()
    {
        $expected = array(
            'arg1' => 'value1',
            'arg2' => 'value2',
        );

        $this->methodWrapper->register(self::METHOD, function($data) {
            return $data;
        });
        $result = $this->methodWrapper->call(self::METHOD, $expected);

        self::assertEquals($expected, $result);
    }

    /**
     * @expectedException \Tivoka\Exception\ProcedureException
     * @expectedExceptionMessage Method not found
     * @expectedExceptionCode -32601
     */
    public function test_CallUndefinedMethod()
    {
        $this->methodWrapper->call(self::METHOD);
    }

    /**
     * Fixed Issue #49.
     *
     * @see https://github.com/marcelklehr/tivoka/issues/49
     */
    public function test_CallMethodNameRegister()
    {
        $this->methodWrapper->register('register', function() {
            return 'register';
        });
        self::assertEquals('register', $this->methodWrapper->call('register'));
    }

    public function test_CallObjectMethod()
    {
        $foo = new Foo();

        self::assertTrue($this->methodWrapper->register(self::METHOD, array($foo, 'bar')));
        self::assertEquals('Foo::bar', $this->methodWrapper->call(self::METHOD));
    }
}

class Foo
{
    public function bar() {
        return 'Foo::bar';
    }
}
