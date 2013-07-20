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

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class BundleManager extends Service
{
    private $bundles = array();

    public function __construct()
    {
        $this->setName('bundle-manager');
        $this->addDependency('invoker');
    }

    /**
     * Registers a bundle.
     *
     * @param IBundle $bundle
     */
    public function register(IBundle $bundle)
    {
        $reflector = new \ReflectionClass($bundle);
        $this->bundles[$this->bundlify(get_class($bundle))] = array(
            'class' => $bundle,
            'location' => dirname($reflector->getFileName())
        );
    }

    protected function configure(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    public function getFromAlias($alias, $named_args = array())
    {
        if (strpos($alias, ':') === false) {
            throw new \InvalidArgumentException('Invalid alias format. Format must consist of BundleName:ControllerName');
        }
        $chunks = explode(':', $alias);
        $bundle = $chunks[0];
        if (!isset($this->bundles[$bundle])) {
            throw new BundleNotFoundException($bundle);
        }
        $bundle_obj = $this->bundles[$bundle]['class'];
        $class      = $chunks[1];
        $method     = isset($chunks[2]) ? $chunks[2] : null;
        $objects    = array_merge($bundle_obj->getControllers(),
                                  $bundle_obj->getServices(),
                                  $bundle_obj->getEvents());

        foreach($objects as $object) {
            $name = substr(get_class($object), strrpos(get_class($object), '\\') + 1);
            if($class === $name) {
                if(!$method) {
                    return $object;
                } else {
                    if (method_exists($object, $method)) {
                        return $this->invoker->invoke(array($object, $method), $named_args);
                    }
                }
            }
        }
        throw new \InvalidArgumentException('No route for alias ' . $alias . ' exists.');
    }

    /**
     * Returns an absolute path to the bundle location based on the given alias.
     * For example: UnityTestBundle:resources:index.html.twig would resolve to
     * the path: Unty/Test/Resources/index.html.twig
     *
     * @param string $alias
     * @return mixed
     */
    public function getPathFromAlias($alias)
    {
        $alias = str_replace(':', DIRECTORY_SEPARATOR, $alias);
        foreach ($this->bundles as $name => $data) {
            $alias = str_replace($name, $data['location'], $alias);
        }
        return $alias;
    }

    /**
     * Returns true if a bundle with the same name already exists.
     *
     * @param IBundle $bundle
     * @return bool
     */
    public function exists(IBundle $bundle)
    {
        return isset($this->bundles[$this->bundlify(get_class($bundle))]);
    }

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

