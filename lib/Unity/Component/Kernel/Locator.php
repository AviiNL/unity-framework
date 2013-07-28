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

use Unity\Component\Service\Service;
use Unity\Component\Service\ServiceManager;
use Unity\Component\Bundle\BundleManager;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Locator extends Service
{
    /**
     * @var \Unity\Component\Kernel\Kernel
     */
    private $kernel;

    /**
     * @var \Unity\Component\Kernel\Invoker
     */
    private $invoker;

    /**
     * @var \Unity\Component\Bundle\BundleManager
     */
    private $bm;

    /**
     * @var \Unity\Component\Service\ServiceManager
     */
    private $sm;

    public function __construct()
    {
        $this->setName('locator');
        $this->addDependency('kernel')
             ->addDependency('bundle-manager')
             ->addDependency('service-manager')
             ->addDependency('invoker');
    }

    protected function configure(Kernel $kernel,
                                 BundleManager $bm,
                                 ServiceManager $sm,
                                 Invoker $invoker)
    {
        $this->kernel = $kernel;
        $this->bm = $bm;
        $this->sm = $sm;
        $this->invoker = $invoker;
    }

    /**
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public function find($name, $args = array())
    {
        $tmp = explode(':', $name, 2);
        if(null !== ($obj = $this->_findClassByAlias($tmp[0]))) {
            if (isset($tmp[1])) {
                if (method_exists($obj, $tmp[1])) {
                    return $this->invoker->invoke(array($obj, $tmp[1]), $args);
                }
                if (empty($tmp[1])) {
                    return $obj;
                }
                $refl = new \ReflectionObject($obj);
                $tmp[1] = str_replace(':', DIRECTORY_SEPARATOR, $tmp[1]);
                $file = dirname($refl->getFileName()) . DIRECTORY_SEPARATOR . $tmp[1];
                if (file_exists($file)) {
                    return $file;
                } else {
                    return null;
                }
            } else {
                return $obj;
            }
        }
    }

    private function _findClassByAlias($alias)
    {
        foreach($this->kernel->getControllers() as $controller)
        {
            if (str_replace('\\', '.', get_class($controller)) == $alias) {
                return $controller;
            }
        }
        foreach($this->sm->getContainer()->toArray() as $key => $obj) {
            if ($key == $alias) {
                return $obj;
            }
            if (str_replace('\\', '.', get_class($obj)) == $alias) {
                return $obj;
            }
        }
        foreach($this->bm->getBundles() as $name => $data) {
            if ($alias == $name) {
                return $data['class'];
            }
        }
        foreach($this->bm->getBundles() as $name => $data) {
            if (str_replace('\\', '.', get_class($data['class'])) == $alias) {
                return $data['class'];
            }
        }
        foreach($this->bm->getBundles() as $n => $data) {
            $name = basename(str_replace('\\', DIRECTORY_SEPARATOR, get_class($data['class'])));
            if (str_replace('\\', '.', $name) == $alias) {
                return $data['class'];
            }
        }
    }
}
