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
namespace Unity\Component\HTTP\Header;

use Unity\Component\HTTP\ResponseHeaders;
use Unity\Component\Kernel\FileNotFoundException;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class ContentType extends Header
{
    public function __construct()
    {
        $this->name  = ResponseHeaders::CONTENT_TYPE;
        $this->mimes = json_decode(file_get_contents(__DIR__ . '/../MimeTypes.json'), true);
    }

    public function setByExtension($filename)
    {
        $ext = substr($filename, strrpos($filename, '.') + 1);
        $this->value = isset($this->mimes[$ext]) ? 'application/octet-stream' : $this->mimes[$ext];
    }
}