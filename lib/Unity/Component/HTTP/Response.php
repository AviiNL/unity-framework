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
namespace Unity\Component\HTTP;

use Unity\Component\HTTP\Header\ContentType;
use Unity\Component\HTTP\Header\ContentLength;
use Unity\Component\HTTP\Header\Header;
use Unity\Component\Service\Service;
use Unity\Component\Container\Container;

/**
 * HTTP-Request Service
 *
 * @author Harold Iedema <harold@iedema.me>
 */
class Response extends Service
{
    private $headers;
    private $content;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('response');
        foreach(headers_list() as $header) {
            $tmp = explode(':', $header, 2);
            if (sizeof($tmp) === 2) {
                $key = trim($tmp[0]);
                $val = trim($tmp[1]);
                $obj = $this->createHeaderObject($key, $val);
                $this->headers[$key] = $obj;
            }
        }
        debug($this->headers);
    }

    /**
     * Sets the response content. This will automatically set the Content-Type
     * and Content-Length headers as well.
     *  
     * @param unknown_type $content
     * @param unknown_type $type
     */
    public function setContent($content, $type = MimeType::TEXT_HTML)
    {
        $this->content = $content;
        $cl = new ContentLength();
        $cl->setLengthOfString($content);
        $this->setHeader($cl);
        
        $ct = new ContentType();
        $ct->setValue($type);
        $this->setHeader($ct);
    }

    /**
     * Sets a header.
     * 
     * @param Header $header
     */
    public function setHeader(Header $header)
    {
        $this->headers[$header->getName()] = $header;
    }

    /**
     * Returns a header by name. Use class constants from RequestHeaders to 
     * avoid spelling-errors and to only use standard response headers.
     * 
     * @param string $name
     * @return NULL
     */
    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Creates a new Header object based on the header name.
     * 
     * @param string $name
     * @param mixed  $value
     */
    protected function createHeaderObject($name, $value)
    {
      $ns    = __NAMESPACE__ . '\\Header\\';
      $class = ucfirst($ns . str_replace('-', '', $name));
      if (!class_exists($class)) {
        $class = $ns . 'Header';
      }
      return new $class($name, $value);
    }
}
