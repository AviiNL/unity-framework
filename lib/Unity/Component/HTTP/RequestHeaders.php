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

class RequestHeaders extends Container
{
    // Standard (RFC) Request Headers
    const ACCEPT               = "Accept";
    const ACCEPT_CHARSET       = "Accept-Charset";
    const ACCEPT_ENCODING      = "Accept-Encoding";
    const ACCEPT_LANGUAGE      = "Accept-Language";
    const ACCEPT_DATETIME      = "Accept-Datetime";
    const AUTHORIZATION        = "Authorization";
    const CACHE_CONTROL        = "Cache-Control";
    const CONNECTION           = "Connection";
    const COOKIE               = "Cookie";
    const CONTENT_LENGTH       = "Content-Length";
    const CONTENT_MD5          = "Content-MD5";
    const DATE                 = "Date";
    const EXPECT               = "Expect";
    const FROM                 = "From";
    const HOST                 = "Host";
    const IF_MATCH             = "If-Match";
    const IF_MODIFIED_SINCE    = "If-Modified-Since";
    const IF_NONE_MATCH        = "If-None-Matched";
    const IF_RANGE             = "If-Range";
    const IF_UNMODIFIED_SINCE  = "If-Unmodified-Since";
    const MAX_FORWARDS         = "Max-Forwards";
    const ORIGIN               = "Origin";
    const PRAGMA               = "Pragma";
    const PROXY_AUTHORIZATION  = "Proxy-Authorization";
    const RANGE                = "Range";
    const REFERER              = "Referer";
    const TE                   = "TE";
    const UPGRADE              = "Upgrade";
    const USER_AGENT           = "User-Agent";
    const VIA                  = "Via";
    const WARNING              = "Warning";

    // Non-Standard Request Headers
    const X_REQUESTED_WITH     = "X-Requested-With";
    const DNT                  = "DNT"; // Do-Not-Track
    const X_FORWARDED_FOR      = "X-Forwarded-For";
    const X_FORWARDED_PROTO    = "X-Forwarded-Proto";
    const FRONT_END_HTTPS      = "Front-End-Https";
    const X_ATT_DEVICEID       = "X-ATT-DeviceId";
    const X_WAP_PROFILE        = "X-Wap-Profile";
    const PROXY_CONNECTION     = "Proxy-Connection";
}