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

namespace ShineUnited\WordPress\Hooks\Tests\HookManager\Register;

use ShineUnited\WordPress\Hooks\HookManager;
use Generator;

/**
 * Register Callback Test
 */
class RegisterCallbackTest extends TestCase {


	/**
	 * @dataProvider registerCallbackDataProvider
	 *
	 * @param string $include The include file to load.
	 *
	 * @return void
	 */
	public function testPassthru(string $include): void {
		$test = $this->loadTestData($include, 'callback');

		$this->hookExpectations($test['filters'], $test['actions']);

		HookManager::registerCallback($test['target']);
	}

	/**
	 * @return Generator
	 */
	public function registerCallbackDataProvider(): Generator {
		$includeOptions = [
			'test-callback-closure.php',
			'test-callback-globalfunction.php',
			'test-object-invokablehooks.php'
		];

		foreach ($includeOptions as $includeOption) {
			yield [
				$includeOption
			];
		}
	}
}
