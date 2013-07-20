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

use Unity\Components\Service\ServiceManager;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class Kernel
{
    private $controllers = array(),
            $plugins     = array(),
            $services    = null;

    final public function __construct()
    {
        $this->services = new ServiceManager();

        $objects = array_merge($this->boot(), array(new KernelController()));
        foreach($objects as $object) {
            if ($object instanceof IController) {
                $this->controllers[] = $object;
            }elseif ($object instanceof IPlugin) {
                if (isset($this->plugins[$plugin->getName()])) {
                    throw new PluginAlreadyRegisteredException($object->getName());
                }
                $this->plugins[$object->getName()] = $object;
            }elseif ($object instanceof IService) {
                $this->services->register($object);
            }
        }
    }

    /**
     * Returns an array of object instances of Controllers and Plugins that
     * should be loaded throughout the framework.
     *
     * Objects referenced in this array should implement IPlugin or IController.
     *
     * @return array
     */
    protected abstract function boot();

    /**
     * Returns the ServiceManager
     *
     * @return \Unity\Components\Service\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->services;
    }
}