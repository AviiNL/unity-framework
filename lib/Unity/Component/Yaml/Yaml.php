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

use Unity\Component\Yaml\Exception\ParseException;

/**
 * Yaml offers convenience methods to load and dump YAML.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Yaml
{
    /**
     * Parses YAML into a PHP array.
     *
     * The parse method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.
     *
     *  Usage:
     *  <code>
     *   $array = Yaml::parse('config.yml');
     *   print_r($array);
     *  </code>
     *
     * As this method accepts both plain strings and file names as an input,
     * you must validate the input before calling this method. Passing a file
     * as an input is a deprecated feature and will be removed in 3.0.
     *
     * @param string  $input                  Path to a YAML file or a string containing YAML
     * @param Boolean $exceptionOnInvalidType True if an exception must be thrown on invalid types false otherwise
     * @param Boolean $objectSupport          True if object support is enabled, false otherwise
     *
     * @return array The YAML converted to a PHP array
     *
     * @throws ParseException If the YAML is not valid
     *
     * @api
     */
    public static function parse($input, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        // if input is a file, process it
        $file = '';
        if (strpos($input, "\n") === false && is_file($input)) {
            if (false === is_readable($input)) {
                throw new Exception\ParseException(sprintf('Unable to parse "%s" as the file is not readable.', $input));
            }

            $file = $input;
            $input = file_get_contents($file);
        }

        $yaml = new Parser();

        try {
            return $yaml->parse($input, $exceptionOnInvalidType, $objectSupport);
        } catch (Exception\ParseException $e) {
            if ($file) {
                $e->setParsedFile($file);
            }

            throw $e;
        }
    }

    /**
     * Dumps a PHP array to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param array   $array                  PHP array
     * @param integer $inline                 The level where you switch to inline YAML
     * @param integer $indent                 The amount of spaces to use for indentation of nested nodes.
     * @param Boolean $exceptionOnInvalidType true if an exception must be thrown on invalid types (a PHP resource or object), false otherwise
     * @param Boolean $objectSupport          true if object support is enabled, false otherwise
     *
     * @return string A YAML string representing the original PHP array
     *
     * @api
     */
    public static function dump($array, $inline = 2, $indent = 4, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        $yaml = new Dumper();
        $yaml->setIndentation($indent);

        return $yaml->dump($array, $inline, 0, $exceptionOnInvalidType, $objectSupport);
    }
}