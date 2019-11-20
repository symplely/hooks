<?php

namespace Async\Hook\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class HooksTest
 */
class HooksActionTest extends TestCase
{
    protected $hooks;
    protected $events;
    protected $action_output;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->hooks = \Hooks_instance();
    }

    public function testAction()
    {
        $done = false;
        $this->hooks->addAction('bar', function () use (&$done) {
            $done = true;
        });
        $this->hooks->doAction('bar');
        $this->assertTrue($done);
    }

	public function testAdd_action()
	{
		$done = false;

		$this->hooks->addAction('foo', function () use (&$done) {
			$done = !$done;
		});

		$this->hooks->doAction('foo');
		$this->assertTrue($done);
	}

	public function testDo_action()
	{
		$this->hooks->addAction('foo', function () {
			echo "Did Action!";
		});

		$this->expectOutputRegex('/Did Action!/');
		$this->assertNull($this->hooks->doAction('foo'));
    }

	public function testHas_action()
	{
		$tag  = __FUNCTION__;
		$func = function () {};

		$this->assertFalse($this->hooks->hasAction($tag, $func));
		$this->assertFalse($this->hooks->hasAction($tag));

		$this->hooks->addAction($tag, $func);
		$this->assertEquals(10, $this->hooks->hasAction($tag, $func));
		$this->assertTrue($this->hooks->hasAction($tag));
		$this->hooks->removeAction($tag, $func);

		$this->assertFalse($this->hooks->hasAction($tag, $func));
		$this->assertFalse($this->hooks->hasAction($tag));
    }

	public function testRemove_action()
	{
		$hooks = $this->hooks;
		$hooks->removeAllActions('testAction');
		$this->assertFalse($hooks->hasAction('testAction', 'time'));
		$hooks->addAction('testAction', 'time');
		$this->assertFalse($hooks->removeAction('testAction', 'print_r'));
		$this->assertTrue($hooks->hasAction('testAction', 'time') !== false);
		$this->assertTrue($hooks->removeAction('testAction', 'time'));
		$this->assertFalse($hooks->hasAction('testAction', 'time'));
    }

	public function testDid_action()
	{
		$tag1 = 'action1';
		$tag2 = 'action2';

		// do action tag1 but not tag2
		$this->hooks->doAction($tag1);
		$this->assertEquals(1, \did_action($tag1));
		$this->assertEquals(0, \did_action($tag2));

		// do action tag2 a random number of times
		$count = rand(0, 10);
		for ($i = 0; $i < $count; $i++) {
			$this->hooks->doAction($tag2);
		}

		// tag1's count hasn't changed, tag2 should be correct
		$this->assertEquals(1, \did_action($tag1));
		$this->assertEquals($count, \did_action($tag2));
	}

    public function test_doAction_with_no_accepted_args()
	{
		$callback = array($this, '_action_callback');
		$hook = $this->hooks;
		$tag = __FUNCTION__;
		$priority = rand(1, 100);
		$accepted_args = 0;
		$arg = __FUNCTION__ . '_arg';

		$hook->addFilter($tag, $callback, $priority, $accepted_args);
		$hook->doAction($arg);

		$this->assertEmpty($this->events[0]['args']);
	}

	public function _action_callback()
	{
		$args = func_get_args();
		$this->events[] = array(
			'action' => __FUNCTION__,
			'args'   => $args,
		);
	}
}
