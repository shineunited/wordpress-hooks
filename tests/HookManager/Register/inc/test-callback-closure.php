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

$closure =
#[Filter('filter-closure-5-1', 5)]
#[Filter('filter-closure-default-1')]
#[Action('action-closure-6-1', 6)]
function ($one): mixed {
	// ...
};

return [
	'target'   => $closure,
	'callback' => [
		'filters' => [
			['filter-closure-5-1', $closure, 5, 1],
			['filter-closure-default-1', $closure, HookManager::DEFAULT_PRIORITY, 1]
		],
		'actions' => [
			['action-closure-6-1', $closure, 6, 1]
		]
	],
	'object'   => [],
	'class'    => [],
	'static'   => []
];
