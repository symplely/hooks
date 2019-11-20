<?php

declare(strict_types=1);

use Async\Hook\Hooks;

if (!\function_exists('___shutdown')) {
    function hooks_instance()
    {
        return Hooks::getInstance();
    }

    function hooks_registered($tag, $priority = 10)
    {
        return hooks_instance()->getRegistered($tag, $priority);
    }

    function hooks_reset()
    {
        return hooks_instance()->reset();
    }

    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return hooks_instance()->addAction($tag, $function_to_add, $priority, $accepted_args);
    }

    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return hooks_instance()->addFilter($tag, $function_to_add, $priority, $accepted_args);
    }

    function apply_filters($tag, ...$value)
    {
        return hooks_instance()->applyFilters($tag, ...$value);
    }

    function do_action($tag, ...$arg)
    {
        return hooks_instance()->doAction($tag, ...$arg);
    }

    function has_action($tag, $function_to_check = false)
    {
        return hooks_instance()->hasAction($tag, $function_to_check);
    }

    function has_filter($tag, $function_to_check = false)
    {
        return hooks_instance()->hasFilter($tag, $function_to_check);
    }

    function remove_action($tag, $function_to_remove, $priority = 10)
    {
        return hooks_instance()->removeAction($tag, $function_to_remove, $priority);
    }

    function remove_filter($tag, $function_to_remove, $priority = 10)
    {
        return hooks_instance()->removeFilter($tag, $function_to_remove, $priority);
    }

    function remove_all_filters($tag, $priority = false)
    {
        return hooks_instance()->removeAllFilters($tag, $priority);
    }

    function remove_all_actions($tag, $priority = false)
    {
        return hooks_instance()->removeAllActions($tag, $priority);
    }

    function did_action($tag)
    {
        return hooks_instance()->didAction($tag);
    }

    function doing_filter($filter = null)
    {
        return hooks_instance()->doingFilter($filter);
    }

    function doing_action($action = null)
    {
        return hooks_instance()->doingAction($action);
    }

    function current_filter()
    {
        return hooks_instance()->currentFilter();
    }

    function current_action()
    {
        return hooks_instance()->currentAction();
    }

    function do_action_ref_array($tag, ...$args)
    {
        return hooks_instance()->doActionRefArray($tag, ...$args);
    }

    function apply_filters_ref_array($tag, ...$args)
    {
        return hooks_instance()->applyFiltersRefArray($tag, ...$args);
    }

    /**
     * Runs just before PHP shuts down execution.
     */
    function ___shutdown()
    {
        /**
         * Fires just before PHP shuts down execution.
         */
        hooks_instance()->doAction('shutdown');
    }

    \register_shutdown_function('___shutdown');

    \do_action('after_hooks_setup');
}
