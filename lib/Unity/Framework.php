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
namespace Unity;

use Unity\Components\Kernel\IBundle;
use Unity\Components\Kernel\Dispatcher;
use Unity\Components\Kernel\Kernel;
use Unity\Components\Kernel\Invoker;
use Unity\Components\HTTP\Request;
use Unity\Components\Event\EventManager;
use Unity\Components\Container\Container;
use Unity\Components\Annotation\AnnotationReader;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class Framework
{
    const   ENV_CLI = 'CLI',
            ENV_XHR = 'XHR',
            ENV_WEB = 'WEB';

    private static $__instance;

    private $kernel,
            $parameters,
            $environment,
            $is_debug_mode,
            $is_booted,
            $debug_time_start;

    /**
     * @param Kernel $kernel
     * @param bool $debug_mode
     */
    final public function __construct($debug_mode = false)
    {
        if ($this->is_booted) return;

        $this->is_debug_mode  = (bool)$debug_mode;
        $this->kernel         = new Kernel();
        $this->parameters     = new Container();

        if ($debug_mode) {
            $this->debug_time_start = microtime(true);
        }

        self::$__instance = $this;

        $this->getKernel()->loadBundle($this->registry());

        if ($this->is_booted) return;
        $this->is_booted = true;

        // Load services
        $this->loadDefaultServices();

        // Resolve service dependencies & boot.
        $this->getKernel()->getServiceManager()->load();
    }

    /**
     * Returns an array of object instances of Controllers, Services and Events
     * to register for the framework.
     *
     * @return array
     */
    abstract protected function registry();

    public function getService($service)
    {
        return $this->kernel->getServiceManager()->getContainer()->get($service);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return Kernel
     */
    final public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the instantiated object of this class.
     *
     * @return \Unity\Framework
     */
    final public static function getInstance()
    {
        return self::$__instance;
    }

    /**
     * Returns true if the framework is currently running in debug-mode.
     *
     * @return bool
     */
    final public function isDebug()
    {
        return (bool)$this->debug_mode;
    }

    /**
     * Loads a set of default services which are required by the framework.
     */
    private function loadDefaultServices()
    {
        $sm = $this->getKernel()->getServiceManager();
        $sm->register(new Invoker());
        $sm->register(new AnnotationReader());
        $sm->register(new Request());
        $sm->register(new Dispatcher());
    }
}