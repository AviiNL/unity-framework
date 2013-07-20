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
namespace Unity\Components\Controller;

use Unity\Components\Service\Service;

use Unity\Framework;

use Unity\Components\Kernel\IController;

/**
 * Basic controller, no templating, just printing whatever the controller method
 * returns or does.
 *
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class BasicController implements IController
{
    private $initialized = false,
            $services    = null,
            $parameters  = null;

    /**
     * Renders the controller method.
     *
     * @param unknown_type $method
     * @param unknown_type $args
     */
    final public function render($method, array $args = array())
    {
        $this->initialize();
        if(null === ($invoker = $this->getService('invoker'))) {
            throw new \RuntimeException('Invoker-service not available.');
        }
        print_r($invoker->invoke(array($this, $method), $args));
    }

    /**
     * @param string $service
     * @return \Unity\Components\Service\Service
     */
    final protected function getService($service)
    {
        return $this->services->getContainer()->get($service);
    }

    /**
     * Returns a parameter or $default if it doesn't exist.
     *
     * @param string $parameter
     * @param mixed $default
     */
    final protected function getParameter($parameter, $default = null)
    {
        return $this->parameters->get($parameter, $default);
    }

    /**
     * Initializes the controller. This method should be called before executing
     * the designated method inside the controller to ensure the services and
     * parameters are available.
     */
    final protected function initialize()
    {
        if($this->initialized) return;
        $this->initialized = true;
        $this->services    = Framework::getInstance()->getKernel()
                                                     ->getServiceManager();
        $this->parameters  = Framework::getInstance()->getParameters();
    }


}