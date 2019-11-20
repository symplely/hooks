<?php

use PHPUnit\Framework\TestCase;

/**
 * Class HooksTest
 */
class CoreTest extends TestCase
{
    /**
     * @var string
     */
    protected $testString_1 = 'lalllöäü123';

    /**
     * @var string
     */
    protected $testString_2 = 'lalll_§$§$&&//"?23';

    /**
     * @param $input
     *
     * @return string
     */
    public function hookTestString_1($input)
    {
        return $input . $this->testString_1;
    }

    /**
     * @param $input
     *
     * @return string
     */
    public function hookTestString_2($input)
    {
        return $input . $this->testString_2;
    }

    public function testHooks()
    {
        \hooks_reset();

        \add_filter('test', [
            $this,
            'hookTestString_1'
        ]);

        \add_filter('test', [
            $this,
            'hookTestString_2'
        ]);

        $lall = \apply_filters('test', '');
        $this->assertSame($lall, $this->testString_1 . $this->testString_2);
    }

    /**
     * WARNING: you have to run "$this->testHooks()" first
     */
    public function testHooksInstance()
    {
        $hooks = \hooks_instance();
        $this->assertInstanceOf(\Async\Hook\HooksInterface::class, $hooks);
        $lall = \apply_filters('test', '');
        $this->assertSame($lall, $this->testString_1 . $this->testString_2);
        \remove_all_filters('test');
    }

    public function testHasFunctions()
    {
        $this->assertSame(true, \remove_all_filters('testFilter'));
        $this->assertSame(true, \remove_all_actions('testAction'));
        $this->assertFalse(\has_filter(''));
        $this->assertFalse(\has_filter(' '));
        $this->assertFalse(\has_filter('testFilter'));
        $this->assertFalse(\has_filter('testFilter', 'time'));
        $this->assertFalse(\has_action('testAction', 'time'));
        $this->assertSame(true, \add_filter('testFilter', 'time'));
        $this->assertSame(true, \add_action('testAction', 'time'));
        $this->assertTrue(\has_filter('testFilter', 'time') !== false);
        $this->assertTrue(\has_action('testAction', 'time') !== false);
        $this->assertFalse(\has_filter('testFilter', 'print_r'));
        $this->assertFalse(\has_action('testAction', 'print_r'));
        $this->assertTrue(\has_filter('testFilter'));
        $this->assertTrue(\has_action('testAction'));
        $this->assertFalse(\has_filter('notExistingFilter'));
        $this->assertFalse(\has_action('notExistingAction'));
    }

    public function testRemoveOneFunctions()
    {
        \remove_all_filters('testFilter');
        \remove_all_actions('testAction');
        $this->assertFalse(\has_filter('testFilter', 'time'));
        $this->assertFalse(\has_action('testAction', 'time'));
        \add_filter('testFilter', 'time');
        \add_action('testAction', 'time');
        $this->assertFalse(\remove_filter('testFilter', 'print_r'));
        $this->assertFalse(\remove_action('testAction', 'print_r'));
        $this->assertTrue(\has_filter('testFilter', 'time') !== false);
        $this->assertTrue(\has_action('testAction', 'time') !== false);
        $this->assertTrue(\remove_filter('testFilter', 'time'));
        $this->assertTrue(\remove_action('testAction', 'time'));
        $this->assertFalse(\has_filter('testFilter', 'time'));
        $this->assertFalse(\has_action('testAction', 'time'));
    }

    public function testRemoveAllFunctions()
    {
        $this->assertSame(true, \remove_all_filters('testFilter'));
        $this->assertSame(true, \remove_all_actions('testAction'));
        $this->assertSame(true, \add_filter('testFilter', 'time', 10));
        $this->assertSame(true, \add_filter('testFilter', 'print_r', 10));
        $this->assertSame(true, \add_filter('testFilter', 'time', 25));
        $this->assertSame(true, \add_action('testAction', 'time', 10));
        $this->assertSame(true, \add_action('testAction', 'print_r', 10));
        $this->assertSame(true, \add_action('testAction', 'time', 25));
        $this->assertTrue(\remove_all_filters('testFilter', 10));
        $this->assertTrue(\remove_all_actions('testAction', 10));
        $this->assertTrue(\has_filter('testFilter'));
        $this->assertTrue(\has_action('testAction'));
        $this->assertSame(25, \has_filter('testFilter', 'time'));
        $this->assertSame(25, \has_action('testAction', 'time'));
        $this->assertTrue(\remove_all_filters('testFilter'));
        $this->assertTrue(\remove_all_actions('testAction'));
        $this->assertFalse(\has_filter('testFilter'));
        $this->assertFalse(\has_action('testAction'));
    }

    public function testDidAction()
    {
        $this->assertSame(true, \remove_all_actions('testActionNone'));
        $this->assertSame(null, \do_action('testActionNone'));
        $this->assertSame(1, \did_action('testActionNone'));
        $this->assertSame(null, \do_action('testActionNone'));
        $this->assertSame(2, \did_action('testActionNone'));
        $this->assertSame(true, \remove_all_actions('testActionNone'));
    }

    public function testDoActionRefArray()
    {
        $this->assertSame(true, \remove_all_actions('testRefAction'));
        $this->assertSame(null, \do_action('testRefAction'));
        $this->assertSame(null, \do_action_ref_array('NotExistingAction', array()));
        $this->assertSame(null, \do_action_ref_array('testRefAction', array('test')));
        $this->assertSame(2, \did_action('testRefAction'));
        $this->assertSame(1, \did_action('NotExistingAction'));
        $this->assertSame(true, \remove_all_actions('notExistingAction'));
    }

    public function testApplyFiltersRefArray()
    {
        $this->assertSame('Foo', \apply_filters('testRefFilter', 'Foo'));
        $this->assertSame('Foo', \apply_filters_ref_array('testRefFilter', array('Foo')));

        $mock = $this
            ->getMockBuilder('stdClass')
            ->setMethods(['applySomeFilter'])
            ->getMock();
        $mock->expects($this->exactly(2))->method('applySomeFilter')->willReturn('foo');

        $this->assertSame(true, \add_filter('testRefFilter', array($mock, 'applySomeFilter')));
        $this->assertSame('foo', \apply_filters('testRefFilter', 'Foo'));
        $this->assertSame('foo', \apply_filters_ref_array('testRefFilter', array('FooBar')));
    }

    public function testRemoveAllActions()
    {
      self::assertSame(true, \remove_all_actions('testAction'));
      self::assertSame(true, \add_action('testAction', 'time', 10));
      self::assertSame(true, \add_action('testAction', 'print_r', 10));
      self::assertSame(true, \add_action('testAction', 'time', 25));
      self::assertTrue(\remove_all_actions('testAction', 10));
      self::assertTrue(\has_action('testAction'));
      self::assertSame(25, \has_action('testAction', 'time'));
      self::assertTrue(\remove_all_actions('testAction'));
      self::assertFalse(\has_action('testAction'));
    }

  public function testRunHookFunctions()
  {
      $this->assertSame(true, \remove_all_filters('testFilter'));
      $this->assertSame(true, \remove_all_actions('testAction'));

      \do_action('testAction');

      $this->assertSame(null, \do_action('testAction'));
      $this->assertSame(null, \do_action_ref_array('testNotExistingAction', []));
      $this->assertSame('Foo', \apply_filters('testFilter', 'Foo'));
      $this->assertSame(null, \do_action_ref_array('testAction', [
          'test'
      ]));
      $this->assertSame('Foo', \apply_filters_ref_array('testFilter', [
          'Foo'
      ]));
      $mock = $this->getMockBuilder('stdClass')->setMethods([
          'doSomeAction',
          'applySomeFilter'
      ])->getMock();

      $mock->expects($this->exactly(4))->method('doSomeAction');
      $mock->expects($this->exactly(10))->method('applySomeFilter')->willReturn('foo');
      $this->assertSame(true, \add_action('testAction', [
          $mock,
          'doSomeAction'
      ]));
      $this->assertSame(true, \add_filter('testFilter', [
          $mock,
          'applySomeFilter'
      ]));
      $this->assertSame(3, \did_action('testAction'));
      $this->assertSame(null, \do_action('testAction'));
      $this->assertSame(4, \did_action('testAction'));
      $this->assertSame('foo', \apply_filters('testFilter', 'Foo'));
      $this->assertSame(true, \add_filter('all', [
          $mock,
          'applySomeFilter'
      ]));
      $this->assertSame(null, \do_action('notExistingAction'));
      $this->assertSame('Foo', \apply_filters('notExistingFilter', 'Foo')); // unmodified value
      $this->assertSame(null, \do_action('testAction', (object) [
          'foo' => 'bar'
      ]));
      $this->assertSame(null, \do_action('testAction', 'param1', 'param2', 'param3', 'param4'));
      $this->assertSame(null, \do_action_ref_array('testAction', [
          'test'
      ]));
      $this->assertSame('foo', \apply_filters('testFilter', 'Foo'));
      $this->assertSame('foo', \apply_filters_ref_array('testFilter', [
          'Foo'
      ]));
  }
}
