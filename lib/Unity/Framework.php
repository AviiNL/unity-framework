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

use Unity\Components\Kernel\Invoker;

use Unity\Components\Event\EventManager;

use Unity\Components\Kernel\Kernel;
use Unity\Components\Container\Container;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Framework
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
    public function __construct(Kernel $kernel, $debug_mode = false)
    {
        if ($this->is_booted) return;

        $this->is_debug_mode  = (bool)$debug_mode;
        $this->kernel         = $kernel;
        $this->parameters     = new Container();
        $this->environment    = $this->getEnvironment();

        if ($debug_mode) {
            $this->debug_time_start = microtime(true);
        }

        self::$__instance = $this;
    }

    /**
     * @return Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Boots the framework.
     */
    public function boot()
    {
        if ($this->is_booted) return;
        $this->is_booted = true;

        // Load services
        $this->loadDefaultServices();

        // Resolve service dependencies & boot.
        $this->getKernel()->getServiceManager()->load();
    }

    /**
     * Returns the instantiated object of this class.
     *
     * @return \Unity\Framework
     */
    public static function getInstance()
    {
        return self::$__instance;
    }

    /**
     * Returns true if the framework is currently running in debug-mode.
     *
     * @return bool
     */
    public function isDebug()
    {
        return (bool)$this->debug_mode;
    }

    /**
     * Returns the environment the framework is currently running in. This
     * method can return one of the following values: WEB, XHR or CLI.
     *
     * @return string
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            if (php_sapi_name() === 'cli') {
                $this->environment = self::ENV_CLI;
            }elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $this->environment = self::ENV_XHR;
            }else{
                $this->environment = self::ENV_WEB;
            }
        }
        return $this->environment;
    }

    /**
     * Loads a set of default services which are required by the framework.
     */
    private function loadDefaultServices()
    {
        $sm = $this->getKernel()->getServiceManager();
        $sm->register(new EventManager());
        $sm->register(new Invoker());
    }
}