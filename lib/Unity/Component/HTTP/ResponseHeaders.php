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

use Unity\Component\Container\Container;

class ResponseHeaders extends HeaderCollection
{
    // Standard (RFC) Response Headers
    const ACCESS_CONTROL_ALLOW_ORIGIN = "Access-Control-Allow-Origin";
    const ACCEPT_RANGES               = "Accept-Ranges";
    const AGE                         = "Age";
    const ALLOW                       = "Allow";
    const CACHE_CONTROL               = "Cache-Control";
    const CONNECTION                  = "Connection";
    const CONTENT_ENCODING            = "Content-Encoding";
    const CONTENT_LANGUAGE            = "Content-Language";
    const CONTENT_LENGTH              = "Content-Length";
    const CONTENT_LOCATION            = "Content-Location";
    const CONTENT_MD5                 = "Content-MD5";
    const CONTENT_DISPOSITION         = "Content-Disposition";
    const CONTENT_RANGE               = "Content-Range";
    const CONTENT_TYPE                = "Content-Type";
    const DATE                        = "Date";
    const ETAG                        = "ETag";
    const EXPIRES                     = "Expires";
    const LAST_MODIFIED               = "Last-Modified";
    const LINK                        = "Link";
    const LOCATION                    = "Location";
    const P3P                         = "P3P";
    const PRAGMA                      = "Pragma";
    const PROXY_AUTHENTICATE          = "Proxy-Authenticate";
    const REFRESH                     = "Refresh";
    const RETRY_AFTER                 = "Retry-After";
    const SERVER                      = "Server";
    const SET_COOKIE                  = "Set-Cookie";
    const STATUS                      = "Status";
    const STRICT_TRANSPORT_SECURITY   = "Strict-Transport-Security";
    const TRAILER                     = "Trailer";
    const TRANSFER_ENCODING           = "Transfer-Encoding";
    const VARY                        = "Vary";
    const VIA                         = "Via";
    const WARNING                     = "Warning";
    const WWW_AUTHENTICATE            = "WWW-Authenticate";

    // Non-standard Response Headers
    const X_FRAME_OPTIONS             = "X-Frame-Options";
    const X_XSS_PROTECTION            = "X-XSS-Protection";
    const CONTENT_SECURITY_POLICY     = "Content-Security-Policy";
    const X_CONTENT_SECURITY_POLICY   = "X-Content-Security-Policy";
    const X_WEBKIT_CSP                = "X-WebKit-CSP";
    const X_CONTENT_TYPE_OPTIONS      = "X-Content-Type-Options";
    const X_POWERED_BY                = "X-Powered-By";
    const X_UA_COMPATIBLE             = "X-UA-Compatible";
}