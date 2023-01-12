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
 * Did Action Test
 */
class DidActionTest extends TestCase {
	use Type\Action;

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('did_action');

		$mock
			->expects($this->once())
			->method('did_action')
			->with(
				$this->identicalTo('action-name')
			)
			->willReturn(0)
		;

		HookManager::didAction('action-name');
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::didAction('action-name');
	}
}
