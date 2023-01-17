# shineunited/wordpress-hooks

[![License](https://img.shields.io/packagist/l/shineunited/wordpress-hooks)](https://github.com/shineunited/wordpress-hooks/blob/main/LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/shineunited/wordpress-hooks?label=latest)](https://packagist.org/packages/shineunited/wordpress-hooks/)
[![PHP Version](https://img.shields.io/packagist/dependency-v/shineunited/wordpress-hooks/php?label=php)](https://www.php.net/releases/index.php)
[![Main Status](https://img.shields.io/github/actions/workflow/status/shineunited/wordpress-hooks/build.yml?branch=main&label=main)](https://github.com/shineunited/wordpress-hooks/actions/workflows/build.yml?query=branch%3Amain)
[![Release Status](https://img.shields.io/github/actions/workflow/status/shineunited/wordpress-hooks/build.yml?branch=release&label=release)](https://github.com/shineunited/wordpress-hooks/actions/workflows/build.yml?query=branch%3Arelease)
[![Develop Status](https://img.shields.io/github/actions/workflow/status/shineunited/wordpress-hooks/build.yml?branch=develop&label=develop)](https://github.com/shineunited/wordpress-hooks/actions/workflows/build.yml?query=branch%3Adevelop)


## Description

A tool for managing WordPress hooks. Allows registration of hooks prior to WordPress initialization and provides a framework for defining hooks using PHP attributes.


## Installation

to add wordpress-hooks, the recommended method is via composer.
```sh
$ composer require shineunited/wordpress-hooks
```


### HookManager

The HookManager class provides static methods for managing WordPress hooks. While most of the functions are simply aliased to the built-in WordPress functions, some of them all management of hooks prior to initialization.

```php
use ShineUnited\WordPress\Hooks\HookManager;

function callback_function($param1, $param2) {
	// code
}

// this can be used prior to initialization
HookManager::addFilter('filter-name', 'callback-function', 10, 2);
```

### Attribute Hooks

Hooks can also be defined by using the Hook attributes and HookManager::register();
```php
use ShineUnited\WordPress\Hooks\Filter;
use ShineUnited\WordPress\Hooks\HookManager;

class MyObjectHooks {

	#[Filter('lowercase')]
	public function lowercase(string $value): string {
		return strtolower($value);
	}

	#[Filter('uppercase')]
	public function uppercase(string $value): string {
		return strtoupper($value);
	}
}

$hooks = new MyObjectHooks();
HookManager::register($hooks);
// WordPress Equivalent
//   add_filter('lowercase', [$hooks, 'lowercase'], 10, 1);
//   add_filter('uppercase', [$hooks, 'uppercase'], 10, 1);
```

Multiple hooks can be applied to a single callback.
```php
use ShineUnited\WordPress\Hooks\Action;
use ShineUnited\WordPress\Hooks\Filter;
use ShineUnited\WordPress\Hooks\HookManager;

$closure =
#[Filter('example-filter', 12)]
#[Action('example-action')]
function($value) {
	// code
};

HookManager::register($closure);
// WordPress Equivalent:
//   add_filter('example-filter', $closure, 12, 1);
//   add_filter('example-action', $closure, 10, 1);
```

### Function Reference

For more details and examples please see our [documentation](https://shineunited.github.io/wordpress-hooks).
