<?php
/**
 * @section LICENSE
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Bryan Davis and Wikimedia Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including without
 * limitation the rights to use, copy, modify, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to
 * whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * @file
 */

namespace Monolog\Handler\Udp2log;


/**
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis and Wikimedia Foundation.
 */
class StreamWriter implements Writer
{
    /**
     * @var resource $fhandle
     */
    protected $fhandle;


    /**
     * @param string $stream
     */
    public function __construct($stream)
    {
        $this->fhandle = fopen($stream, 'a');
    }


    /**
     * @param string $message
     * @param string $prefix Message prefix
     */
    public function write($message, $prefix = null)
    {
        if ($prefix !== null) {
            $message = "{$prefix} {$message}";
        }
        fwrite($this->fhandle, $message);
    }


    public function close()
    {
        if ($this->fhandle !== null) {
            fclose($this->fhandle);
        }
    }
}
