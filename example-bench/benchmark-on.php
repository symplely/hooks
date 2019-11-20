<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the benchmark of Dazzle Event package.
 * Benchmark has been run according to the library v0.5
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * USAGE
 * ---------------------------------------------------------------------------------------------------------------------
 * To run this example in CLI from project root use following syntax. Make sure you install all externally referenced
 * libraries.
 *
 * $> php ./example-bench/benchmark-on.php
 *
 * ---------------------------------------------------------------------------------------------------------------------
 */
require_once 'vendor/autoload.php';

use Async\Hook\EventEmitter;

$emitter = new EventEmitter();
$numEmitters = 1e2;
$numEvents = 1e5;
$numAll = $numEmitters * $numEvents;

for ($i=0; $i<$numEmitters; $i++)
{
    $emitter->on('event', function ($a, $b, $c) {}, 10, 3);
}

$time1 = microtime(true);

for ($i=0; $i<$numEvents; $i++)
{
    $emitter->emit('event', 'A', 'B', 'C');
}

$time2 = microtime(true);

printf("%s\n", str_repeat('-', 64));
printf("%-30s %8s ms -> %6s   events/ms\n", 'Time needed:', $timeAll = (round(($time2-$time1)*1e6)/1e3), round($numAll/$timeAll));
printf("   > %-25s %8s ms -> %6s   events/ms\n", 'Emitting events', $timeAll = (round(($time2-$time1)*1e6)/1e3), round($numAll/$timeAll));
printf("%s\n", str_repeat('-', 64));
printf("%-30s %8s MB\n", 'Memory:', round(memory_get_usage(true) / 1024 / 1024, 3));
printf("   > %-25s %8s MB -> %6s emitters/MB\n", 'allocated', $memAll = (round(memory_get_usage() / 1024 / 1024, 3)), round($numEmitters/$memAll));
printf("%-30s %8s MB\n", 'Peak Memory:', round(memory_get_peak_usage(true) / 1024 / 1024, 3));
printf("   > %-25s %8s MB -> %6s emitters/MB\n", 'allocated', $memAll = (round(memory_get_peak_usage() / 1024 / 1024, 3)), round($numEmitters/$memAll));
printf("%s\n", str_repeat('-', 64));
