<?php
/**
 * Tivoka - JSON-RPC done right!
 * Copyright (c) 2011-2013 by Marcel Klehr <mklehr@gmx.net>
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
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright (c) 2013, Marcel Klehr
 */

namespace Tivoka\Encoder;

use Tivoka\Encoder\Exception\EncoderException;

/**
 * Simple JSON encoder/decoder handling.
 * @package Tivoka
 */
class JsonEncoder implements EncoderInterface
{
    /**
     * List of known JSON serialization errors.
     * @var string[]
     */
    protected static $json_errors = array(
        JSON_ERROR_NONE => '',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
    );

    /**
     * Encodes data to JSON.
     *
     * @param mixed $data Any serializable data.
     * @return string JSON string.
     * @throws EncoderException When serialization fails.
     */
    public function encode($data)
    {
        $json = json_encode($data);

        if (!is_string($json)) {
            throw new EncoderException(
                'Could not encode data of type ' .
                (is_object($data) ? get_class($data) : gettype($data)) .
                ' to JSON: ' .
                static::$json_errors[json_last_error()]
            );
        }

        return $json;
    }

    /**
     * Decodes JSON to data.
     *
     * @param string $json JSON string.
     * @return mixed Unserialized data.
     * @throws EncoderException When serialization fails.
     */
    public function decode($json)
    {
        $data = json_decode($json, true);

        if (null === $data) {
            throw new EncoderException('JSON parse error: ' . static::$json_errors[json_last_error()]);
        }

        return $data;
    }
}
