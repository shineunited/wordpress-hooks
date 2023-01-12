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

namespace ShineUnited\WordPress\Hooks\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base Test Case
 */
abstract class TestCase extends BaseTestCase {

	/**
	 * @return void
	 */
	protected function toDo(): void {
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
		self::markTestIncomplete(sprintf('To-Do: %s::%s', $caller['class'], $caller['function']));
	}

	/**
	 * Mock a global function.
	 *
	 * @param string|array $functions A string or array of string function names to mock.
	 *
	 * @return MockObject
	 */
	protected function mockGlobalFunction(string|array $functions): MockObject {
		if (!is_array($functions)) {
			$functions = [$functions];
		}

		$builder = $this->getMockBuilder(\stdClass::class);
		$builder->addMethods($functions);

		$mock = $builder->getMock();

		$key = uniqid('mock_global_' . implode('_', $functions) . '_', true);
		$GLOBALS[$key] = $mock;

		foreach ($functions as $function) {
			eval('
				namespace {
					function ' . $function . '() {
						$mock = $GLOBALS[\'' . addslashes($key) . '\'];
						return call_user_func_array([$mock, \'' . $function . '\'], func_get_args());
					}
				}
			');
		}

		return $mock;
	}
}
