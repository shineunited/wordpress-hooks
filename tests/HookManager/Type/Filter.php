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

namespace ShineUnited\WordPress\Hooks\Tests\HookManager\Type;

/**
 * Filter Type Trait
 */
trait Filter {

	/**
	 * {@inheritdoc}
	 */
	protected function getHookType(bool $initialCaps = false): string {
		if ($initialCaps) {
			return 'Filter';
		} else {
			return 'filter';
		}
	}
}
