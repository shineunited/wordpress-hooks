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

use ShineUnited\WordPress\Hooks\HookManager;

require('class-examplehooks.php');

$class = ExampleHooks::class;

return [
	'target'   => $class,
	'callback' => [],
	'object'   => [],
	'class'    => [
		'filters' => [
			['filter-static-1-3', [$class, 'callbackThreeParamsStatic'], 1, 3],
			['filter-static-default-3', [$class, 'callbackThreeParamsStatic'], HookManager::DEFAULT_PRIORITY, 3],
			['filter-static-1-4', [$class, 'callbackFourParamsStatic'], 1, 4],
			['filter-static-12-4', [$class, 'callbackFourParamsStatic'], 12, 4],
		],
		'actions' => [
			['action-static-8-2', [$class, 'callbackTwoParamsStatic'], 8, 2],
			['action-static-15-2', [$class, 'callbackTwoParamsStatic'], 15, 2],
		]
	],
	'static'   => []
];
