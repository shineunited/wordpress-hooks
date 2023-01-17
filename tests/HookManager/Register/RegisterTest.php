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
 * Register Test
 */
class RegisterTest extends TestCase {


	/**
	 * @dataProvider registerDataProvider
	 *
	 * @param string  $include The include file to load.
	 * @param integer $mode    The mode to run the test with.
	 *
	 * @return void
	 */
	public function testPassthru(string $include, int $mode): void {
		$types = [];
		if ($mode & HookManager::REGISTER_CALLBACK) {
			$types[] = 'callback';
		}

		if ($mode & HookManager::REGISTER_OBJECT) {
			$types[] = 'object';

			// static is dependant on object mode
			if ($mode & HookManager::REGISTER_STATIC) {
				$types[] = 'static';
			}
		}

		if ($mode & HookManager::REGISTER_CLASS) {
			$types[] = 'class';
		}

		$test = $this->loadTestData($include, $types);

		$this->hookExpectations($test['filters'], $test['actions']);

		HookManager::register($test['target'], $mode);
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function registerDataProvider(): Generator {
		$includeOptions = [
			'test-callback-closure.php',
			'test-callback-globalfunction.php',
			'test-class-examplehooks.php',
			'test-object-examplehooks.php',
			'test-object-invokablehooks.php'
		];

		$modeOptions = range(0, 15);

		foreach ($includeOptions as $includeOption) {
			foreach ($modeOptions as $modeOption) {
				yield [
					$includeOption,
					$modeOption
				];
			}
		}
	}
}
