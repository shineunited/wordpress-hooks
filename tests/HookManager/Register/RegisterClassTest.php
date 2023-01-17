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
 * Register Class Test
 */
class RegisterClassTest extends TestCase {

	/**
	 * @dataProvider registerClassDataProvider
	 *
	 * @param string $include The include file to load.
	 *
	 * @return void
	 */
	public function testPassthru(string $include): void {
		$test = $this->loadTestData($include, 'class');

		$this->hookExpectations($test['filters'], $test['actions']);

		HookManager::registerClass($test['target']);
	}

	/**
	 * @return Generator
	 */
	public function registerClassDataProvider(): Generator {
		$includeOptions = [
			'test-class-examplehooks.php'
		];

		foreach ($includeOptions as $includeOption) {
			yield [
				$includeOption
			];
		}
	}
}
