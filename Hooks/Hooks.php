<?php

declare(strict_types=1);

namespace Async\Hook;

use Async\Hook\HooksInterface;

/**
 * This class is based on the WordPress hook/plugin API.
 *
 * @see https://core.trac.wordpress.org/browser/tags/5.3/src/wp-includes/plugin.php
 * @see https://core.trac.wordpress.org/browser/tags/5.3/src/wp-includes/class-wp-hook.php
 *
 * It's been modified to follow OOP and PSR-2 programming styles.
 */
class Hooks implements HooksInterface
{
    /**
     * Holds list of hooks
     * @var array
     */
    private static $filters = array();

    /**
     * @var array
     */
    private static $mergedFilters = array();

    /**
     * Tracks how many times a hook fired
     * @var array
     */
    private static $actions = array();

    /**
     * Holds the name of the current filter
     * @var array
     */
    private static $currentFilter = array();

    /**
     * @var HooksInterface
     */
    private static $_instance = null;

    private function __construct()
    {
        self::$currentFilter = array();
        self::$actions = array();
        self::$mergedFilters = array();
        self::$filters = array();
    }

    public function reset()
    {
        self::$currentFilter = array();
        self::$actions = array();
        self::$mergedFilters = array();
        self::$filters = array();
        self::$_instance = null;

        return $this;
    }

    public static function  getInstance(): HooksInterface
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getRegistered(string $identifier, $priority = 10): array
    {
        return isset(self::$filters[$identifier][$priority]) ? self::$filters[$identifier][$priority] : array();
    }

    public function addFilter(string $identifier, callable $function, int $priority = 10, int $accepted_args = 1): bool
    {
        $idx = self::__filterBuildUniqueId($identifier, $function, $priority);

        self::$filters[$identifier][$priority][$idx] = array(
            'function' => $function,
            'accepted_args' => $accepted_args
        );

        unset(self::$mergedFilters[$identifier]);

        return true;
    }

    public function removeFilter(string $identifier, ?callable $function, int $priority = 10): bool
    {
        $function = self::__filterBuildUniqueId($identifier, $function, $priority);

        $removed = isset(self::$filters[$identifier][$priority][$function]);

        if (true === $removed) {
            unset(self::$filters[$identifier][$priority][$function]);
            if (empty(self::$filters[$identifier][$priority]))
                unset(self::$filters[$identifier][$priority]);

            unset(self::$mergedFilters[$identifier]);
        }

        return $removed;
    }

    public function removeAllFilters(string $identifier = '', $priority = false): bool
    {
        if (isset(self::$filters[$identifier])) {
            if (false !== $priority && isset(self::$filters[$identifier][$priority]))
                unset(self::$filters[$identifier][$priority]);
            else
                unset(self::$filters[$identifier]);
        }

        if (isset(self::$mergedFilters[$identifier]))
            unset(self::$mergedFilters[$identifier]);

        if (empty($identifier) && !empty(self::$filters)) {
            self::$filters = [];
        }

        return true;
    }

    public function hasFilter(string $identifier = '', $function = false)
    {
        if (!empty($identifier)) {
            $has = !empty(self::$filters[$identifier]);
            if (false === $function || false == $has)
                return $has;

            if (!$idx = self::__filterBuildUniqueId($identifier, $function, false))
                return false;

            foreach ((array) \array_keys(self::$filters[$identifier]) as $priority) {
                if (isset(self::$filters[$identifier][$priority][$idx]))
                    return $priority;
            }
        }

        return false;
    }

    public function applyFilters(string $identifier, $value)
    {
        $args = array();
        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$currentFilter[] = $identifier;
            $args = \func_get_args();
            self::__callAllHook($args);
        }

        if (!isset(self::$filters[$identifier])) {
            if (isset(self::$filters['all']))
                \array_pop(self::$currentFilter);
            return $value;
        }

        if (!isset(self::$filters['all']))
            self::$currentFilter[] = $identifier;

        // Sort
        if (!isset(self::$mergedFilters[$identifier])) {
            \ksort(self::$filters[$identifier]);
            self::$mergedFilters[$identifier] = true;
        }

        \reset(self::$filters[$identifier]);

        if (empty($args))
            $args = \func_get_args();

        do {
            foreach ((array) \current(self::$filters[$identifier]) as $the_)
                if (!empty($the_['function'])) {
                    $args[1] = $value;
                    $value = \call_user_func_array($the_['function'], \array_slice($args, 1, (int) $the_['accepted_args']));
                }
        } while (\next(self::$filters[$identifier]) !== false);

        \array_pop(self::$currentFilter);

        return $value;
    }

    public function applyFiltersRefArray(string $identifier, array $args)
    {
        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$currentFilter[] = $identifier;
            $all_args = \func_get_args();
            self::__callAllHook($all_args);
        }

        if (!isset(self::$filters[$identifier])) {
            if (isset(self::$filters['all']))
                \array_pop(self::$currentFilter);
            return $args[0];
        }

        if (!isset(self::$filters['all']))
            self::$currentFilter[] = $identifier;

        // Sort
        if (!isset(self::$mergedFilters[$identifier])) {
            \ksort(self::$filters[$identifier]);
            self::$mergedFilters[$identifier] = true;
        }

        \reset(self::$filters[$identifier]);

        do {
            foreach ((array) \current(self::$filters[$identifier]) as $the_)
                if (!empty($the_['function']))
                    $args[0] = \call_user_func_array($the_['function'], \array_slice($args, 0, (int) $the_['accepted_args']));
        } while (\next(self::$filters[$identifier]) !== false);

        \array_pop(self::$currentFilter);

        return $args[0];
    }

    public function addAction(string $identifier, callable $function, int $priority = 10, $accepted_args = 1): bool
    {
        return $this->addFilter($identifier, $function, $priority, $accepted_args);
    }

    public function hasAction(string $identifier, $check = false)
    {
        return $this->hasFilter($identifier, $check);
    }

    public function removeAction(string $identifier, ?callable $function, $priority = 10): bool
    {
        return $this->removeFilter($identifier, $function, $priority);
    }

    public function removeAllActions(string $identifier = '', $priority = false): bool
    {
        return $this->removeAllFilters($identifier, $priority);
    }

    public function doAction(string $identifier, $arg = '')
    {
        $this->__bump_action($identifier);

        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$currentFilter[] = $identifier;
            $all_args = \func_get_args();
            self::__callAllHook($all_args);
        }

        if (!isset(self::$filters[$identifier])) {
            if (isset(self::$filters['all']))
                \array_pop(self::$currentFilter);
            return null;
        }

        if (!isset(self::$filters['all']))
            self::$currentFilter[] = $identifier;

        $args = array();
        if (\is_array($arg) && 1 == \count($arg) && isset($arg[0]) && \is_object($arg[0])) // array(&$this)
            $args[] = &$arg[0];
        else
            $args[] = $arg;

        for ($a = 2; $a < \func_num_args(); $a++)
            $args[] = \func_get_arg($a);

        // Sort
        if (!isset(self::$mergedFilters[$identifier])) {
            \ksort(self::$filters[$identifier]);
            self::$mergedFilters[$identifier] = true;
        }

        \reset(self::$filters[$identifier]);

        do {
            foreach ((array) \current(self::$filters[$identifier]) as $the_) {
                if (!empty($the_['function']))
                    \call_user_func_array($the_['function'], \array_slice($args, 0, (int) $the_['accepted_args']));
            }
        } while (\next(self::$filters[$identifier]) !== false);

        \array_pop(self::$currentFilter);
    }

    public function doActionRefArray(string $identifier, array $args)
    {
        $this->__bump_action($identifier);

        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$currentFilter[] = $identifier;
            $all_args = \func_get_args();
            self::__callAllHook($all_args);
        }

        if (!isset(self::$filters[$identifier])) {
            if (isset(self::$filters['all']))
                \array_pop(self::$currentFilter);

            return null;
        }

        if (!isset(self::$filters['all']))
            self::$currentFilter[] = $identifier;

        // Sort
        if (!isset(self::$mergedFilters[$identifier])) {
            \ksort(self::$filters[$identifier]);
            self::$mergedFilters[$identifier] = true;
        }

        \reset(self::$filters[$identifier]);

        do {
            foreach ((array) \current(self::$filters[$identifier]) as $the_) {
                if (!empty($the_['function']))
                    \call_user_func_array($the_['function'], \array_slice($args, 0, (int) $the_['accepted_args']));
            }
        } while (\next(self::$filters[$identifier]) !== false);

        \array_pop(self::$currentFilter);
    }

    public function didAction(string $identifier): int
    {
        if (!isset(self::$actions) || !isset(self::$actions[$identifier]))
            return 0;

        return self::$actions[$identifier];
    }

    public function currentFilter(): string
    {
        return \end(self::$currentFilter);
    }

    public function currentAction(): string
    {
        return $this->currentFilter();
    }

    public function doingFilter($filter = null): bool
    {
        if (null === $filter) {
            return !empty(self::$currentFilter);
        }
        return \in_array($filter, self::$currentFilter);
    }

    public function doingAction(string $action = null): bool
    {
        return $this->doingFilter($action);
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * @param string $identifier Used in counting how many hooks were applied
     * @param callable|mixed $function Used for creating unique id
     * @param int|bool $priority Used in counting how many hooks were applied.
     * - If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
     *
     * @return string|bool Unique ID for usage as array key or false
     * - if $priority === false and $function is an object reference, and it does not already have a unique id.
     */
    protected static function __filterBuildUniqueId(string $identifier, $function, $priority)
    {
        static $filter_id_count = 0;

        if (\is_string($function))
            return $function;

        if (\is_object($function)) {
            // Closures are currently implemented as objects
            $function = array($function, '');
        } else {
            $function = (array) $function;
        }

        if (isset($function[0]) && \is_object($function[0])) {
            // Object Class Calling
            return \spl_object_hash($function[0]) . $function[1];
        } elseif (isset($function[0]) && \is_string($function[0])) {
            // Static Calling
            return $function[0] . '::' .$function[1];
        }
    }

    /**
     * @param $args [description]
     */
    protected static function __callAllHook($args)
    {
        \reset(self::$filters['all']);
        do {
            foreach ((array) \current(self::$filters['all']) as $the_)
                if (!empty($the_['function']))
                    \call_user_func_array($the_['function'], $args);
        } while (\next(self::$filters['all']) !== false);
    }

    /**
     * Common script before running an action or applying a filter:
     *    - increase hits count
     *
     * @param string $identifier
     */
    protected function __bump_action($identifier)
    {
        if (!isset(self::$actions)) {
            self::$actions = array();
        }

        if (!isset(self::$actions[$identifier])) {
            self::$actions[$identifier] = 1;
        } else {
            ++self::$actions[$identifier];
        }
    }
}
