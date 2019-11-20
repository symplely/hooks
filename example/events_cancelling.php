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

$emitter->on('event', $callback1 = function() {
    echo "1st listener reacted to the event.\n";
});
$emitter->on('event', $callback2 = function() {
    echo "2nd listener reacted to the event.\n";
});
$emitter->on('event', $callback3 = function() {
    echo "3rd listener reacted to the event.\n";
});

echo "------\n";
$emitter->emit('event');

echo "-------\n";
$emitter->off('event', $callback3); // alternative method to cancel listener
$emitter->emit('event');

echo "-------\n";
$emitter->cancel(); // preferred method to cancel all listeners
$emitter->emit('event');