<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the example of using disposable events with EventEmitter.
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * USAGE
 * ---------------------------------------------------------------------------------------------------------------------
 * To run this example in CLI from project root use following syntax
 *
 * $> php ./example/events_handlers_delayed.php
 *
 * ---------------------------------------------------------------------------------------------------------------------
 */
require_once 'vendor/autoload.php';

use Async\Hook\EventEmitter;

$emitter = new EventEmitter();

// this handler will start to work on 3rd time it receives this particular event
$emitter->delay('event.number', 3, function($number) {
    echo "Event has been fired with \$number = $number.\n";
});

$counter = 0;
$counterMax = 5;

while ($counter < $counterMax) {
    $emitter->emit('event.number', ++$counter);
}
