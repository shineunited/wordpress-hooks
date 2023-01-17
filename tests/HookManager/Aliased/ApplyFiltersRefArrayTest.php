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

namespace ShineUnited\WordPress\Hooks\Tests\HookManager\Aliased;

use ShineUnited\WordPress\Hooks\HookManager;
use ShineUnited\WordPress\Hooks\UninitializedError;

/**
 * Apply Filters Ref Array Test
 */
class ApplyFiltersRefArrayTest extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('apply_filters_ref_array');

		$mock
			->expects($this->once())
			->method('apply_filters_ref_array')
			->with(
				$this->identicalTo('test-filter'),
				$this->identicalTo(['test-value'])
			)
		;

		HookManager::applyFiltersRefArray('test-filter', ['test-value']);
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::applyFiltersRefArray('test-filter', ['test-value']);
	}
}
