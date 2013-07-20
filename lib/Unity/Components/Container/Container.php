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
namespace Unity\Components\Container;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Container
{
  protected $storage   = array();
  protected $separator = '_';
  protected $parent    = null;

  /**
   * @param array $array       Optional pre-fill array
   * @param string $separator  Single character separator (default: .)
   * @param Container $parent  Parent container (if there is one)
   */
  public function __construct($array = array(), $separator = '.', Container $parent = null)
  {
    $this->separator = $separator;
    $this->parent    = $parent;
    $this->set($array);
  }

  /**
   * Adds a new element to this container.
   *
   * @param string $key
   * @param mixed $value
   */
  public function set($key, $value = null)
  {
    $class = get_called_class();
    if(is_array($key)) {
      foreach($key as $k => $v) {
        $this->set($k, $v);
      }
    }elseif(stripos($key, $this->separator) !== false) {
        $root = substr($key, 0, strpos($key, $this->separator));
        $rest = substr($key, strlen($root) + 1);
        if(isset($this->storage[$root]) && !$this->storage[$root] instanceof self) {
          $this->storage[$root] = new $class(array(), $this->separator, $this);
        }elseif(!isset($this->storage[$root])) {
          $this->storage[$root] = new $class(array(), $this->separator, $this);
        }
        $this->storage[$root]->set($rest, $value);
    }else{
      if(is_array($value)) {
        $this->storage[$key] = new $class(array(), $this->separator, $this);
        foreach($value as $k => $v) {
          $this->storage[$key]->set($k, $v);
        }
      }else{
        $this->storage[$key] = $value;
      }
    }
  }

  /**
   * Return a value based on the requested method.
   *
   * @param string $fn
   * @param array $args
   * @return mixed
   */
  public function __call($fn, $args = array())
  {
    if(substr($fn, 0, 3) === 'get') {
      $name = ucfirst(substr($fn, 3));
      foreach($this->storage as $key => $data) {
        $key = str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $key)));
        if($key == $name) {
          return $data;
        }
      }
    }
  }

  /**
   * Returns the parent container if there is one, false otherwise.
   *
   * @return Container|bool<false>
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Returns the root container, the very parent.
   *
   * @return Container
   */
  public function getRoot()
  {
    if($this->parent instanceof Container) {
      return $this->parent->getRoot();
    }
    return $this;
  }

  /**
   * Returns an element from the Container.
   *
   * @param string $key
   * @param string $default
   * @return mixed
   */
  public function get($key, $default = null)
  {
    if(stripos($key, $this->separator) !== false) {
      $root = substr($key, 0, strpos($key, $this->separator));
      $rest = substr($key, strlen($root) + 1);
      return isset($this->storage[$root]) ? $this->storage[$root]->get($rest, $default) : $default;
    }
    $value = isset($this->storage[$key]) ? $this->storage[$key] : $default;
    if(is_string($value)) {
      if(strpos($value, '%') === 0 && strrpos($value, '%') === strlen($value) - 1) {
        $key = trim($value, '%');
        $value = $this->getRoot()->get($key, $default);
      }
    }
    return $value;
  }

  /**
   * Deletes a key.
   *
   * @param string $key
   */
  public function delete($key)
  {
    if(stripos($key, $this->separator) !== false) {
      $root = substr($key, 0, strpos($key, $this->separator));
      $rest = substr($key, strlen($root) + 1);
      isset($this->storage[$root]) ? $this->storage[$root]->delete($rest) : null;
    }
    if(isset($this->storage[$key])) {
      unset($this->storage[$key]);
    }
  }

  /**
   * Clears the entire contianer. Use with caution!
   */
  public function clear()
  {
    $this->storage = array();
  }

  /**
   * Returns an associative array of the Container storage.
   *
   * @return array
   */
  public function toArray()
  {
    $result = array();
    foreach($this->storage as $key => $value) {
      if($value instanceof self) {
        $result[$key] = $value->toArray();
      }else{
        $result[$key] = $this->get($key);
      }
    }
    return $result;
  }

  /**
   * Returns a JSON representation of this Container
   *
   * @return string
   */
  public function toJSON()
  {
    return json_encode($this->toArray());
  }

  /**
   * Fills this container with data from the given JSON string.
   *
   * @param string $json
   */
  public function fromJSON($json)
  {
    $this->set(json_decode($json, true));
  }

  /**
   * Loads data from the given INI file into this Container.
   *
   * @param string $filename
   */
  public function fromINI($filename)
  {
    if(!file_exists($filename)) {
      throw new \Exception('File [' . $filename . '] not found.');
    }
    if(false === ($ini = parse_ini_file($filename, true))) {
      throw new \Exception('Error parsing INI file: [' . $filename . ']');
    }
    foreach($ini as $key => $value) {

    }
    $this->set($ini);
  }

  private function formatValues($values)
  {
    if(is_array($values)) {
      foreach($values as $key => $value)
      {
        $value = $this->formatValues($value);
      }
    }else{
      foreach($this->storage as $key => $value) {
        $values = str_replace('%' . $key . '%', $value, $values);
      }
    }
    return $values;
  }
}