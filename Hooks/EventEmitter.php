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

    public function on($event, $function_to_add, int $priority = 10, int $acceptedArgs = 1)
    {
        if (!\is_callable($function_to_add)) {
            throw new \InvalidArgumentException("The provided ".$function_to_add." is not a valid callable.");
        }

        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }

        $this->addListener($event, $function_to_add);

        return $this->hook->addAction($event, $function_to_add, $priority, $acceptedArgs);
    }

    public function once($event, $function_to_add)
    {
        if (!\is_callable($function_to_add)) {
            throw new \InvalidArgumentException("The provided ".$function_to_add." is not a valid callable.");
        }

        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }

        $this->addListener($event, $function_to_add, true);
    }

    public function off($event, ?callable $function_to_remove = null, int $priority = 10)
    {
        if ($function_to_remove !== null) {
            $this->removeListener($event, $function_to_remove);
        }

        return $this->hook->removeAction($event, $function_to_remove, $priority);
    }

    public function add($name, $function_to_add, int $priority = 10, int $acceptedArgs = 1)
    {
        if (!\is_callable($function_to_add)) {
            throw new \InvalidArgumentException("The provided ".$function_to_add." is not a valid callable.");
        }

        if ($name === null) {
            throw new \InvalidArgumentException('filter name must not be null');
        }

        $this->addListener($name, $function_to_add);

        return $this->hook->addFilter($name, $function_to_add, $priority, $acceptedArgs);
    }

    public function clear($name, $function_to_remove, int $priority = 10)
    {
        if ($function_to_remove !== null) {
            $this->removeListener($name, $function_to_remove);
        }

        return $this->hook->removeFilter($name, $function_to_remove, $priority);
    }

    public function cancel(string $name = '', int $priority = 10)
    {
        $this->removeAllListeners($name);

        return $this->hook->removeAllFilters($name, $priority);
    }

    public function trigger(?string $name, ...$value)
    {
        if ($name === null) {
            throw new \InvalidArgumentException('filter name must be string and not be null');
        }

        return $this->hook->applyFilters($name, ...$value);
    }

    public function emit(?string $event, ...$args)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must be string and not be null');
        }

         if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $listener( ...$args);
            }
        }

        if (isset($this->listeners[$event.'_only_once'])) {
            $listeners = $this->listeners[$event.'_only_once'];
            unset($this->listeners[$event.'_only_once']);
            foreach ($listeners as $listener) {
                $listener( ...$args);
            }
        }
    }

    public function dispatch(?string $event, ...$args)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must be string and not be null');
        }

        return $this->hook->doAction($event, ...$args);
    }

    public function delay(string $event, int $ticks, callable $function_to_add, int $priority = 10, int $acceptedArgs = 1)
    {
        $counter = 0;
        return $this->on($event, function(...$args) use (&$counter, $ticks, $function_to_add) {
            if (++$counter >= $ticks) {
                \call_user_func_array($function_to_add, $args);
            }
        }, $priority, $acceptedArgs);
    }

    public function hasEvent(string $event, $function_to_check = false)
    {
        return $this->hasListener('Action', $event, $function_to_check);
    }

    public function hasName(string $name, $function_to_check = false)
    {
        return $this->hasListener('Filter', $name, $function_to_check);
    }

    public function listeners(?string $event)
    {
        if ($event === null) {
            $events = [];
            $eventNames = \array_unique(\array_keys($this->listeners));
            foreach ($eventNames as $eventName) {
                $events[$eventName] = \array_merge(
                    isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : [],
                    isset($this->listeners[$event.'_only_once']) ? $this->listeners[$event.'_only_once'] : []
                );
            }

            return $events;
        }

        return \array_merge(
            isset($this->listeners[$event]) ? $this->listeners[$event] : [],
            isset($this->listeners[$event.'_only_once']) ? $this->listeners[$event.'_only_once'] : []
        );
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
        $hasListener = 'has'.$type;

        return (\call_user_func([$this->hook, $hasListener], $hook, $function_to_check) === false)
            ? false
            : true;
    }

    /**
     * Add a non prioritized listener
     *
     * @param $hook
     * @param $function_to_add
     * @param $isOnce
     */
    protected function addListener($hook, $function_to_add, bool $isOnce = false)
    {
        $once = ($isOnce) ? $hook.'_only_once' : $hook;
        if (!isset($this->listeners[$once])) {
            $this->listeners[$once] = [];
        }

        $this->listeners[$once][] = $function_to_add;
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

        if (isset($this->listeners[$event.'_only_once'])) {
            $index = \array_search($function_to_remove, $this->listeners[$event.'_only_once'], true);
            if (false !== $index) {
                unset($this->listeners[$event.'_only_once'][$index]);
                if (\count($this->listeners[$event.'_only_once']) === 0) {
                    unset($this->listeners[$event.'_only_once']);
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
            if (isset($this->listeners[$event.'_only_once']))
                unset($this->listeners[$event.'_only_once']);
        } else {
            $this->listeners = [];
        }
    }
}
