<?php

declare(strict_types=1);

namespace Async\Hook;

interface HooksInterface
{
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
     * This allow other `functions/methods` to modify various types of internal data at runtime.
     *
     * A `function/method` can modify data by binding a callback to a filter hook. When the filter
     * is later applied, each bound callback is run in order of priority, and given
     * the opportunity to modify a value by returning a new value.
     *
     * The following example shows how a callback function is bound to a filter hook.
     *
     * Note that `$example` is passed to the callback, (maybe) modified, then returned:
     *
     *     function example_callback( $example ) {
     *         // Maybe modify $example in some way.
     *         return $example;
     *     }
     *     add_filter( 'example_filter', 'example_callback' );
     *
     * Bound callbacks can accept from none to the total number of arguments passed as parameters
     * in the corresponding apply_filters() call.
     *
     * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
     * it can accept none (the same as 1) of the arguments or up to four. The important part is that
     * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
     * opted to accept. If no arguments were accepted by the callback that is considered to be the
     * same as accepting 1 argument. For example:
     *
     *     // Filter call.
     *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
     *
     *     // Accepting zero/one arguments.
     *     function example_callback() {
     *         ...
     *         return 'some value';
     *     }
     *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
     *
     *     // Accepting two arguments (three possible).
     *     function example_callback( $value, $arg2 ) {
     *         ...
     *         return $maybe_modified_value;
     *     }
     *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
     *
     * *Note:* The function will return true whether or not the callback is valid.
     * It is up to you to take care. This is done for optimization purposes, so
     * everything is as quick as possible.
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
     * This function removes a function attached to a specified filter hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * To remove a hook, the $function and $priority arguments must match
     * when the hook was added. This goes for both filters and actions. No warning
     * will be given on removal failure.
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
     * Calls the callback functions that have been added to a filter identifier hook.
     *
     * The callback functions attached to the filter hook are invoked by calling
     * this function. This function can be used to create a new filter hook by
     * simply calling this function with the name of the new hook specified using
     * the `$tag` parameter.
     *
     * The function also allows for multiple additional arguments to be passed to hooks.
     *
     * Example usage:
     *
     *     // The filter callback function
     *     function example_callback( $string, $arg1, $arg2 ) {
     *         // (maybe) modify $string
     *         return $string;
     *     }
     *     add_filter( 'example_filter', 'example_callback', 10, 3 );
     *
     *     /*
     *      * Apply the filters by calling the 'example_callback()' function that's
     *      * hooked onto `example_filter` above.
     *      *
     *      * - 'example_filter' is the filter hook
     *      * - 'filter me' is the value being filtered
     *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
     *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
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
