# hooks

[![Build Status](https://travis-ci.org/symplely/hooks.svg?branch=master)](https://travis-ci.org/symplely/hooks)[![codecov](https://codecov.io/gh/symplely/hooks/branch/master/graph/badge.svg)](https://codecov.io/gh/symplely/hooks)[![Maintainability](https://api.codeclimate.com/v1/badges/3fc929777fd5c6403abf/maintainability)](https://codeclimate.com/github/symplely/hooks/maintainability)

This library allows you to easily add some event-based architecture into your application thru registering call-backs that would be executed by triggering a **hook**, **event**, or **listener** on a string identifier/tag, which we call here __$hook_spot__, which would normally be expressing desired action with prefixes like "before" or "after" if necessary.

----------

How to Use?

Simple, Include the class file in your application bootstrap (setup/load/configuration or whatever you call it) and start hooking your filter and action hooks using the global `Hooks` functions. Ex:

```PHP
add_action('header_action', 'echo_this_in_header');

function echo_this_in_header() {
   echo 'this came from a hooked function';
}
```

then all that is left for you is to call the hooked function when you want anywhere in your application, EX:

```PHP
echo '<div id="extra_header">';
  do_action('header_action');
echo '</div>';
```

and you output will be:

```html
<div id="extra_header">this came from a hooked function</div>
```

## Installation

To install this library make sure you have [composer](https://getcomposer.org/) installed, then run following command:

```shell
composer require symplely/hooks
```

## Usage

This library is inspired by the EventEmitter API found in [node.js](https://github.com/nodejs/node/blob/master/lib/events.js), and [Événement](https://github.com/igorw/evenement).

So it comes with a familiar simple event emitter interface that delegates to the `add_filter`, `apply_filters`, `add_action` and `do_action` methods of the `Hooks` API class.

### Creating an Emitter

```php
<?php
require 'vendor/autoload.php';

use Async\Hook\EventEmitter;

$emitter = new EventEmitter();
```

### Adding Listeners

```php
<?php
$emitter->on('user.created', function (User $user) use ($logger) {
    $logger->log(sprintf("User '%s' was created.", $user->getLogin()));
});
```

### Emitting Events

```php
<?php
$emitter->emit('user.created', $user);
```

## Methods

### on()

Delegate to Hooks' [add_action]function.

### once()

Delegate to Hooks' [add_action]function, then [remove_action]function.

### off()

Delegate to Hooks' [remove_action]function.

### emit()

Delegate to Hooks' [do_action]function.

### add()

Delegate to Hooks' [add_filter]function.

### clear()

Delegate to Hooks' [remove_filter]function.

### cancel()

Delegate to Hooks' [remove_all_filters]function.

### trigger()

Delegate to Hooks' [apply_filters]function.

**ACTIONS:**

```php
/**
 * Hooks a function on to a specific action hook.
 */
add_action($hook_spot, $function_to_add, $priority, $accepted_args);

/**
 * Execute functions hooked on a specific action hook.
 * Will return null if $hook_spot does not exist
 */
do_action($hook_spot, ...$arg);

/**
 * Removes a function from a specified action hook.
 * Will return true if the function is removed
 */
remove_action($hook_spot, $function_to_remove, $priority);

/**
 * Check if any action has been registered for a hook.
 * Will return boolean if anything registered, or the priority.
 */
has_action($hook_spot, $function_to_check);

/**
 * Retrieve the number of times an action is fired.
 */
did_action($hook_spot);
```

**FILTERS:**

```php
/**
 * Hooks a function or method to a specific filter hook.
 * Will return boolean true
 */
add_filter($hook_spot, $function_to_add, $priority, $accepted_args);

/**
 * Removes a function from a specified filter hook.
 * Will return boolean Whether the function existed before it was removed
 */
remove_filter($hook_spot, $function_to_remove, $priority, $accepted_args);

/**
 * Check if any filter has been registered for a hook.
 * Will return mixed
 */
has_filter($hook_spot, $function_to_check);

/**
 * Call the functions added to a filter hook.
 * Will return the filtered value after all hooked functions are applied to it.
 */
apply_filters($hook_spot, $value, ...$arg);
```

There are a few more methods but these are the main Ones you'll use.
