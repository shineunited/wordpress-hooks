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

require('class-invokablehooks.php');

$object = new InvokableHooks();

return [
	'target'   => $object,
	'callback' => [
		'filters' => [
			['filter-callback-5-5', $object, 5, 5]
		],
		'actions' => [
			['action-callback-5-5', $object, 5, 5]
		]
	],
	'object'   => [
		'filters' => [
			['filter-object-default-2', [$object, 'callbackTwoParams'], HookManager::DEFAULT_PRIORITY, 2],
			['filter-object-5-2', [$object, 'callbackTwoParams'], 5, 2],
			['filter-object-default-4', [$object, 'callbackFourParams'], HookManager::DEFAULT_PRIORITY, 4],
		],
		'actions' => [
			['action-object-5-2', [$object, 'callbackTwoParams'], 5, 2],
			['action-object-default-3', [$object, 'callbackThreeParams'], HookManager::DEFAULT_PRIORITY, 3],
		]
	],
	'class'    => [],
	'static'   => [
		'filters' => [
			['filter-static-1-3', [$object::class, 'callbackThreeParamsStatic'], 1, 3],
			['filter-static-default-3', [$object::class, 'callbackThreeParamsStatic'], HookManager::DEFAULT_PRIORITY, 3],
			['filter-static-1-4', [$object::class, 'callbackFourParamsStatic'], 1, 4],
			['filter-static-12-4', [$object::class, 'callbackFourParamsStatic'], 12, 4],
		],
		'actions' => [
			['action-static-8-2', [$object::class, 'callbackTwoParamsStatic'], 8, 2],
			['action-static-15-2', [$object::class, 'callbackTwoParamsStatic'], 15, 2],
		]
	]
];
