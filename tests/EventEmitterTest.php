<?php

namespace Async\Hook\Tests;

use Async\Hook\EventEmitter;
use PHPUnit\Framework\TestCase;

class EventEmitterTest extends TestCase
{
    /**
     * @var EventEmitter
     */
    protected $emitter;

    public function setUp(): void
    {
        $this->emitter = new EventEmitter();
    }

    public function tearDown(): void
    {
        $this->emitter->cancel('foo');
        $this->emitter = null;
    }

    public function testEventPriority()
    {
        $result = null;

        $this->emitter->on('init', function () use (&$result) {
            $result = 'early';
        }, 11);

        $this->emitter->on('init', function () use (&$result) {
            $result = 'late';
        }, 10);

        $this->emitter->emit('init');

        $this->assertEquals('early', $result);
    }

    public function testDelay()
    {
        $that = $this;
        // will start to work on 3rd time it receives this particular event
        $this->emitter->delay('counter', 3, function ($number) use ($that) {
            $that->assertGreaterThanOrEqual(3, $number);
        });

        $counter = 0;
        $counterMax = 5;

        while ($counter < $counterMax) {
            $this->emitter->emit('counter', ++$counter);
        }

        $this->emitter->off('counter');
    }

    public function testEmitWithoutArguments()
    {
        $listenerCalled = false;
        $this->emitter->on('foo', function () use (&$listenerCalled) {
            $listenerCalled = true;
        });
        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo');
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithOneArgument()
    {
        $test = $this;
        $listenerCalled = false;
        $this->emitter->on('foo', function ($value) use (&$listenerCalled, $test) {
            $listenerCalled = true;
            $test->assertSame('bar', $value);
        });
        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo', 'bar');
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithTwoArguments()
    {
        $test = $this;
        $listenerCalled = false;
        $this->emitter->on('foo', function ($arg1, $arg2) use (&$listenerCalled, $test) {
            $listenerCalled = true;
            $test->assertSame('bar', $arg1);
            $test->assertSame('baz', $arg2);
        }, 10, 2);
        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo', 'bar', 'baz');
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithTwoListeners()
    {
        $listenersCalled = 0;
        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });
        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(2, $listenersCalled);
    }

    public function testRemoveListenerMatching()
    {
        $listenersCalled = 0;
        $listener = function () use (&$listenersCalled) {
            $listenersCalled++;
        };
        $this->emitter->on('foo', $listener);
        $this->emitter->off('foo', $listener);
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(0, $listenersCalled);
    }

    public function testRemoveListenerNotMatching()
    {
        $listenersCalled = 0;
        $listener = function () use (&$listenersCalled) {
            $listenersCalled++;
        };
        $this->emitter->on('foo', $listener);
        $this->emitter->off('bar', $listener);
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenersCalled);
    }

    public function testRemoveAllListenersMatching()
    {
        $listenersCalled = 0;
        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });
        $this->emitter->cancel('foo');
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(0, $listenersCalled);
    }

    public function testRemoveAllListenersNotMatching()
    {
        $listenersCalled = 0;
        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });
        $this->emitter->cancel('bar');
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenersCalled);
    }

    public function testRemoveAllListenersWithoutArguments()
    {
        $listenersCalled = 0;
        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->emitter->on('bar', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->emitter->cancel();
        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->emitter->emit('bar');
        $this->assertSame(0, $listenersCalled);
    }

    public function testCallableClosure()
    {
        $calledWith = null;
        $this->emitter->on('foo', function ($data) use (&$calledWith) {
            $calledWith = $data;
        });
        $this->emitter->emit('foo', 'bar');
        $this->assertSame('bar', $calledWith);
    }

    public function testEmitIsVariadicLikeDoAction()
    {
        $result = null;

        $this->emitter->on('foome', function () use (&$result) {
            $result = func_get_args();
        }, 10, 2);

        $this->emitter->emit('foome', 'grace jones', 'andre the giant');

        $this->assertSame(array('grace jones', 'andre the giant'), $result);
    }

    public function testFilters()
    {
        $this->emitter->add('the_content', function ($content, $append) {
            return $content . ' ' . $append;
        }, 10, 2);

        $this->emitter->add('the_content', function ($content) {
            return $content . ' yolo';
        });

        $content = $this->emitter->trigger('the_content', 'ham', 'sandwich');

        $this->assertEquals('ham sandwich yolo', $content);
    }

    public function testMultipleEmits()
    {
        $test = null;

        $this->emitter->on('foo', function ($arg) use (&$test) {
            $test = $arg;
        });

        $this->emitter->on('foo', function ($arg) use (&$test) {
            $test = $arg;
        });

        $this->emitter->emit('foo', 'bar');

        $this->assertNotEmpty($test);
    }

    public function testCallingTriggerWithNoFiltersAddedReturnsValue()
    {
        $toFilter = 'foobar';

        $filtered = $this->emitter->trigger('fooBar', $toFilter);

        $this->assertSame($toFilter, $filtered);
    }

    public function testHasEventListenerReturnsFalseWhenNoListenerAdded()
    {
        $hasListener = $this->emitter->hasEvent('foo', true);

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenListenerAdded()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEvent('foo');

        $this->assertTrue($hasListener);
    }

    public function testHasFilterReturnsFalseWhenNoFilterAdded()
    {
        $hasFilter = $this->emitter->hasName('fooo');

        $this->assertFalse($hasFilter);
    }

    public function testHasFilterReturnsTrueWhenFilterAdded()
    {
        $this->emitter->add('foo', 'strtoupper');

        $hasFilter = $this->emitter->hasName('foo');

        $this->assertTrue($hasFilter);
    }

    public function testHasEventListenerReturnsFalseWhenStringFunctionToCheckDoesNotMatch()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEvent('foo', 'some_other_func');

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenStringFunctionToCheckMatches()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEvent('foo', 'phpinfo');

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenClosuresSame()
    {
        $saySomething = function () {
            echo 'something';
        };

        $this->emitter->on('foo', $saySomething);

        $hasListener = $this->emitter->hasEvent('foo', $saySomething);

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsFalseWhenComparingDifferentClosures()
    {
        $this->emitter->on('foo', function () {
            echo 'foo';
        });

        $hasListener = $this->emitter->hasEvent('foo', function () {
            echo 'foo';
        });

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenPassedSameArrayCallable()
    {
        $this->emitter->on('foo', array($this, 'listener1'));

        $hasListener = $this->emitter->hasEvent('foo', array($this, 'listener1'));

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsFalseWhenPassedDifferentArrayCallable()
    {
        $this->emitter->on('foo', array($this, 'listener1'));

        $hasListener = $this->emitter->hasEvent('foo', array($this, 'listener2'));

        $this->assertFalse($hasListener);
    }

    public function testWhenTriggerExistsReturnsResultOfCallingThatFunction()
    {
        $filtered = $this->emitter->trigger('some_filter', 'jimjam');

        $this->assertSame('jimjam', $filtered);
    }

    public function testCorrectArgsPassedToFunctionWhenPresent()
    {
        $filtered = $this->emitter->trigger('foo', ['foo', 'bar']);

        $this->assertSame(array('foo', 'bar'), $filtered);
    }

    public function testOffRemovesAllListeners()
    {
        $function = function () { };
        $this->emitter->on('foo', $function);

        $this->emitter->off('foo', $function);

        $this->assertFalse($this->emitter->hasEvent('foo', $function));
    }

    public function testOnce()
    {
        $listenerCalled = 0;
        $this->emitter->once('foo', function () use (&$listenerCalled) {
            $listenerCalled++;
        });

        $this->assertSame(0, $listenerCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenerCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenerCalled);
    }

    public function testOnceWithArguments()
    {
        $capturedArgs = [];
        $this->emitter->once('foo', function ($a, $b) use (&$capturedArgs) {
            $capturedArgs = array($a, $b);
        }, 10, 2);

        $this->emitter->emit('foo', 'a', 'b');
        $this->assertSame(array('a', 'b'), $capturedArgs);
    }

    public function testOnceNestedCallRegression()
    {
        $first = 0;
        $second = 0;
        $this->emitter->once('event', function () use (&$first, &$second) {
            $first++;
            $this->emitter->once('event', function () use (&$second) {
                $second++;
            });
            $this->emitter->emit('event');
        });

        $this->emitter->emit('event');
        self::assertSame(1, $first);
        self::assertSame(1, $second);
    }

    public function testListeners()
    {
        $onA = function () { };
        $onB = function () { };
        $onC = function () { };
        $onceA = function () { };
        $onceB = function () { };
        $onceC = function () { };
        $this->assertCount(0, $this->emitter->listeners('event'));
        $this->emitter->on('event', $onA);
        $this->assertCount(1, $this->emitter->listeners('event'));
        $this->assertSame([$onA], $this->emitter->listeners('event'));
        $this->emitter->on('event', $onceA);
        $this->assertCount(2, $this->emitter->listeners('event'));
        $this->assertSame([$onA, $onceA], $this->emitter->listeners('event'));
        $this->emitter->on('event', $onceB);
        $this->assertCount(3, $this->emitter->listeners('event'));
        $this->assertSame([$onA, $onceA, $onceB], $this->emitter->listeners('event'));
        $this->emitter->on('event', $onB);
        $this->assertCount(4, $this->emitter->listeners('event'));
        $this->emitter->off('event', $onceA);
        $this->assertCount(3, $this->emitter->listeners('event'));
        $this->emitter->once('event', $onceC);
        $this->assertCount(4, $this->emitter->listeners('event'));
        $this->emitter->on('event', $onC);
        $this->assertCount(5, $this->emitter->listeners('event'));
        $this->emitter->once('event', $onceA);
        $this->assertCount(6, $this->emitter->listeners('event'));
        $this->emitter->clear('event', $onB);
        $this->assertCount(5, $this->emitter->listeners('event'));
        $this->emitter->emit('event');
        $this->assertCount(3, $this->emitter->listeners('event'));
    }

    public function testEventNameMustBeStringOn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('event name must not be null');
        $this->emitter->on(null, function () { });
    }

    public function testEventNameMustBeStringOnce()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('event name must not be null');
        $this->emitter->once(null, function () { });
    }

    public function listener1()
    {
        // do a thing
    }

    public function listener2()
    {
        // do another thing
    }
}
