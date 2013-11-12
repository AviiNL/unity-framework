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

use Unity\Framework;

use Unity\Component\Bundle\BundleManager;

use Unity\Component\Yaml\Yaml;
use Unity\Component\Event\EventManager;
use Unity\Component\Service\Service;
use Unity\Component\Service\ServiceManager;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Kernel extends Service
{
    private $controllers = array(),
            $plugins     = array(),
            $services    = null,
            $events      = null,
            $bundles     = null,
            $application = null;

    final public function __construct(Framework $application)
    {
        $this->setName('kernel');

        $this->services    = new ServiceManager();
        $this->events      = new EventManager();
        $this->bundles     = new BundleManager();
        $this->application = $application;

        $this->services->register($this);
        $this->services->register($this->bundles);
        $this->services->register($this->events);
    }

    /**
     * Returns the application instance.
     *
     * @return \Unity\Framework
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns all registered controllers.
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
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
    final public function register($objects)
    {
      if (!is_array($objects)) $objects = array($objects);
      $this->loadBundle($objects);
    }

    /**
     * Registers objects from a YAML file.
     *
     * @param unknown_type $filename
     * @throws FileNotFoundException
     */
    final public function registerFromYAML($filename)
    {
      if (!file_exists($filename)) {
        throw new FileNotFoundException($filename);
      }
      $yaml    = Yaml::parse(file_get_contents($filename));
      $classes = array();
      $objects = array();

      if (isset($yaml['controllers'])) {
        foreach((array)$yaml['controllers'] as $name) {
          $classes[] = $name;
        }
      }
      if (isset($yaml['services'])) {
        foreach((array)$yaml['services'] as $name) {
          $classes[] = $name;
        }
      }
      if (isset($yaml['events'])) {
        foreach((array)$yaml['events'] as $name) {
          $classes[] = $name;
        }
      }
      if (isset($yaml['bundles'])) {
        foreach((array)$yaml['bundles'] as $name) {
          $classes[] = $name;
        }
      }
      if (isset($yaml[0])) {
        $classes = $yaml;
      }

      foreach ($classes as $class) {
        $objects[] = new $class();
      }
      $this->register($objects);
    }

    private function loadBundle($objects)
    {
        foreach($objects as $object) {
            if ($object instanceof IController) {
                $this->controllers[] = $object;
                $object->setContainer($this->services->getContainer());
                $object->setParameters($this->services->getService('parameters'));
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
     * @return \Unity\Component\Service\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->services;
    }

    /**
     * @return \Unity\Component\Event\EventManager
     */
    public function getEventManager()
    {
        return $this->events;
    }
}