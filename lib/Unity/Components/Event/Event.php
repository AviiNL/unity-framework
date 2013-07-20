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
namespace Unity\Components\Event;

use Unity\Components\Kernel\Invoker;
use Unity\Components\Container\Container;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Event
{
    private $name        = null,
            $run_once    = true,
            $hooks       = array(),
            $is_executed = false,
            $parameters  = null;

    /**
     * Creates a new event by the name $name. If $run_once is set to true, this
     * event will only run once. If callbacks are hooked to this event after the
     * event was fired and run_once is set to true, these hooks are executed
     * immediately.
     *
     * @param string $name
     * @param bool   $run_once
     */
    public function __construct($name, $run_once = true)
    {
        $this->name       = $name;
        $this->run_once   = (bool)$run_once;
        $this->parameters = new Container();
    }

    /**
     * Sets an event parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);
    }

    /**
     * Returns true if the specified parameter exists in the current event.
     *
     * @param string $key
     */
    public function hasParameter($key)
    {
        return $this->parameters->get($key) !== null;
    }

    /**
     * Returns the value of the given parameter or $default if it doesn't exist.
     *
     * @param string $key
     * @param mixed  $default
     */
    public function getParameter($key, $default = null)
    {
        return $this->parameters->get($key, $default);
    }

    /**
     * Returns the name of this event.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns true if this event is designd to only run once.
     *
     * @return boolean
     */
    public function isRunOnce()
    {
        return $this->run_once;
    }

    /**
     * Returns true if this event is already executed before.
     *
     * @return bool
     */
    public function isExecuted()
    {
        return $this->is_executed;
    }

    /**
     * Triggers this event.
     *
     * @param Invoker $invoker
     * @param array $named_args
     */
    public function trigger(Invoker $invoker, $named_args = array())
    {
        usort($this->hooks, function($a, $b){
            return $a['priority'] > $b['priority'] ? -1 : 1;
        });
        $named_args[] = $this;
        foreach($this->hooks as $hook) {
            $invoker->invoke($hook['callback'], $named_args);
        }
    }

    /**
     * Binds a callback to this event. Execution order is determined by the
     * priority of the callback, making 0 the least important and 100 the most.
     *
     * @param callable $callable
     * @param int $priority
     */
    public function bind(callable $callable, $priority = 50)
    {
        if ($priority < 0 || $priority > 100) {
            throw new \InvalidArgumentException('Bind-priority should range between 0 and 100.');
        }
        $this->hooks[] = array(
            'callback' => $callable,
            'priority' => $priority
        );
    }
}