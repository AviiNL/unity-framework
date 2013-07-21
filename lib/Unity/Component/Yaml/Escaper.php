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
namespace Unity\Component\Yaml;

/**
 * Escaper encapsulates escaping rules for single and double-quoted
 * YAML strings.
 *
 * @author Matthew Lewinski <matthew@lewinski.org>
 */
class Escaper
{
    // Characters that would cause a dumped string to require double quoting.
    const REGEX_CHARACTER_TO_ESCAPE = "[\\x00-\\x1f]|\xc2\x85|\xc2\xa0|\xe2\x80\xa8|\xe2\x80\xa9";

    // Mapping arrays for escaping a double quoted string. The backslash is
    // first to ensure proper escaping because str_replace operates iteratively
    // on the input arrays. This ordering of the characters avoids the use of strtr,
    // which performs more slowly.
    private static $escapees = array('\\\\', '\\"', '"',
                                     "\x00",  "\x01",  "\x02",  "\x03",  "\x04",  "\x05",  "\x06",  "\x07",
                                     "\x08",  "\x09",  "\x0a",  "\x0b",  "\x0c",  "\x0d",  "\x0e",  "\x0f",
                                     "\x10",  "\x11",  "\x12",  "\x13",  "\x14",  "\x15",  "\x16",  "\x17",
                                     "\x18",  "\x19",  "\x1a",  "\x1b",  "\x1c",  "\x1d",  "\x1e",  "\x1f",
                                     "\xc2\x85", "\xc2\xa0", "\xe2\x80\xa8", "\xe2\x80\xa9");
    private static $escaped  = array('\\"', '\\\\', '\\"',
                                     "\\0",   "\\x01", "\\x02", "\\x03", "\\x04", "\\x05", "\\x06", "\\a",
                                     "\\b",   "\\t",   "\\n",   "\\v",   "\\f",   "\\r",   "\\x0e", "\\x0f",
                                     "\\x10", "\\x11", "\\x12", "\\x13", "\\x14", "\\x15", "\\x16", "\\x17",
                                     "\\x18", "\\x19", "\\x1a", "\\e",   "\\x1c", "\\x1d", "\\x1e", "\\x1f",
                                     "\\N", "\\_", "\\L", "\\P");

    /**
     * Determines if a PHP value would require double quoting in YAML.
     *
     * @param string $value A PHP value
     *
     * @return Boolean True if the value would require double quotes.
     */
    public static function requiresDoubleQuoting($value)
    {
        return preg_match('/'.self::REGEX_CHARACTER_TO_ESCAPE.'/u', $value);
    }

    /**
     * Escapes and surrounds a PHP value with double quotes.
     *
     * @param string $value A PHP value
     *
     * @return string The quoted, escaped string
     */
    public static function escapeWithDoubleQuotes($value)
    {
        return sprintf('"%s"', str_replace(self::$escapees, self::$escaped, $value));
    }

    /**
     * Determines if a PHP value would require single quoting in YAML.
     *
     * @param string $value A PHP value
     *
     * @return Boolean True if the value would require single quotes.
     */
    public static function requiresSingleQuoting($value)
    {
        return preg_match('/[ \s \' " \: \{ \} \[ \] , & \* \# \?] | \A[ - ? | < > = ! % @ ` ]/x', $value);
    }

    /**
     * Escapes and surrounds a PHP value with single quotes.
     *
     * @param string $value A PHP value
     *
     * @return string The quoted, escaped string
     */
    public static function escapeWithSingleQuotes($value)
    {
        return sprintf("'%s'", str_replace('\'', '\'\'', $value));
    }
}