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
namespace Unity\Component\Event;

use Unity\Component\Kernel\Invoker;
use Unity\Component\Service\ServiceManager;
use Unity\Component\Service\Service;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class EventManager extends Service
{
    /**
     * @var \Unity\Component\Kernel\Invoker
     */
    private $invoker = null,
            $events  = array();

    public function __construct()
    {
        $this->setName('event-manager')
             ->addDependency('invoker');
    }

    /**
     * @param Invoker $invoker
     */
    protected function configure(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Registers a new event.
     *
     * @param Event $event
     * @throws EventAlreadyExistsException
     */
    public function register(Event $event)
    {
        if( isset($this->events[$event->getName()])) {
            throw new EventAlreadyExistsException($event->getName());
        }
        $this->events[$event->getName()] = $event;
    }

    /**
     * Returns an event.
     *
     * @param string $name
     * @throws EventNotFoundException
     * @return \Unity\Component\Event\Event
     */
    public function getEvent($name)
    {
        if (!isset($this->events[$name])) {
            throw new EventNotFoundException($name);
        }
        return $this->events[$name];
    }

    /**
     * Triggers an event.
     *
     * @param mixed $event
     * @param array $named_args
     * @throws EventNotFoundException
     */
    public function trigger($event, $named_args = array())
    {
        if(!$event instanceof Event) {
            if(!isset($this->events[$event])) {
                throw new EventNotFoundException($event);
            }
            $event = $this->events[$event];
        }
        $event->trigger($this->invoker, $named_args);
    }
}