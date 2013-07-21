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
namespace Unity\Component\Kernel;

use Unity\Component\Parameter\Parameters;

use Unity\Component\Container\Container;

interface IController
{
    /**
     * Renders the controller method, providing $args to the method using named
     * arguments.
     *
     * @param unknown_type $method
     * @param array $args
     */
    public function render($method, array $args);

    /**
     * Defines the Container object for this controller. Once a container has
     * been set, it can not be modified again.
     *
     * @param Container $container
     */
    public function setContainer(Container $container);

    /**
     * Defines the Parameters container for this controller. Once it has been
     * defined, i can not be changed.
     *
     * @param Parameters $parameters
     */
    public function setParameters(Parameters $parameters);

}