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
namespace Unity\Component\Annotation;

class Parser
{
    private $data;
    private $reflector;

    /**
     * Parses a raw docBlock
     *
     * @param \Reflector $reflector
     */
    public function __construct(\Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->parseBlock($reflector->getDocComment());
    }

    /**
     * @return \Reflector
     */
    public function getReflector()
    {
        return $this->reflector;
    }

    /**
     * Returns an array of all annotations.
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->data;
    }

    /**
     * Returns a single annotations' values or NULL if the annotation does not
     * exist.
     *
     * @param string $name
     * @return array|NULL
     */
    public function getAnnotation($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Returns true if the given annotation exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasAnnotation($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Parses a docBlock comment for annotations.
     *
     * @param string $data
     */
    private function parseBlock($data)
    {
      foreach(explode("\n", $data) as $line) {
        $line = str_replace(array("\r", "\n", "\t"), '', trim($line));
        $line = trim(trim($line, '/*'));

        if(substr($line, 0, 1) === '@') {
          $this->parseAnnotation($line);
        }
      }
    }

    /**
     * Parses a single annotation line.
     *
     * @param string $data
     */
    private function parseAnnotation($data)
    {
        // Separate the annotation name and arguments
        if(strpos($data, '(') !== false) {
          $annotation = substr($data, 0, strpos($data, '('));
          $arguments  = trim(substr($data, strpos($data, '(')), '()');
        }elseif(strpos($data, ' ')) {
          $annotation = trim(substr($data, 0, strpos($data, ' ')));
          $arguments  = trim(substr($data, strlen($annotation)));
        }else{
          $annotation = $data;
          $arguments = null;
        }

        $annotation = trim($annotation, '@ ');
        $arguments  = trim($arguments);

        // Get it's arguments (if any)
        if($arguments) {
            $arguments = $this->parseArguments($arguments);
        }
        $this->data[$annotation] = $arguments;
    }

    /**
     * Parse arguments.
     *
     * @param string $data
     * @return string|multitype:Ambigous <boolean, string>
     */
    private function parseArguments($data)
    {
        if((strpos($data, ',') === false
            && strpos($data, '=') === false) || empty($data)) {
          return trim($data, '"');
        }

        $args = explode(',', $data);
        $result = array();
        foreach($args as $arg) {
          $tmp   = explode('=', $arg);
          $name  = trim($tmp[0], '" ');
          $value = (isset($tmp[1]) ? trim($tmp[1]) : true);
          $result[$name] = $value;
        }

        return $result;
    }
}