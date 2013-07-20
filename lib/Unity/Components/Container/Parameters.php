<?php
/*
 UnityPHP - Modular Application Framework
 Copyright (c) 2013, Harold Iedema

 -------------------------------------------------------------------------------

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is furnished
 to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.

 -------------------------------------------------------------------------------
*/
namespace Unity\Components\Container;

use Unity\Components\Service\Service;

/**
 * Configuration Class
 *
 * @author Harold Iedema <harold@iedema.me>
 */
class Parameters extends Service
{
    private $container;

    public function __construct()
    {
        $this->setName('parameters');
        $this->container = new Container();
    }

    /**
     * Returns the specified parameter. If it does not exist, $default is returned.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->container->get($key, $default);
    }

    /**
     * Loads parameter configuration from an INI file.
     *
     * @param string $filename
     */
    public function loadFromINI($filename)
    {
        $this->container->fromINI($filename);
    }

    /**
     * Loads parameter configuration from a JSON file.
     *
     * @param string $filename
     */
    public function loadFromJSON($filename)
    {
        $this->container->fromJSON($filename);
    }
}