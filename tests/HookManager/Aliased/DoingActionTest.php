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
 * Doing Action Test
 */
class DoingActionTest extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('doing_action');

		$mock
			->expects($this->once())
			->method('doing_action')
			->with(
				$this->identicalTo('action-name')
			)
			->willReturn(true)
		;

		HookManager::doingAction('action-name');
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::doingAction('action-name');
	}
}
