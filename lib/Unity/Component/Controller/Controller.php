<?php
/*
 * UnityPHP - Modular Application Framework Copyright (c) 2013, Harold Iedema ------------------------------------------------------------------------------- Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions: The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. -------------------------------------------------------------------------------
 */
namespace Unity\Component\Controller;

use Unity\Component\Kernel\IController;
use Unity\Component\Container\Container;
use Unity\Component\Service\ServiceNotFoundException;
use Unity\Component\Parameter\Parameters;
use Unity\Component\Parameter\ParameterNotFoundException;

/**
 * Basic controller, no templating, just printing whatever the controller method
 * returns or does.
 *
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class Controller implements IController
{
    private $container  = null,
            $parameters = null,
            $directory  = null;

    /**
     * Returns the directory where this controller resides.
     *
     * @return string
     */
    final protected function getPath()
    {
        if (! $this->directory) {
            $reflector = new \ReflectionClass(get_called_class());
            $this->directory = dirname($reflector->getFileName());
        }
        return $this->directory;
    }

    /**
     * Returns the Request service.
     *
     * @return \Unity\Component\HTTP\Request
     */
    final protected function getRequest()
    {
        return $this->get('request');
    }

    /**
     * @see \Unity\Component\Kernel\IController::render()
     */
    public function render($method, array $args = array())
    {
        if (null === ($invoker = $this->get('invoker'))) {
            throw new ServiceNotFoundException('invoker');
        }
        $result = $invoker->invoke(array($this, $method), $args);
    }

    /**
     * @see \Unity\Component\Kernel\IController::setContainer()
     */
    final public function setContainer(Container $container)
    {
        if ($this->container) {
            throw new \BadMethodCallException('Unable to alter a previously set container.');
        }
        $this->container = $container;
    }

    /**
     * @see \Unity\Component\Kernel\IController::setParameters()
     */
    final public function setParameters(Parameters $parameters)
    {
        if ($this->parameters) {
            throw new \BadMethodCallException('Unable to alter a previously set parameters-container.');
        }
        $this->parameters = $parameters;
    }

    /**
     * Returns a service by the given name.
     *
     * @param string $service
     */
    final protected function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * Returns a parameter by the given name.
     * If the parameter does not exist,
     * the $default value is returned. If this is not specified, an exception
     * will be thrown of type ParameterNotFoundException.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final protected function getParameter($key, $default = null)
    {
        $value = $this->parameters->getParameter($key, $default);
        if (sizeof(func_get_args()) == 1 && $value === null) {
            throw new ParameterNotFoundException($key);
        }
        return $value;
    }
}