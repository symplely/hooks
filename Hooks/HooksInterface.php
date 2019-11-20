<?php

declare(strict_types=1);

namespace Async\Hook;

interface HooksInterface
{
    public static function getInstance(): HooksInterface;

    /**
     * Return array of callback hooks registered for identifier.
     *
     * @param string $identifier
     * @param int $priority - optional.
     *
     * @return array
     */
    public function getRegistered(string $identifier, $priority = 10): array;

    /**
     * Hooks a function or method to a specific filter identifier action.
     *
     * @param string $identifier The name identifier to bind a filter $function onto.
     * @param callable $function The name of the function to be called when the filter is applied.
     * @param int $priority - optional.
     *  Used to specify the order in which the functions associated with a particular action are executed (default: 10).
     *  Lower numbers correspond with earlier execution, and functions with the same priority are executed
     *  in the order in which they were added to the action.
     * @param int $accepted_args - optional. The number of arguments the function accept (default 1).
     *
     * @return boolean true
     */
    public function addFilter(string $identifier, callable $function, int $priority = 10, int $accepted_args = 1): bool;

    /**
     * Removes a function from a specified filter identifier hook.
     *
     * @param string $identifier The filter identifier hook to which the function to be removed is hooked.
     * @param callable $function The name of the function which should be removed.
     * @param int $priority - optional. The priority of the function (default: 10).
     * @param int $accepted_args - optional. The number of arguments the function accepts (default: 1).
     *
     * @return boolean Whether the function existed before it was removed.
     */
    public function removeFilter(string $identifier, ?callable $function, int $priority = 10): bool;

    /**
     * Remove all of the hooks from a filter.
     *
     * @param string $identifier The filter identifier to remove hooks from.
     * @param int $priority The priority number to remove.
     *
     * @return bool True when finished.
     */
    public function removeAllFilters(string $identifier = '', $priority = false): bool;

    /**
     * Check if any filter identifier has been registered for a hook.
     *
     * @param string $identifier The name of the filter identifier hook.
     * @param callable $function optional.
     *
     * @return mixed If $function is omitted,
     * - returns boolean for whether the hook has anything registered.
     * - When checking a specific function, the priority of that hook is returned,
     *      or false if the function is not attached.
     * - When using the $function_to_check argument, this function may return a
     *      non-boolean value that evaluates to false (e.g.) 0,
     *      so use the === operator for testing the return value.
     */
    public function hasFilter(string $identifier = '', $function = false);

    /**
     * Call the functions added to a filter identifier hook.
     *
     * @param string $identifier The name of the filter identifier hook.
     * @param mixed $value The value on which the filters identifier hooked onto are applied on.
     * @param mixed $var,... Additional variables passed to the functions hooked onto.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFilters(string $identifier, $value);

    /**
     * Execute functions hooked on a specific filter identifier hook, specifying arguments in an array.
     *
     * @param string $identifier The name of the filter identifier hook.
     * @param array $args The arguments supplied to the functions hooked onto.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFiltersRefArray(string $identifier, array $args);

    /**
     * Hooks a function on to a specific identifier action.
     *
     * @param string $identifier The name identifier of the action to which the $function is bind onto.
     * @param callable $function The name of the function you wish to be called.
     * @param int $priority - optional. Used to specify the order in which the functions associated
     *  with a particular action are executed (default: 10).
     *  Lower numbers correspond with earlier execution, and functions with the same priority are
     *  executed in the order in which they were added to the action.
     * @param int $accepted_args optional. The number of arguments the function accept (default 1).
     */
    public function addAction(string $identifier, callable $function, int $priority = 10, $accepted_args = 1): bool;

    /**
     * Check if any action has been registered for the identifier hook.
     *
     * @param string $identifier The name identifier of the action hook.
     * @param callable $check optional.
     *
     * @return mixed If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
     *   When checking a specific function, the priority of that hook is returned,
     *      or false if the function is not attached.
     *   When using the $function_to_check argument, this function may return a
     *      non-boolean value that evaluates to false (e.g.) 0,
     *      so use the === operator for testing the return value.
     */
    public function hasAction(string $identifier, $check = false);

    /**
     * Removes a function from a specified identifier action hook.
     *
     * @param string $identifier The identifier action hook to which the function to be removed is hooked.
     * @param callable $function The name of the function which should be removed.
     * @param int $priority optional The priority of the function (default: 10).
     * @return boolean Whether the function is removed.
     */
    public function removeAction(string $identifier, ?callable $function, $priority = 10): bool;

    /**
     * Remove all of the hooks from an identifier action.
     *
     * @param string $identifier The identifier action to remove hooks from.
     * @param int $priority The priority number to remove them from.
     *
     * @return bool True when finished.
     */
    public function removeAllActions(string $identifier = '', $priority = false): bool;

    /**
     * Execute functions hooked on a specific identifier action hook.
     *
     * @param string $identifier The name identifier of the action to be executed.
     * @param mixed $arg,... Optional additional arguments which are passed on to the functions hooked onto the action.
     *
     * @return null Will return null if $identifier does not exist in $filter array
     */
    public function doAction(string $identifier, $arg = '');

    /**
     * Execute functions hooked on a specific identifier action hook, specifying arguments in an array.
     *
     * @param string $identifier The name identifier of the action to be executed.
     * @param array $args The arguments supplied to the functions hooked onto
     *
     * @return null Will return null if $identifier does not exist in $filter array
     */
    public function doActionRefArray(string $identifier, array $args);

    /**
     * Retrieve the number of times an identifier action is fired.
     *
     * @param string $identifier The name identifier of the action hook.
     *
     * @return int The number of times action identifier hook is fired
     */
    public function didAction(string $identifier): int;

    /**
     * Retrieve the name of the current identifier filter or action.
     *
     * @return string Hook name identifier of the current filter or action.
     */
    public function currentFilter(): string;

    /**
     * Retrieve the name of the current action.
     *
     * @uses current_filter()
     *
     * @return string Hook name identifier of the current action.
     */
    public function currentAction(): string;

    /**
     * Retrieve the name of a filter currently being processed.
     *
     * The function current_filter() only returns the most recent filter or action
     * being executed. did_action() returns true once the action is initially
     * processed. This function allows detection for any filter currently being
     * executed (despite not being the most recent filter to fire, in the case of
     * hooks called from hook callable) to be verified.
     *
     * @see current_filter()
     * @see did_action()
     * @global array $current_filter Current filter.
     *
     * @param null|string $filter Optional. Filter to check. Defaults to null,
     * - which checks if any filter is currently being run.
     *
     * @return bool Whether the filter is currently in the stack
     */
    public function doingFilter($filter = null): bool;

    /**
     * Retrieve the name identifier of an action currently being processed.
     *
     * @uses doing_filter()
     *
     * @param string|null $action Optional. Action to check. Defaults to null, which checks
     * - if any action is currently being run.
     *
     * @return bool Whether the action is currently in the stack.
     */
    public function doingAction(string $action = null): bool;
}
