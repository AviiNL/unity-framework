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

use Unity\Component\Bundle\BundleManager;

use Unity\Component\HTTP\Route;

use Unity\Component\HTTP\Request;
use Unity\Component\Service\Service;
use Unity\Component\Service\ServiceManager;
use Unity\Component\Annotation\AnnotationReader;
use Unity\Component\Event\EventManager;
use Unity\Component\Event\EventListener;
use Unity\Framework;

/**
 * The Dispatcher class is responsible for dispatching Controllers based on the
 * given criteria. For example, if the method 'dispatchFromRequest' is called,
 * this class is responsible for iterating through all controllers until one is
 * found containing a method that matches the route from the Request path.
 *
 * @author Harold Iedema <harold@iedema.me>
 */
class Dispatcher extends Service
{
    /**
     * @var \Unity\Component\Kernel\Invoker
     */
    private $invoker;

    /**
     * @var \Unity\Component\Kernel\Kernel
     */
    private $kernel;

    /**
     * @var \Unity\Component\HTTP\Request
     */
    private $request;

    /**
     * @var \Unity\Component\Kernel\BundleManager
     */
    private $bundle_manager;

    /**
     * @var \Unity\Component\Annotation\AnnotationReader
     */
    private $annotation_reader;

    /**
     * @var \Unity\Component\Event\EventManager
     */
    private $em;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('dispatcher')
             ->addDependency('kernel')
             ->addDependency('invoker')
             ->addDependency('bundle-manager')
             ->addDependency('event-manager')
             ->addDependency('annotation-reader')
             ->addDependency('request');
    }

    /**
     * Service Configurator
     *
     * @param BundleManager $bm
     * @param Invoker $invoker
     * @param ServiceManager $sm
     */
    protected function configure(Kernel $kernel,
                                 Invoker $invoker,
                                 BundleManager $bm,
                                 AnnotationReader $ar,
                                 EventManager $em,
                                 Request $request)
    {
        $this->em                = $em;
        $this->kernel            = $kernel;
        $this->invoker           = $invoker;
        $this->request           = $request;
        $this->annotation_reader = $ar;
        $this->bundle_manager    = $bm;

        $this->em->register(new EventListener('dispatcher.on_resource', true));
        $this->em->register(new EventListener('dispatcher.on_route', true));
        $this->em->register(new EventListener('dispatcher.404', true));

        $this->em->getEvent('dispatcher.404')->bind(function(EventListener $e){
            if (!headers_sent()) {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
                header("Status: 404 Not Found");
            }
            Framework::getInstance()->execute404();
        }, 0);
    }

    public function dispatchFromRequest()
    {
        if ($this->request->getEnvironment() == Request::ENV_CLI) {
            throw DispatcherException::notAvailableInCLI();
        }
        
        // Resources
        foreach ($this->bundle_manager->getBundles() as $bundle) {
            /* @var $bundle \Unity\Component\Kernel\Bundle */
            if (empty($this->request->getPath())) break;
            if (null !== ($file = $bundle['class']->findResource($this->request->getPath()))) {
                $this->em->trigger('dispatcher.on_resource', [
                    'resource' => $file,
                    'file'     => $file
                ]);
                return;
            }
        }
        
        // Controllers
        $routes = $this->collectRoutes();
        foreach ($routes as $data) {
            $callable = $data['callable'];
            $route    = $data['route'];
            if ($route->matchesPath($this->request->getPath())) {
                $object = $data['callable'][0];
                $method = $data['callable'][1];
                $event  = $this->em->getEvent('dispatcher.on_route');
                /* @var $event \Unity\Component\Event\EventListener */
                $event->setParameter('controller', $object);
                $event->setParameter('method', $method);
                $event->setParameter('args', $route->getNamedArguments());
                $event->setParameter('route', $route);
                $this->em->trigger($event);
                $object = $event->getParameter('object', $object);
                $method = $event->getParameter('method', $method);
                $args   = $event->getParameter('args', $route->getNamedArguments());
                return $object->render($method, $args->toArray());
            }
        }


        $this->em->trigger('dispatcher.404', array('routes' => $routes));
    }

    // _________________________________________________________/ PRIVATES \____

    /**
     * Returns an array of callable method arrays and Route objects.
     *
     * @return array
     */
    private function collectRoutes()
    {
        $controllers = array();
        foreach ($this->kernel->getControllers() as $controller) {
            $class = $this->annotation_reader->getClass($controller);
            foreach ($class->getMethods() as $method) {
                $route = $method->getAnnotation('Route');
                if ($route && $route instanceof Route) {
                    $controllers[] = array(
                        'callable' => array($controller, $method->getName()),
                        'route' => $route
                    );
                }
            }
        }
        return $controllers;
    }

}