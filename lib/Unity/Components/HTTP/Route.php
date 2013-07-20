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
namespace Unity\Components\HTTP;

use Unity\Components\Annotation\Annotation;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Route extends Annotation
{
    private $named_args = array();

    /**
     * Returns true if the given $path matches this Route.
     *
     * @param string $path
     */
    public function matchesPath($path)
    {
        foreach((array)$this->value as $key => $value) {
            if($value === true) {
                $value = $key;
            }
            $value = trim($value, '/ ');
            if ($path == $value) {
                return true;
            }
            if( $this->match($value, $path) ) {
                return true;
            }
        }
    }

    /**
     * @return multitype:
     */
    public function getNamedArguments()
    {
        return $this->named_args;
    }

    /**
     * Returns true if the $route matches $path.
     *
     * @param string $route
     * @param string $path
     */
    private function match($route, $path)
    {
        $params = array();
        foreach ($this->reflector->getParameters() as $param) {
            $params[] = $param->getName();
        }

        $path_chunks = explode('/', $path);
        $route_chunks = explode('/', $route);

        if(sizeof($path_chunks) != sizeof($route_chunks)) {
          return false;
        }

        if(empty($route) && empty($path)) {
          return true;
        }
        if(!empty($route) && empty($path)) {
          return false;
        }
        for ($i = 0; $i < sizeof($route_chunks); $i++) {
            $r = $route_chunks[$i];
            $p = $path_chunks[$i];
            if(strpos($r, '{') === 0 && strpos($r, '}') === strlen($r) - 1) {
                $this->named_args[trim($r, '{}')] = urldecode($p);
            }elseif ($r != $p) {
                return false;
            }
        }
        return true;
    }
}