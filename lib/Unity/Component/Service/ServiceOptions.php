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
namespace Unity\Component\Service;

use Unity\Component\Parameter\Parameters;

use Unity\Component\Container\Container;
use Unity\Component\Parameter\ParameterNotFoundException;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class ServiceOptions
{
    /**
     * Parameter Container
     *
     * @var Container
     */
    private $container;

    /**
     * Prefix for parameters, e.g. a service name.
     *
     * @var string
     */
    private $parameter_prefix;

    /**
     * @param Container $container
     */
    public function __construct(Parameters $parameters = null)
    {
        $this->parameters = $parameters;
    }

    /**
     * Declares the given option name as 'mandatory'. An exception will
     * be thrown with the given description if the option is not registered.
     *
     * @param string $name
     * @param string $description
     * @throws OptionNotFoundException
     */
    public function addRequiredOption($name, $description)
    {
        if (null === $this->parameters->getParameter($this->getParamName($name), null)) {
            throw new OptionNotFoundException($this->getParamName($name), true, $description);
        }
    }

    /**
     * Adds an option. If the option is not registered, the given default
     * value will be set in the paramter container instead to ensure the
     * availability of the option.
     *
     * @param string $name
     * @param mixed $default_value
     */
    public function addOption($name, $default_value = null)
    {
        if (null === $this->parameters->getParameter($this->getParamName($name), null)) {
            $this->parameters->setParameter($this->getParamName($name), $default_value);
        }
    }

    /**
     * Returns an option value.
     *
     * @param string $name
     * @throws ParameterNotFoundException
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->parameters->getParameter($this->getParamName($name));
    }

    /**
     * @param string $name
     */
    public function setOptionPrefix($name)
    {
        $this->parameter_prefix = trim($name, '.');
    }

    /**
     * Returns the actual parameter name with prefixed name if it's being used.
     *
     * @param string $name
     * @return string
     */
    private function getParamName($name)
    {
        if ($name[0] == '~') {
            return ltrim($name, '~');
        }
        return $this->parameter_prefix ? $this->parameter_prefix . '.' . $name : $name;
    }
}