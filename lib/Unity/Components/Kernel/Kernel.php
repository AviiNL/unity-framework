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

use Unity\Components\Service\Service;
use Unity\Components\Event\EventManager;
use Unity\Components\Service\ServiceManager;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Kernel extends Service
{
    private $controllers = array(),
            $plugins     = array(),
            $services    = null,
            $events      = null,
            $bundles     = null;

    final public function __construct()
    {
        $this->setName('kernel');

        $this->services = new ServiceManager();
        $this->events   = new EventManager();
        $this->bundles  = new BundleManager();

        $this->services->register($this);
        $this->services->register($this->bundles);
        $this->services->register($this->events);
    }

    public function getControllers()
    {
        return $this->controllers;
    }

    public function loadBundle($objects)
    {
        foreach($objects as $object) {
            if ($object instanceof IController) {
                $this->controllers[] = $object;
            }elseif ($object instanceof IService) {
                $this->services->register($object);
            }elseif ($object instanceof IEvent) {
                $this->events->register($object);
            }elseif ($object instanceof IBundle && !$this->bundles->exists($object)) {
                $this->bundles->register($object);
                $this->loadBundle($object->getObjects());
            }
        }
    }

    /**
     * Returns the ServiceManager
     *
     * @return \Unity\Components\Service\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->services;
    }

    /**
     * @return \Unity\Components\Event\EventManager
     */
    public function getEventManager()
    {
        return $this->events;
    }
}