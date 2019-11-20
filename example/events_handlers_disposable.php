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
 * $> php ./example/events_handlers_disposable.php
 *
 * ---------------------------------------------------------------------------------------------------------------------
 */
require_once 'vendor/autoload.php';

use Async\Hook\EventEmitter;

$emitter = new EventEmitter();

// this handler will be disposed after first fire
$emitter->once('event.number', function($number) {
    echo "Event has been fired with \$number = $number.\n";
});

$counter = 0;
$emitter->emit('event.number', ++$counter);
$emitter->emit('event.number', ++$counter);
$emitter->emit('event.number', ++$counter);
