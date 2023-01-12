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
 * Do Actions Ref Array Test
 */
class DoActionsRefArrayTest extends TestCase {
	use Type\Action;

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$mock = $this->mockGlobalFunction('do_action_ref_array');

		$mock
			->expects($this->once())
			->method('do_action_ref_array')
			->with(
				$this->identicalTo('test-action'),
				$this->identicalTo(['test-value'])
			)
		;

		HookManager::doActionRefArray('test-action', ['test-value']);
	}

	/**
	 * @return void
	 */
	public function testUninitialized(): void {
		$this->expectException(UninitializedError::class);
		HookManager::doActionRefArray('test-action', ['test-value']);
	}
}
