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
 * Register Object Test
 */
class RegisterObjectTest extends TestCase {

	/**
	 * @dataProvider registerClassDataProvider
	 *
	 * @param string  $include       The include file to load.
	 * @param boolean $includeStatic The static boolean to test with.
	 *
	 * @return void
	 */
	public function testPassthru(string $include, bool $includeStatic): void {
		$types = ['object'];
		if ($includeStatic) {
			$types[] = 'static';
		}

		$test = $this->loadTestData($include, $types);

		$this->hookExpectations($test['filters'], $test['actions']);

		HookManager::registerObject($test['target'], $includeStatic);
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function registerClassDataProvider(): Generator {
		$includeOptions = [
			'test-object-examplehooks.php',
			'test-object-invokablehooks.php'
		];

		$staticOptions = [
			true,
			false
		];

		foreach ($includeOptions as $includeOption) {
			foreach ($staticOptions as $staticOption) {
				yield [
					$includeOption,
					$staticOption
				];
			}
		}
	}
}
