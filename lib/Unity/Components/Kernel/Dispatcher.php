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
namespace Unity\Components\Kernel;

use Unity\Components\HTTP\Route;

use Unity\Components\HTTP\Request;
use Unity\Components\Service\Service;
use Unity\Components\Service\ServiceManager;
use Unity\Components\Annotation\AnnotationReader;

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
     * @var \Unity\Components\Kernel\Invoker
     */
    private $invoker;

    /**
     * @var \Unity\Components\Kernel\Kernel
     */
    private $kernel;

    /**
     * @var \Unity\Components\HTTP\Request
     */
    private $request;

    /**
     * @var \Unity\Components\Kernel\BundleManager
     */
    private $bundle_manager;

    /**
     * @var \Unity\Components\Annotation\AnnotationReader
     */
    private $annotation_reader;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('dispatcher')
             ->addDependency('kernel')
             ->addDependency('invoker')
             ->addDependency('bundle-manager')
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
                                 Request $request)
    {
        $this->kernel            = $kernel;
        $this->invoker           = $invoker;
        $this->request           = $request;
        $this->annotation_reader = $ar;
        $this->bundle_manager    = $bm;
    }

    public function dispatchFromRequest()
    {
        if ($this->request->getEnvironment() == Request::ENV_CLI) {
            throw DispatcherException::notAvailableInCLI();
        }

        // Controllers
        $routes = $this->collectRoutes();
        foreach ($routes as $data) {
            $callable = $data['callable'];
            $route    = $data['route'];
            if ($route->matchesPath($this->request->getPath())) {
                $object = $data['callable'][0];
                $method = $data['callable'][1];
                return $object->render($method, $route->getNamedArguments());
            }
        }

        // Resources
        foreach ($this->bundle_manager->getBundles() as $bundle) {
            /* @var $bundle \Unity\Components\Kernel\Bundle */
            if(null !== ($file = $bundle['class']->findResource($this->request->getPath()))) {
                echo '<h1>TODO</h1><i>Implement teh file rendererrrrrawr!<hr>';
                return;
            }
        }

        echo '404';
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