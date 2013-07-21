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
namespace Unity\Component\Bundle;

use Unity\Component\Kernel\IBundle;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class Bundle implements IBundle
{
    private $name           = null,
            $reflector      = null,
            $controllers    = array(),
            $services       = array(),
            $events         = array(),
            $resource_paths = array();

    /**
     * Returns the name of this bundle.
     *
     * @return string
     */
    final public function getName()
    {
        if (!$this->name) {
            $this->name = $this->bundlify(get_class($this));
        }
        return $this->name;
    }

    /**
     * Returns all objects registered with this bundle.
     *
     * @return multitype:
     */
    final public function getObjects()
    {
        return array_merge($this->controllers, $this->services, $this->events);
    }

    /**
     * Returns the absolute path to $filename by searching through the resource-
     * paths registered within this bundle. Returns NULL if the file could not
     * be located.
     *
     * @param string $filename
     * @return string|null
     */
    final public function findResource($filename)
    {
        foreach ($this->resource_paths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                return $path . DIRECTORY_SEPARATOR . $filename;
            }
        }
    }

    /**
     * Registers a resource path relative from the bundle path.
     *
     * @param string $path
     */
    final protected function registerResourcePath($path)
    {
        if (!$this->reflector) {
            $this->reflector = new \ReflectionClass($this);
        }

        $dir = dirname($this->reflector->getFileName());
        if(strpos($dir, $path) === 0) {
          $dir = $path;
        }else{
          $dir .= DIRECTORY_SEPARATOR . $path;
        }

        if(!file_exists($dir)) {
            throw new \InvalidArgumentException('The path ' . $dir . ' does not exist.');
        }

        $this->resource_paths[] = $dir;
    }

    /**
     * Registers one or more controllers to this bundle.
     *
     * @param IController $controllers
     */
    final protected function registerControllers($controllers)
    {
        $this->controllers = array_merge($this->controllers, (array)$controllers);
    }

    /**
     * Registers one or more services to this bundle.
     *
     * @param IService $services
     */
    final protected function registerServices($services)
    {
      $this->services = array_merge($this->services, (array)$services);
    }

    /**
     * Registers one or more events to this bundle.
     *
     * @param IEvent $events
     */
    final protected function registerEvents($events)
    {
      $this->events = array_merge($this->events, (array)$events);
    }

    // ___________________________________________________________/ HELPERS \___

    /**
     * Creates a name for the bundle.
     *
     * @param string $text
     * @return string
     */
    private function bundlify($text)
    {
        $text = trim($this->slugify($text));
        $text = str_replace(' ', '', ucwords(str_replace('-', ' ', $text)));
        return $text;
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