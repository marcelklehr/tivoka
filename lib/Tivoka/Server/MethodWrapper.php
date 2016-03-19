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

/**
 * MethodWrapper for implementing anonymous objects on the fly
 * @package Tivoka
 */
class MethodWrapper
{
    /**
     * @var array The list of callbacks
     */
    private $methods = array();

    /**
     * Registers a server method
     *
     * @param string $name The name of the method to provide (already existing methods with the same name will be overridden)
     * @param callable|array $method The callback
     * @returns bool FALSE if no valid callback has been given
     */
    public function register($name, $method)
    {
        if (!is_callable($method)) {
            return FALSE;
        }

        $this->methods[$name] = $method;

        return TRUE;
    }

    /**
     * Returns TRUE if the method with the given name is registered
     *
     * @param string $method The name of the method to check
     *
     * @returns bool
     */
    public function exist($method)
    {
        return isset($this->methods[$method]);
    }

    /**
     * Invokes the requested method
     *
     * @param string $method
     * @param array $params
     *
     * @return mixed
     * @throws Exception\ProcedureException
     */
    public function call($method, array $params = array())
    {
        if (!$this->exist($method)) {
            throw new Exception\ProcedureException('Method not found', -32601);
        }

        return call_user_func($this->methods[$method], $params);
    }
}
?>
