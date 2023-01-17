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

class ExampleHooks {

	#[Filter('filter-object-default-2')]
	#[Filter('filter-object-5-2', 5)]
	#[Action('action-object-5-2', 5)]
	public function callbackTwoParams($one, $two): mixed {
		// ...
	}

	#[Action('action-object-default-3')]
	public function callbackThreeParams($one, $two, $three): mixed {
		// ...
	}

	#[Filter('filter-object-default-4')]
	public function callbackFourParams($one, $two, $three, $four): mixed {
		// ...
	}

	#[Action('action-static-8-2', 8)]
	#[Action('action-static-15-2', 15)]
	public static function callbackTwoParamsStatic($one, $two): mixed {
		// ...
	}

	#[Filter('filter-static-1-3', 1)]
	#[Filter('filter-static-default-3')]
	public static function callbackThreeParamsStatic($one, $two, $three): mixed {
		// ...
	}

	#[Filter('filter-static-1-4', 1)]
	#[Filter('filter-static-12-4', 12)]
	public static function callbackFourParamsStatic($one, $two, $three, $four): mixed {
		// ...
	}
}
