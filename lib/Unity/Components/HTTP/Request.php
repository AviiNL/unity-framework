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

use Unity\Components\Service\Service;
use Unity\Components\Container\Container;

// Failsafe option for non-apache webservers
// As of PHP 5.4.0, getallheaders also became available under FastCGI.
if (!function_exists('getallheaders')) {
    function getallheaders() {
      return array();
    }
}

/**
 * HTTP-Request Service
 *
 * @author Harold Iedema <harold@iedema.me>
 */
class Request extends Service
{
    const ENV_CLI = 'CLI',
          ENV_XHR = 'XHR',
          ENV_WEB = 'WEB';

    private $parameters  = null,
            $headers     = null,
            $path        = null,
            $ip          = null,
            $origin      = null,
            $environment = null;

    public function __construct()
    {
        $this->setName('request');

        // Pre-set all variables so they can't be spoofed as easily.
        $this->parameters  = $this->getParameters();
        $this->headers     = $this->getHeaders();
        $this->environment = $this->getEnvironment();
        $this->ip          = $this->getIp();
        $this->origin      = $this->getOrigin();
        $this->path        = $this->getPath();
    }

    /**
     * Returns all parameters.
     *
     * @return \Unity\Components\HTTP\Parameters
     */
    public function getParameters()
    {
        if (!$this->parameters) {
            $this->parameters = new Container();
            $this->parameters->set('POST' , $this->hydrateParams((array)$_POST));
            $this->parameters->set('GET'  , $this->hydrateParams((array)$_GET));
            $this->parameters->set('FILES', $this->hydrateParams((array)$_FILES));
            $this->parameters->set('FILES', $this->hydrateParams((array)$_SERVER));
        }
        return $this->parameters;
    }

    /**
     * Returns the request path.
     *
     * @return string
     */
    public function getPath()
    {
        if (!$this->path && isset($_SERVER['REQUEST_URI'])) {
            $tmp = parse_url($_SERVER['REQUEST_URI']);
            $this->path = trim($tmp['path'], '/');
        }
        return $this->path;
    }

    /**
     * Returns the origin URI of the request.
     *
     * @return string
     */
    public function getOrigin()
    {
        if (!$this->origin) {
            $this->origin = htmlspecialchars(escapeshellarg(
                $this->parameters->get('HTTP_REFERER')));
        }
        return $this->origin;
    }

    /**
     * Returns the IP address of the originating host.
     *
     * @return string
     */
    public function getIp()
    {
        if (!$this->ip) {
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif(isset($_SERVER['REMOTE_ADDR'])) {
                $this->ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $this->ip = '0.0.0.0';
            }
        }
        return $this->ip;
    }

    /**
     * Returns a request parameter.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        if ($default !== ($v = $this->parameters->get('POST.' . $key))) {
          return $v;
        } elseif ($default !== ($v = $this->parameters->get('GET.' . $key))) {
          return $v;
        } elseif ($default !== ($v = $this->parameters->get('FILES.' . $key))) {
          return $v;
        } else {
          return $default;
        }
    }

    /**
     * Formats an input string or array.
     *
     * @param array $data
     * @return array
     */
    private function hydrateParams($data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->hydrateParams($value);
            } else {
                $result[$key] = (null !== ($v = json_decode($value, true))) ? $v : $value;
            }
        }
        return $result;
    }

    /**
     * Returns the environment the request originated from.
     *
     * @return string:enum(CLI, XHR, WEB)
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            if (php_sapi_name() === 'cli') {
                $this->environment = self::ENV_CLI;
            }elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $this->environment = self::ENV_XHR;
            }else{
                $this->environment = self::ENV_WEB;
            }
        }
        return $this->environment;
    }

    /**
     * Returns all request headers.
     *
     * @return \Unity\Components\HTTP\RequestHeaders
     */
    public function getHeaders()
    {
        if(!$this->headers) {
             $this->headers = new RequestHeaders(getallheaders());
        }
        return $this->headers;
    }
}
