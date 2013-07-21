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

use Unity\Component\Service\ServiceManager;

use Unity\Component\Service\Service;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Invoker extends Service
{
    /**
     * @var \Unity\Component\Service\ServiceManager
     */
    private $service_manager;

    public function __construct()
    {
        $this->setName('invoker')
             ->addDependency('service-manager');
    }

    protected function configure(ServiceManager $sm)
    {
        $this->service_manager = $sm;
    }

    /**
     * Invokes the given callable.
     *
     * @param callable $callable
     * @param array $named_args
     * @throws \RuntimeException
     * @return mixed
     */
    public function invoke($callable, $named_args = array())
    {
        $reflector = $this->getReflector($callable);
        $arguments = array();

        foreach ($reflector->getParameters() as $param) {
            if (null !== ($value = $this->getArgumentValue($param))) {
                $arguments[] = $value;
            } elseif (isset($named_args[$param->getName()])) {
                $arguments[] = $named_args[$param->getName()];
            } elseif ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            } elseif ($param->getClass()) {
                foreach($named_args as $index => $value) {
                    if(is_object($value) &&
                       $param->getClass()->getName() == get_class($value)) {
                        $arguments[] = $value;
                    }
                }
            } else {
                throw new \RuntimeException('Unable to hydrate argument "$' .
                                             $param->getName() . '".');
            }
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Find an argument value based on the given ReflectionParameter.
     *
     * @param \ReflectionParameter $param
     * @return Service|null
     */
    private function getArgumentValue(\ReflectionParameter $param)
    {
        $services = $this->service_manager->getContainer()->toArray();
        $slug_cls = str_replace('_', '-', $param->getName());

        foreach($services as $name => $service) {
          if($param->getClass() && $param->getClass()->getName() == get_class($service)) {
              return $service;
          } elseif (isset($services[$slug_cls])) {
              return $services[$slug_cls];
          }
        }
    }

    /**
     * @return \Reflector
     */
    private function getReflector($callable)
    {
        if (is_array($callable) && sizeof($callable) === 2) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }
        if (is_string($callable) && function_exists($callable)) {
            return new \ReflectionFunction($callable);
        }
        if (is_object($callable) && $callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }
        throw new \InvalidArgumentException('Unable to invoke a non-callable argument.');
    }
}