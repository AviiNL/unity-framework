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

use Unity\Component\Kernel\IService;
use Unity\Component\Container\Container;
use Unity\Component\Parameter\ParameterNotFoundException;

/**
 * A service represents a class that's instantiated once directly at framework
 * boot and is accessible via the service container through the Framework
 * instance.
 *
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class Service implements IService
{
    private $name         = null,
            $dependencies = array(),
            $is_booted    = false;
    /**
     * @var \Unity\Component\Container\Container
     */
    private $container    = null;

    /**
     * @var \Unity\Component\Service\ServiceOptions
     */
    private $options      = null;

    /**
     * Defines the name of this service. A service name should be classified as
     * a 'slug', consisting only of lower-case characters, numerals and a dash.
     *
     * If an invalid name is given, a InvalidServiceNameException is thrown and
     * a suggestion of a proper name is given in the message.
     *
     * @param string $name
     * @throws \InvalidArgumentException
     * @throws InvalidServiceNameException
     * @return \Unity\Component\Service\Service
     */
    final protected function setName($name)
    {
        if (!$this->container instanceof Container) {
            $this->container = new Container();
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Service name cannot be empty.');
        }
        if ($name !== ($slug = $this->slugify($name))) {
            throw new InvalidServiceNameException($name, $slug);
        }

        $this->options = new ServiceOptions($this->container->get('parameters'));

        $this->name = $name;
        return $this;
    }

    /**
     * Returns an option value.
     *
     * @param string $name
     * @throws ParameterNotFoundException
     * @return mixed
     */
    final protected function getOption($name)
    {
        return $this->options->getOption($name);
    }

    /**
     * Declares the given option name as 'mandatory'. An exception will
     * be thrown with the given description if the option is not registered.
     *
     * @param string $name
     * @param string $description
     * @throws OptionNotFoundException
     * @return \Unity\Component\Service\Service
     */
    final protected function addRequiredOption($name, $description)
    {
        $this->options->addRequiredOption($name, $description);
        return $this;
    }

    /**
     * @param string $prefix
     * @return \Unity\Component\Service\Service
     */
    final protected function setOptionPrefix($prefix)
    {
        $this->options->setOptionPrefix($prefix);
        return $this;
    }

    /**
     * Adds an option. If the option is not registered, the given default
     * value will be set in the paramter container instead to ensure the
     * availability of the option.
     *
     * @param string $name
     * @param mixed $default_value
     * @return \Unity\Component\Service\Service
     */
    final protected function addOption($name, $default_value)
    {
        $this->options->addOption($name, $default_value);
        return $this;
    }

    /**
     * Sets the container for this service. This can only be done before the
     * service has booted.
     *
     * @param Container $container
     * @throws \RuntimeException
     */
    final public function setContainer(Container $container)
    {
        if ($this->is_booted) {
            throw new \RuntimeException(
                'Unable to alter container after service is booted.');
        }
        $this->container = $container;
    }

    /**
     * @param string $service_id
     * @throws ServiceNotFoundException
     * @return Service
     */
    public function getService($service_id)
    {
        if (null !== ($service = $this->container->get($service_id))) {
            return $service;
        }
        throw new ServiceNotFoundException($service_id);
    }

    /**
     * Adds a dependency on another service, meaning that this service will not
     * be initialized until the given service is available and loaded.
     *
     * @param string $name
     * @throws InvalidServiceNameException
     * @return \Unity\Component\Service\Service
     */
    final protected function addDependency($name)
    {
        if ($name !== ($slug = $this->slugify($name))) {
          throw new InvalidServiceNameException($name, $slug);
        }
        $this->dependencies[] = $name;
        return $this;
    }

    /**
     * Returns a list of dependencies.
     *
     * @return array
     */
    final public function getServiceDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Returns the name of this service.
     *
     * @return string
     */
    final public function getServiceName()
    {
        return $this->name;
    }

    final public function boot()
    {
        if ($this->is_booted) return;
        if (empty($this->dependencies) && !method_exists($this, 'configure')) {
          return;
        }
        if (!method_exists($this, 'configure')) {
          throw new ServiceWithoutConfigureMethodException(
              'Service ' . $this->name . ' has dependencies and therefore must have a "configure" method.');
        }

        $this->options = new ServiceOptions($this->container->get('parameters'));

        $args      = array();
        $services  = $this->container->toArray();
        $reflector = new \ReflectionMethod($this, 'configure');

        foreach($reflector->getParameters() as $param) {
            // find by prefixed class
            /* @var $param \ReflectionParameter */
            $name = str_replace('_', '-', $param->getName());
            if ($param->getClass()) {
                $found = false;
                foreach($services as $name => $service) {
                    if(get_class($service) == $param->getClass()->getName()) {
                        $args[] = $service;
                        $found = true;
                    }
                }
                if (!$found) {
                    throw new \RuntimeException(
                        'Servce ' . $service->getServiceName() . ' requires ' .
                        $param->getClass()->getName() . ', but this class is not a service.');
                }
            } elseif (isset($services[$name])) {
                $args[] = $services[$name];
            } elseif ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    'Unable to hydate argument "$' . $param->getName() . '" for service '
                    . $this->getServiceName());
            }
        }

        call_user_func_array(array($this, 'configure'), $args);

        $this->is_booted = true;
    }

    /**
     * Creates a slug based on the given $text. Returns a slugified string or
     * false on failure.
     *
     * @param string $text
     * @return boolean|string
     */
    private function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return false;
        }
        return $text;
    }
}