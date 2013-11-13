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

use Unity\Component\Security\UserManager;

use Unity\Component\Security\Firewall;

use Unity\Component\Security\Session;

use Unity\Component\Security\SessionManager;

use Unity\Component\Cache\FileCacheService;

require_once __DIR__ . '/Utilities.php';

use Unity\Component\Kernel\Dispatcher;
use Unity\Component\Kernel\Kernel;
use Unity\Component\Kernel\Invoker;
use Unity\Component\Kernel\FileNotFoundException;
use Unity\Component\Yaml\YamlService;
use Unity\Component\HTTP\Request;
use Unity\Component\Parameter\Parameters;
use Unity\Component\Annotation\AnnotationReader;
use Unity\Component\Service\ServiceNotFoundException;
use Unity\Component\Kernel\Locator;

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
            $debug_time_start,
            $app_root_dir;

    /**
     * @param Kernel $kernel
     * @param bool $debug_mode
     */
    final public function __construct($debug_mode = false)
    {
        $this->is_debug_mode  = (bool)$debug_mode;
        $yaml_service         = new YamlService();
        $this->kernel         = new Kernel($this);
        $this->parameters     = new Parameters($yaml_service);

        $this->kernel->getServiceManager()->register($this->parameters);
        $this->kernel->getServiceManager()->register($yaml_service);
        $this->kernel->getServiceManager()->register(new SessionManager());
        $this->kernel->getServiceManager()->register(new Session());
        $this->kernel->getServiceManager()->register(new Firewall());
        $this->kernel->getServiceManager()->register(new UserManager());

        $reflector = new \ReflectionObject($this);
        $this->app_root_dir = dirname($reflector->getFileName());
        $this->parameters->setParameter('app_root_dir', $this->app_root_dir);

        if ($debug_mode) {
            $this->debug_time_start = microtime(true);
        }

        self::$__instance = $this;
        $this->initialize();
    }

    private function initialize()
    {
        if ($this->is_booted) return
        $this->is_booted = true;

        $this->loadDefaultServices();

        if(!method_exists($this, 'boot')) {
            throw new \RuntimeException('Application class should implement the protected method "boot".');
        }

        $this->boot();
        $this->kernel->getServiceManager()->load();
    }

    /**
     * Method executed when a 404 occurs. The Application class should override
     * this method.
     */
    public function execute404()
    {
        require __DIR__ . '/Resources/404.html';
    }

    /**
     * Registers one or more objects. All objects should be an instance of the
     * implementation of: IBundle, IController, IEvent or IService.
     *
     * You can pass an array to this method to register more objects at once.
     *
     * @param mixed $object
     * @return self
     */
    final protected function register($objects)
    {
        $this->kernel->register($objects);
    }

    /**
     * Loads parameters from the given YAML file.
     *
     * @param string $yaml_file
     */
    final protected function loadParameters($yaml_file)
    {
        if (!file_exists($yaml_file)) {
            throw new FileNotFoundException($yaml_file);
        }
        $this->parameters->loadFromYAML($yaml_file);
    }

    /**
     * Registers objects from a YAML file.
     *
     * @param unknown_type $filename
     * @throws FileNotFoundException
     */
    final protected function registerFromYAML($filename)
    {
        $this->kernel->registerFromYAML($filename);
    }

    /**
     * Returns a service instance.
     *
     * @param unknown_type $service
     * @return Ambigous <\Unity\Component\Container\mixed, string>
     */
    public function getService($service)
    {
        if (null === ($object = $this->kernel->getServiceManager()->getContainer()->get($service))) {
            throw new ServiceNotFoundException($service);
        }
        return $object;
    }

    /**
     * @return \Unity\Component\Parameter\Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
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
        $sm = $this->kernel->getServiceManager();
        $sm->register(new Invoker());
        $sm->register(new Locator());
        $sm->register(new AnnotationReader());
        $sm->register(new Request());
        $sm->register(new Dispatcher());
        $sm->register(new FileCacheService());
    }
}