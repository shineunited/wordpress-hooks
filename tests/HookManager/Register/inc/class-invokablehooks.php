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

require('class-examplehooks.php');

#[Filter('filter-callback-5-5', 5)]
#[Action('action-callback-5-5', 5)]
class InvokableHooks extends ExampleHooks {

	public function __invoke($one, $two, $three, $four, $five): mixed {
		// ...
	}
}
