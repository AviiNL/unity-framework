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

class AnnotationClass
{
    /**
     * @var string
     */
    private $class       = null;

    /**
     * @var \ReflectionClass
     */
    private $reflector   = null;

    /**
     * @var array of AnnotationMethod
     */
    private $methods     = null;

    /**
     * @var array of Annotation
     */
    private $annotations = array();

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->reflector = new \ReflectionClass($class);
        $this->annotations = new AnnotationCollection(new Parser($this->reflector));
        $this->annotations->setTargetClass($class)
                          ->prepare();

        foreach($this->reflector->getMethods() as $method) {
            $this->methods[$method->getName()] = new AnnotationMethod($class, $method->getName());
        }
    }
}