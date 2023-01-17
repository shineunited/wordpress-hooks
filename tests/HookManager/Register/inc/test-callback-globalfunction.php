<?php

/**
 * This file is part of WordPress Hooks.
 *
 * (c) Shine United LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use ShineUnited\WordPress\Hooks\Action;
use ShineUnited\WordPress\Hooks\Filter;
use ShineUnited\WordPress\Hooks\HookManager;

#[Filter('filter-string-default-2')]
#[Filter('filter-string-5-2', 5)]
function global_callback($one, $two): mixed {
	// ...
}

return [
	'target'   => 'global_callback',
	'callback' => [
		'filters' => [
			['filter-string-default-2', 'global_callback', HookManager::DEFAULT_PRIORITY, 2],
			['filter-string-5-2', 'global_callback', 5, 2]
		]
	],
	'object'   => [],
	'class'    => [],
	'static'   => []
];
