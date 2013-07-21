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

class AnnotationCollection
{
    private $parser      = null,
            $reflector   = null,
            $methods     = array(),
            $annotations = array(),
            $imports;

    public function __construct(Parser $parser,
                                AnnotationCollection $parent = null)
    {
        $this->parser    = $parser;
        $this->reflector = $parser->getReflector();
        $this->parent    = $parent;
        $this->build();
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getName()
    {
        return $this->reflector->getName();
    }

    private function build()
    {
        if($this->reflector instanceof \ReflectionClass) {
            // Fetch use-statements.
            $this->imports = $this->getImports();
            foreach($this->reflector->getMethods() as $method) {
                $this->methods[$method->getName()] =
                      new AnnotationCollection(new Parser($method), $this);
            }
        }
        if($this->reflector instanceof \ReflectionMethod) {
            $this->imports = $this->parent->getImports();
        }

        $ns = $this->reflector->getNamespaceName();

        if(null === ($annotations = $this->parser->getAnnotations())) {
          return;
        }

        foreach($annotations as $name => $value)
        {
            $class = null;
            if (isset($this->imports[$name])) {
                $class = $this->imports[$name];
            } elseif(class_exists($name)) {
                $class = $name;
            } elseif(class_exists($ns . "\\" . $name)) {
                $class = $ns . "\\" . $name;
            } else {
                foreach ($this->imports as $alias => $namespace) {
                    $name = str_replace($alias . '\\', $namespace . '\\', $name);
                    if(class_exists($name)) {
                        $class = $name;
                    }
                }
            }
            if (!$class) {
                $this->annotations[$name] = new Annotation($name, $value, $this->reflector);
                continue;
            }
            $this->annotations[$name] = new $class($name, $value, $this->reflector);
        }
    }

    /**
     * Returns the specified Annotation object or FALSE if it doesn't exist.
     *
     * @param string $annotation
     * @return Annotation
     */
    public function getAnnotation($annotation)
    {
        if(isset($this->annotations[$annotation]))
            return $this->annotations[$annotation];

        foreach ($this->annotations as $name => $value) {
            if (strtolower($name) == strtolower($annotation)) {
                return $value;
            } elseif ($name instanceof Annotation &&
                      strtolower($name->getName()) == strtolower($annotation)) {
                return $value;
            }
        }
        return false;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @return AnnotationCollection
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Retrieves a list of use-statements from the class file.
     */
    public function getImports()
    {
        if (!$this->imports) {
            $data          = file_get_contents($this->reflector->getFileName());
            $token_parser  = new TokenParser($data);
            $this->imports = $token_parser->parseUseStatements(
                $this->reflector->getNamespaceName());
        }
        return $this->imports;
    }
}









