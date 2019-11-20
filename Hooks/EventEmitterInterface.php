<?php

declare(strict_types=1);

namespace Async\Hook;

interface EventEmitterInterface
{
    /**
     * Register/Adds the given event listener/function to the list of event listeners that listen to the given event.
     *
     * @param string $event
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function on($event, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * Adds the given event listener to happen exactly once.
     *
     * @param string $event
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function once($event, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * Remove listeners from an event
     *
     * @param string $event
     * @param callable $function_to_remove
     * @param int $priority
     */
    public function off($event, $function_to_remove, $priority = 10);

    /**
     * Register a filter with a name.
     *
     * @param string $name
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     * @return $this
     */
    public function add($name, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * Removes the given filer name from the list of event listeners that listen to the given event.
     *
     * @param string $name
     * @param callable $function_to_remove
     * @param int $priority
     */
    public function clear($name, $function_to_remove, $priority = 10);

    /**
     * Removes all name event listeners.
     *
     * @param string $name
     * @param int $priority
     */
    public function cancel($name = '', $priority = 10);

    /**
     * Filters the given value by applying all the changes from the event
     * registered with the given name. Returns the filtered value.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function trigger($name, ...$value);

    /**
     * Executes all the listeners/functions registered with the given event.
     *
     * @param string $event
     * @param mixed $args
     */
    public function emit($event, ...$args);

    /**
     * Add listener for event that will start to be invoked after $ticks number of events is emitted.
     *
     * @param string $event
     * @param int $ticks
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function delay($event, $ticks, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * Is an event listener registered for the given event
     *
     * Delegates to Hooks `has_action` when present, or falls back
     * to internal listener queue for testing purposes.
     *
     * @param  string $event
     * @param  mixed  $function_to_check
     * @return boolean
     */
    public function hasEvent($event, $function_to_check = false);

    /**
     * Has a filter function been registered for a given filter name
     *
     * Delegates to Hooks `has_filter` when present, or falls back
     * to internal listener queue for testing purposes.
     *
     * @param string $name
     * @param mixed $function_to_check
     * @return boolean
     */
    public function hasName($name, $function_to_check = false);

    /**
     * Return the listeners for a given event
     *
     * @param string|null $event
     * @return array
     */
    public function listeners($event = null);
}
