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
 * Current Filter Test
 */
class CurrentFilterTest extends TestCase {
	use Type\Filter;

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('current_filter');

		$mock
			->expects($this->once())
			->method('current_filter')
			->willReturn('')
		;

		HookManager::currentFilter();
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::currentFilter();
	}
}
