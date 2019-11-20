<?php

namespace Async\Hook\Tests;

use Async\Hook\Hooks;
use PHPUnit\Framework\TestCase;

/**
 * Class HooksTest
 */
class HooksFilterTest extends TestCase
{
    /**
     *
     * @var Hooks
     */
    protected $hooks;

    protected $action_output;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->hooks = Hooks::getInstance();
    }

    public function testAdd_filter()
    {
        $content = 'Hello world';

        $this->hooks->addFilter('fooo', function ($contents) {
            $changed = $contents . 'test';
            return '<b>' . $changed . '</b>';
        });

        $this->assertEquals(apply_filters('fooo', $content), '<b>Hello worldtest</b>');
    }

	function test_has_filter()
	{
		$tag  = __FUNCTION__;
		$func = function () {};

		$this->assertFalse(has_Filter($tag, $func));
		$this->assertFalse(has_Filter($tag));
		add_filter($tag, $func);
		$this->assertEquals(10, has_filter($tag, $func));
		$this->assertTrue(has_filter($tag));
		remove_filter($tag, $func);
		$this->assertFalse(has_filter($tag, $func));
		$this->assertFalse(has_filter($tag));
    }

    public function test_add_two_filters_with_same_priority()
    {
		$callback_one = [$this, '__return_null'];
		$callback_two = [$this, '__return_false'];
        $hook = Hooks::getInstance();
        $tag = __FUNCTION__;
        $priority = rand(1, 100);
        $accepted_args = rand(1, 100);

        $hook->addFilter($tag, $callback_one, $priority, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));

        $hook->addFilter($tag, $callback_two, $priority, $accepted_args);
        $this->assertCount(2, $hook->getRegistered($tag, $priority));
    }

    public function test_add_two_filters_with_different_priority()
    {
		$callback_one = [$this, '__return_null'];
		$callback_two = [$this, '__return_false'];
        $hook = Hooks::getInstance();
        $tag = __FUNCTION__;
        $priority = rand(1, 100);
        $accepted_args = rand(1, 100);

        $hook->addFilter($tag, $callback_one, $priority, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));

        $hook->addFilter($tag, $callback_two, $priority + 1, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));
        $this->assertCount(1, $hook->getRegistered($tag, $priority + 1));
    }

    public function test_readdFilter()
    {
        $callback = [$this, '__return_null'];
        $hook = Hooks::getInstance();
        $tag = __FUNCTION__;
        $priority = rand(1, 100);
        $accepted_args = rand(1, 100);

        $hook->addFilter($tag, $callback, $priority, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));

        $hook->addFilter($tag, $callback, $priority, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));
    }

    public function test_readdFilter_with_different_priority()
    {
        $callback = [$this, '__return_null'];
        $hook = Hooks::getInstance();
        $tag = __FUNCTION__;
        $priority = rand(1, 100);
        $accepted_args = rand(1, 100);

        $hook->addFilter($tag, $callback, $priority, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));

        $hook->addFilter($tag, $callback, $priority + 1, $accepted_args);
        $this->assertCount(1, $hook->getRegistered($tag, $priority));
        $this->assertCount(1, $hook->getRegistered($tag, $priority + 1));
    }

    public function test_remove_and_add_last_action()
    {
        $this->action_output = '';

        $this->hooks->addFilter('remove_and_add_action', [$this, '__return_empty_string'], 10, 0);

        $this->hooks->addFilter('remove_and_add_action', array($this, '_action_remove_and_add1'), 11, 0);

        $this->hooks->addFilter('remove_and_add_action', array($this, '_action_remove_and_add2'), 12, 0);

        $this->hooks->doAction('remove_and_add_action');

        $this->assertSame('12', $this->action_output);
    }

	public function testHasFilter()
	{
		$hooks = Hooks::getInstance();
		$this->assertSame(true, $hooks->removeAllFilters('testFilter'));
		$this->assertFalse($hooks->hasFilter(''));
		$this->assertFalse($hooks->hasFilter(' '));
		$this->assertFalse($hooks->hasFilter('testFilter'));
		$this->assertFalse($hooks->hasFilter('testFilter', 'time'));
		$this->assertSame(true, $hooks->addFilter('testFilter', 'time'));
		$this->assertTrue($hooks->hasFilter('testFilter', 'time') !== false);
		$this->assertFalse($hooks->hasFilter('testFilter', 'print_r'));
		$this->assertTrue($hooks->hasFilter('testFilter'));
		$this->assertFalse($hooks->hasFilter('notExistingFilter'));
	}

	public function test_hasFilter_with_function()
	{
        $callback = [$this, '__return_null'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback, $priority, $accepted_args);

		$this->assertEquals($priority, $hook->hasFilter($tag, $callback));
	}

	public function test_hasFilter_without_callback()
	{
        $callback = [$this, '__return_null'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback, $priority, $accepted_args);

		$this->assertTrue($hook->hasFilter($tag));
	}

	public function test_not_hasFilter_without_callback()
	{
		$hook = Hooks::getInstance();
		$this->assertFalse($hook->hasFilter());
	}

	public function test_not_hasFilter_with_callback()
	{
        $callback = [$this, '__return_null'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;

		$this->assertFalse($hook->hasFilter($tag, $callback));
	}

	public function test_hasFilter_with_wrong_callback()
	{
        $callback = [$this, '__return_null'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback, $priority, $accepted_args);

		$this->assertFalse($hook->hasFilter($tag, '__return_false'));
    }

	public function testRemoveAllFilters()
	{
		$hooks = Hooks::getInstance();
		$this->assertSame(true, $hooks->removeAllFilters('testFilter'));
		$this->assertSame(true, $hooks->addFilter('testFilter', 'time', 10));
		$this->assertSame(true, $hooks->addFilter('testFilter', 'print_r', 10));
		$this->assertSame(true, $hooks->addFilter('testFilter', 'time', 25));
		$this->assertTrue($hooks->removeAllFilters('testFilter', 10));
		$this->assertTrue($hooks->hasFilter('testFilter'));
		$this->assertSame(25, $hooks->hasFilter('testFilter', 'time'));
		$this->assertTrue($hooks->removeAllFilters('testFilter'));
		$this->assertFalse($hooks->hasFilter('testFilter'));
	}

	public function test_removeAllFilters()
	{
        $callback = [$this, '__return_null'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback, $priority, $accepted_args);

		$hook->removeAllFilters();

		$this->assertFalse($hook->hasFilter($tag));
	}

	public function test_removeAllFilters_with_priority()
	{
		$callback_one = [$this, '__return_null'];
		$callback_two = [$this, '__return_false'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback_one, $priority, $accepted_args);
		$hook->addFilter($tag, $callback_two, $priority + 1, $accepted_args);

		$hook->removeAllFilters($tag, $priority);

		$this->assertFalse($hook->hasFilter($tag, $callback_one));
		$this->assertTrue($hook->hasFilter($tag));
		$this->assertEquals($priority + 1, $hook->hasFilter($tag, $callback_two));
    }

	public function test_removeFilters_with_another_at_same_priority()
	{
		$callback_one = [$this, '__return_null'];
		$callback_two = [$this, '__return_false'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback_one, $priority, $accepted_args);
		$hook->addFilter($tag, $callback_two, $priority, $accepted_args);

		$hook->removeFilter($tag, $callback_one, $priority);

		$this->assertCount(1, $hook->getRegistered($tag, $priority));
	}

	public function test_removeFilter_with_another_at_different_priority()
	{
		$callback_one = [$this, '__return_null'];
		$callback_two = [$this, '__return_false'];
		$hook = Hooks::getInstance();
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = rand(1, 100);

		$hook->addFilter($tag, $callback_one, $priority, $accepted_args);
		$hook->addFilter($tag, $callback_two, $priority + 1, $accepted_args);

		$hook->removeFilter($tag, $callback_one, $priority);
		$this->assertEmpty($hook->getRegistered($tag, $priority));
		$this->assertCount(1, $hook->getRegistered($tag, $priority + 1));
	}

	public function testRemoveFilter()
	{
		$hooks = Hooks::getInstance();
		$hooks->removeAllFilters('testFilter');
		$this->assertFalse($hooks->hasFilter('testFilter', 'time'));
		$hooks->addFilter('testFilter', 'time');
		$this->assertFalse($hooks->removeFilter('testFilter', 'print_r'));
		$this->assertTrue($hooks->hasFilter('testFilter', 'time') !== false);
		$this->assertTrue($hooks->removeFilter('testFilter', 'time'));
		$this->assertFalse($hooks->hasFilter('testFilter', 'time'));
    }

    public function _filter_remove_and_add1($string)
    {
        return $string . '1';
    }

    public function _filter_remove_and_add2($string)
    {
        $this->hooks->removeFilter('remove_and_add', array($this, '_filter_remove_and_add2'), 11);
        $this->hooks->addFilter('remove_and_add', array($this, '_filter_remove_and_add2'), 11, 1);

        return $string . '2';
    }

    public function _filter_remove_and_add3($string)
    {
        return $string . '3';
    }

    public function _filter_remove_and_add4($string)
    {
        return $string . '4';
    }

    public function __return_empty_string()
    {
        return '';
    }

    public function _action_remove_and_add1()
    {
        $this->action_output .= 1;
    }

    public function _action_remove_and_add2()
    {
        $this->hooks->removeFilter('remove_and_add_action', array($this, '_action_remove_and_add2'), 11);
        $this->hooks->addFilter('remove_and_add_action', array($this, '_action_remove_and_add2'), 11, 0);

        $this->action_output .= '2';
    }

    public function _action_remove_and_add3()
    {
        $this->action_output .= '3';
    }

    public function _action_remove_and_add4()
    {
        $this->action_output .= '4';
    }

	public function __return_null()
	{
		return null;
	}

	public function __return_false()
	{
		return false;
	}
}
