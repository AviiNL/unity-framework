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
namespace Unity\Components\Service;

use Unity\Components\Container\Container;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class ServiceManager extends Service
{
    private static $__instance = null;
    private $load_order        = array(),
            $services          = array(),
            $resolve_data      = array(),
            $is_booted         = false;

    /**
     * @var \Unity\Components\Container\Container
     */
    private $container          = null;

    /**
     * Constructor
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        // Prevent instance duplication.
        if (self::$__instance instanceof self) {
            throw new \RuntimeException(
                'Another instance of ' . __CLASS__ . ' already exists!');
        }
        self::$__instance = $this;

        $this->container = new Container();
        $this->setName('service-manager');

        // Register self
        $this->register($this);
    }

    /**
     * @return \Unity\Components\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Registers a service.
     *
     * @param Service $service
     */
    public function register(Service $service)
    {
        if (!$this->is_booted) {
            $this->services[$service->getServiceName()] = $service;
        } else {
            $this->services[$service->getServiceName()] = $service;
            $this->resolve_data[$service->getServiceName()] = array(
                'dependency_list' => $service->getServiceDependencies(),
                'recursion_level' => 0,
                'is_loaded'       => false
            );
            $this->resolveDependency($service->getServiceName());
            if($this->resolve_data[$service->getServiceName()]['is_loaded']) {
                $this->container->set($service->getServiceName(), $service);
            }else{
                throw new \RuntimeException(
                    'Service "' . $service->getServiceName() . '" was not loaded due to unresolvable dependencies.');
            }
        }
    }

    /**
     * Boots all services in the order based on the dependencies they rely on.
     */
    public function load()
    {
        foreach ($this->services as $service) {
            $this->resolve_data[$service->getServiceName()] = array(
                'dependency_list' => $service->getServiceDependencies(),
                'recursion_level' => 0,
                'is_loaded'       => false
            );
        }
        foreach($this->resolve_data as $name => $data) {
            $this->resolveDependency($name);
        }
        // Separate the 2 loops due to alteration of the array by the
        // dependency resolver.
        foreach($this->resolve_data as $name => $data) {
            if($data['is_loaded']) {
                $this->container->set($name, $this->services[$name]);
            }
        }
        $this->is_booted = true;
    }

    /**
     * Resolve dependencies between services.
     *
     * @param string $name
     * @throws \RuntimeException
     * @throws DepedencyNotFoundException
     */
    private function resolveDependency($name)
    {
        if ($this->resolve_data[$name]['is_loaded']) {
            return;
        }
        // No dependencies, just boot it...
        if(empty($this->resolve_data[$name]['dependency_list'])) {
            $this->startService($this->services[$name]);
            return;
        }
        $this->resolve_data[$name]['recursion_level']++;
        foreach ($this->resolve_data[$name]['dependency_list'] as $dependency) {
            if ($dependency == $name) {
                throw new \RuntimeException('Service ' . $name . ' tries to add a dependency on itself.');
            }

            if (!isset($this->resolve_data[$dependency])) {
                throw new DepedencyNotFoundException($name, $dependency);
            }
            // check for bi-directional dependency
            if (in_array($name, $this->resolve_data[$dependency]['dependency_list'])) {
                throw new \RuntimeException(
                    'Service "' . $name . '" requires "' . $dependency . '" but the dependency "'
                    . $dependency . '" requires "' . $name . '" as well.');
            }
            if($this->resolve_data[$dependency]['recursion_level'] > 1) {
                continue;
            }
            if (!$this->resolve_data[$dependency]['is_loaded']) {
                $this->resolveDependency($dependency);
            }
        }
        if(!$this->resolve_data[$name]['is_loaded']) {
            $this->startService($this->services[$name]);
        }
    }

    private function startService(Service $service)
    {
        $this->container->set($service->getServiceName(), $service);
        $service->setContainer($this->container);
        $service->boot();
        $this->resolve_data[$service->getServiceName()]['is_loaded'] = true;
    }
}