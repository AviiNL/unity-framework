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
namespace Unity\Component\Annotation;

use Unity\Component\Service\Service;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class AnnotationReader extends Service
{
    private $cache      = array();
    private $debug_mode = false;

    public function __construct()
    {
        $this->setName('annotation-reader');
    }

    /**
     * Returns an AnnotationClass object for the given class.
     *
     * @param mixed $class
     */
    public function getClass($class)
    {
        if (is_object($class)) $class = get_class($class);
        if (isset($this->cache[$class])) return $this->cache[$class];
        $reflector = new \ReflectionClass($class);
        return ($this->cache[$class] = new AnnotationCollection(new Parser($reflector)));
    }

    public function getMethod($class, $method)
    {
        return $this->getClass($class)->getMethod($method);
    }
}