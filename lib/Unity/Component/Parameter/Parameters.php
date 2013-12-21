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
namespace Unity\Component\Parameter;

use Unity\Component\Service\Service;
use Unity\Component\Yaml\YamlService;
use Unity\Component\Container\Container;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Parameters extends Service
{
    private $container;
    private $yaml;

    public function __construct(YamlService $yaml)
    {
        $this->setName('parameters');
        $this->yaml      = $yaml;
        $this->container = new Container();
    }

    /**
     * Returns the value of the given parameter name. If the default value is
     * not specified, it will throw a ParameterNotFoundException if the
     * parameter does not exist.
     *
     * @param string $key
     * @param mixed $default
     * @throws ParameterNotFoundException
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        $value = $this->container->get($key, $default);
        if(sizeof(func_get_args()) == 1 && $value === null) {
            throw new ParameterNotFoundException($key);
        }
        return $value;
    }

    /**
     * Sets a value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParameter($key, $value)
    {
        if (strpos($value, '%') !== false) {
            preg_match_all('/%(.*)%/', $value, $matches);
            foreach ($matches[0] as $match) {
                if (!empty($match)) {
                    $value = str_replace($match, $this->getParameter(trim($match, '%')), $value);
                }
            }
        }
        $this->container->set($key, $value);
    }

    /**
     * Loads a YAML file.
     *
     * @param string $filename
     */
    public function loadFromYAML($filename)
    {
        $data = $this->yaml->parseFile($filename);
        foreach((array)$data as $key => $value) {
            $this->setParameter($key, $value);
        }
    }
}