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

namespace ShineUnited\WordPress\Hooks\Tests\HookManager;

use ShineUnited\WordPress\Hooks\HookManager;
use ShineUnited\WordPress\Hooks\UninitializedError;

/**
 * Apply Filters Test
 */
class ApplyFiltersTest extends TestCase {
	use Type\Filter;

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('apply_filters');

		$mock
			->expects($this->once())
			->method('apply_filters')
			->with(
				$this->identicalTo('test-filter'),
				$this->identicalTo('test-value')
			)
		;

		HookManager::applyFilters('test-filter', 'test-value');
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::applyFilters('test-filter', 'test-value');
	}
}
