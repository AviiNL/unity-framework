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
namespace Unity\Components\Annotation;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Kaps <christian.kaps@mohiva.com>
 */
class TokenParser
{
    /**
     * The token list.
     *
     * @var array
     */
    private $tokens;

    /**
     * The number of tokens.
     *
     * @var int
     */
    private $numTokens = 0;

    /**
     * The current array pointer.
     *
     * @var int
     */
    private $pointer = 0;

    public function __construct($contents)
    {
        $this->tokens = token_get_all($contents);
        $this->numTokens = count($this->tokens);
        $this->pointer = 0;
    }

    /**
     * Gets the next non whitespace and non comment token.
     *
     * @param $docCommentIsComment
     *     If TRUE then a doc comment is considered a comment and skipped.
     *     If FALSE then only whitespace and normal comments are skipped.
     *
     * @return array The token if exists, null otherwise.
     */
    public function next($docCommentIsComment = TRUE)
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            $this->pointer++;
            if ($this->tokens[$i][0] === T_WHITESPACE ||
                $this->tokens[$i][0] === T_COMMENT ||
                ($docCommentIsComment && $this->tokens[$i][0] === T_DOC_COMMENT)) {

                continue;
            }

            return $this->tokens[$i];
        }

        return null;
    }

    /**
     * Parse a single use statement.
     *
     * @return array A list with all found class names for a use statement.
     */
    public function parseUseStatement()
    {
        $class = '';
        $alias = '';
        $statements = array();
        $explicitAlias = false;
        while (null !== ($token = $this->next())) {
            $isNameToken = $token[0] === T_STRING || $token[0] === T_NS_SEPARATOR;
            if (!$explicitAlias && $isNameToken) {
                $class .= $token[1];
                $alias = $token[1];
            } else if ($explicitAlias && $isNameToken) {
                $alias .= $token[1];
            } else if ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } else if ($token === ',') {
                $statements[($alias)] = $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } else if ($token === ';') {
                $statements[($alias)] = $class;
                break;
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * Get all use statements.
     *
     * @param string $namespaceName The namespace name of the reflected class.
     * @return array A list with all found use statements.
     */
    public function parseUseStatements($namespaceName)
    {
        $statements = array();
        while (null !== ($token = $this->next())) {
            if ($token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            }
            if ($token[0] !== T_NAMESPACE || $this->parseNamespace() != $namespaceName) {
                continue;
            }

            // Get fresh array for new namespace. This is to prevent the parser to collect the use statements
            // for a previous namespace with the same name. This is the case if a namespace is defined twice
            // or if a namespace with the same name is commented out.
            $statements = array();
        }

        return $statements;
    }

    /**
     * Get the namespace.
     *
     * @return string The found namespace.
     */
    public function parseNamespace()
    {
        $name = '';
        while (($token = $this->next()) && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
            $name .= $token[1];
        }

        return $name;
    }

    /**
     * Get the class name.
     *
     * @return string The foundclass name.
     */
    public function parseClass()
    {
        // Namespaces and class names are tokenized the same: T_STRINGs
        // separated by T_NS_SEPARATOR so we can use one function to provide
        // both.
        return $this->parseNamespace();
    }
}
