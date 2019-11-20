<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the example of using events with EventEmitter.
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * USAGE
 * ---------------------------------------------------------------------------------------------------------------------
 * To run this example in CLI from project root use following syntax
 *
 * $> php ./example/events_quickstart.php
 *
 * ---------------------------------------------------------------------------------------------------------------------
 */
require_once 'vendor/autoload.php';

use Async\Hook\EventEmitter;

$emitter = new EventEmitter();

$emitter->on('script.start', function($user, $time) {
    echo "User '$user' has started this script at $time.\n";
}, 10, 2);

$emitter->emit('script.start', get_current_user(), date('H:i:s Y-m-d'));
