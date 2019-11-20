<?php

declare(strict_types=1);

namespace Async\Hook;

use Async\Hook\Hooks;
use Async\Hook\HooksInterface;
use Async\Hook\EventEmitterInterface;

/**
 * EventEmitter manages and interacts with the Hooks API.
 */
class EventEmitter implements EventEmitterInterface
{
    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var HooksInterface
     */
    protected $hook;

    public function __construct(?HooksInterface $hook = null)
    {
        $this->hook = empty($hook) ? Hooks::getInstance() : $hook;
    }

    public function on($event, $function_to_add, $priority = 10, $acceptedArgs = 1)
    {
        if (!\is_callable($function_to_add)) {
            throw new \InvalidArgumentException("The provided " . $function_to_add . " is not a valid callable.");
        }

        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }

        $this->addListener($event, $function_to_add);

        return $this->hook->addAction($event, $function_to_add, $priority, $acceptedArgs);
    }

    public function once($event, $function_to_add, $priority = 10, $acceptedArgs = 1)
    {
        $once = function () use (&$once, $event, $function_to_add) {
            $this->off($event, $once);
            \call_user_func_array($function_to_add, \func_get_args());
        };

        return $this->on($event, $once, $priority, $acceptedArgs);
    }

    public function off($event, $function_to_remove = null, $priority = 10)
    {
        if ($function_to_remove !== null) {
            $this->removeListener($event, $function_to_remove);
        }

        return  $this->hook->removeAction($event, $function_to_remove, $priority);
    }

    public function add($name, $function_to_add, $priority = 10, $acceptedArgs = 1)
    {
        if (!is_callable($function_to_add)) {
            throw new \InvalidArgumentException("The provided " . $function_to_add . " is not a valid callable.");
        }

        if ($name === null) {
            throw new \InvalidArgumentException('filter name must not be null');
        }

        $this->addListener($name, $function_to_add);

        return  $this->hook->addFilter($name, $function_to_add, $priority, $acceptedArgs);
    }

    public function clear($name, $listener = null, $priority = 10)
    {
        if ($listener !== null) {
            $this->removeListener($name, $listener);
        }

        return  $this->hook->removeFilter($name, $listener, $priority);
    }

    public function cancel($name = '', $priority = 10)
    {
        $this->removeAllListeners($name);

        return  $this->hook->removeAllFilters($name, $priority);
    }

    public function trigger($name, ...$value)
    {
        if ($name === null) {
            throw new \InvalidArgumentException('filter name must not be null');
        }

        return  $this->hook->applyFilters($name, ...$value);
    }

    public function emit($event, ...$args)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }

        // Using WordPress Hook API gives 4x slower response, the following edits beats Evenement on benchmarks
        // The Hook API doing much more that just simply calling the callbacks.
        //if (isset($this->listeners[$event])) {
        //    foreach ($this->listeners[$event] as $listener) {
        //        $listener( ...$args);
        //    }
        //}

        //return  $this->hook->doAction($event, ...$args);
        return  $this->hook->justDoAction($event, ...$args);
    }

    public function delay($event, $ticks, $function_to_add, $priority = 10, $acceptedArgs = 1)
    {
        $counter = 0;
        return $this->on($event, function(...$args) use (&$counter, $event, $ticks, $function_to_add) {
            if (++$counter >= $ticks) {
                \call_user_func_array($function_to_add, $args);
            }
        }, $priority, $acceptedArgs);
    }

    public function hasEvent($event, $function_to_check = false)
    {
        return $this->hasListener('Action', $event, $function_to_check);
    }

    public function hasName($name, $function_to_check = false)
    {
        return $this->hasListener('Filter', $name, $function_to_check);
    }

    public function listeners($event = null)
    {
        return isset($this->listeners[$event]) ? $this->listeners[$event] : array();
    }

    /**
     * Check if listener exists for type, hook, and function.
     * The priority of the callback will be returned or false.
     * If no callback is given will return true or false if
     * there's any callbacks registered to the hook.
     *
     * @param  string  $type
     * @param  string  $hook
     * @param  mixed $function_to_check
     * @return boolean
     */
    protected function hasListener($type, $hook, $function_to_check = false)
    {
        $hasListener = 'has' . $type;

        return (\call_user_func([$this->hook, $hasListener], $hook, $function_to_check) === false)
            ? false
            : true;
    }

    /**
     * Add a prioritized listener
     *
     * @param $hook
     * @param $function_to_add
     * @param $priority
     */
    protected function addListener($hook, $function_to_add)
    {
        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = [];
        }

        $this->listeners[$hook][] = $function_to_add;
    }

    /**
     * Remove a listener
     *
     * @param string $event
     * @param mixed $function_to_remove
     * @return void
     */
    protected function removeListener($event, $function_to_remove)
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        if (isset($this->listeners[$event])) {
            $index = \array_search($function_to_remove, $this->listeners[$event], true);
            if (false !== $index) {
                unset($this->listeners[$event][$index]);
                if (\count($this->listeners[$event]) === 0) {
                    unset($this->listeners[$event]);
                }
            }
        }
    }

    /**
     * Removes all listeners.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed. If it is not specified, every listener for every event is
     * removed.
     */
    protected function removeAllListeners($event = null)
    {
        if ($event !== null) {
            unset($this->listeners[$event]);
        } else {
            $this->listeners = [];
        }
    }
}
