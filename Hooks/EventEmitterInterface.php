<?php

declare(strict_types=1);

namespace Async\Hook;

interface EventEmitterInterface
{
    /**
     * Register/Adds the given `event` listener/function to the list of event listeners that listen to the given event.
     *
     * Delegates to Hooks `add_action`.
     *
     * @param string $event
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function on($event, $function_to_add, int $priority = 10, int $acceptedArgs = 1);

    /**
     * Adds the given `event` listener to happen exactly once.
     *
     * This can `ONLY` be used with `emit()`
     *
     * @param string $event
     * @param callable $function_to_add
     */
    public function once($event, $function_to_add);

    /**
     * Remove listeners from an `event`.
     *
     * Delegates to Hooks `remove_action`.
     *
     * @param string $event
     * @param callable $function_to_remove
     * @param int $priority
     */
    public function off($event, ?callable $function_to_remove = null, int $priority = 10);

    /**
     * Register a `filter` listener with a name.
     *
     * Delegates to Hooks `add_filter`.
     *
     * @param string $name
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function add($name, $function_to_add, int $priority = 10, int $acceptedArgs = 1);

    /**
     * Removes the given filter name from the list of `event` listeners that listen to the given event.
     *
     * Delegates to Hooks `remove_filter`.
     *
     * @param string $name
     * @param callable $function_to_remove
     * @param int $priority
     */
    public function clear($name, $function_to_remove, int $priority = 10);

    /**
     * Removes all `name`, `event` listeners.
     *
     * Delegates to Hooks `remove_all_filters`.
     *
     * @param string $name
     * @param int $priority
     */
    public function cancel(string $name = '', int $priority = 10);

    /**
     * Filters the given value by applying all the changes from the `event`
     * registered with the given name.
     *
     * Delegates to Hooks `apply_filters`.
     *
     * Returns the filtered value.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function trigger(?string $name, ...$value);

    /**
     * No tracking, not prioritized, just executes all the `listeners` registered with the event directly.
     *
     * @param string $event
     * @param mixed $args
     */
    public function emit(?string $event, ...$args);

    /**
     * Executes all the `listeners` registered with the given event.
     *
     * Delegates to Hooks `do_action`.
     *
     * @param string $event
     * @param mixed $args
     */
    public function dispatch(?string $event, ...$args);

    /**
     * Add listener for event that will start to be invoked after $ticks number of `events` is emitted.
     *
     * Delegates to Hooks `add_action`.
     *
     * @param string $event
     * @param int $ticks
     * @param callable $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function delay(string $event, int $ticks, callable $function_to_add, int $priority = 10, int $acceptedArgs = 1);

    /**
     * Is an event listener registered for the given event
     *
     * Delegates to Hooks `has_action`.
     *
     * @param  string $event
     * @param  mixed  $function_to_check
     * @return boolean
     */
    public function hasEvent(string $event, $function_to_check = false);

    /**
     * Has a filter function been registered for a given filter name
     *
     * Delegates to Hooks `has_filter`.
     *
     * @param string $name
     * @param mixed $function_to_check
     * @return boolean
     */
    public function hasName(string $name, $function_to_check = false);

    /**
     * Return the listeners for a given event
     *
     * @param string|null $event
     * @return array
     */
    public function listeners(?string $event);
}
