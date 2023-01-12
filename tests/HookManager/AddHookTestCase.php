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
use Generator;
use stdClass;

/**
 * Base Add Hook Test Case
 */
abstract class AddHookTestCase extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$hmAddFunction = 'add' . $this->getHookType(true);
		$wpAddFunction = 'add_' . $this->getHookType(false);

		$mock = $this->mockGlobalFunction($wpAddFunction);

		$mock
			->expects($this->once())
			->method($wpAddFunction)
			->with(
				$this->identicalTo('test-hook'),
				$this->identicalTo('test_callback'),
				$this->identicalTo(5),
				$this->identicalTo(3)
			)
			->willReturn(true)
		;

		HookManager::$hmAddFunction('test-hook', 'test_callback', 5, 3);
	}

	/**
	 * Verifies the HookManager add function against the WordPress equivalent.
	 *
	 * @dataProvider addHookDataProvider
	 *
	 * @param string $label The current test label.
	 * @param array  $calls An array of add calls to run.
	 *
	 * @return void
	 */
	public function testAddHook(string $label, array $calls): void {
		$hmAddFunction = 'add' . $this->getHookType(true);
		$wpAddFunction = 'add_' . $this->getHookType(false);

		$prefix = $hmAddFunction . ' Test: ' . $label . "\n";

		$results = [];
		$count = 0;
		foreach ($calls as $call) {
			$count++;

			$returnValue = HookManager::$hmAddFunction(
				$call[0], // $hookName
				$call[1], // $callback
				$call[2], // $priority
				$call[3]  // $acceptedArgs
			);

			$this->assertSame($call[4], $returnValue, $prefix . 'Unexpected HookManager::' . $hmAddFunction . ' return value for call #' . $count);

			$results[$count] = [
				'call'   => $call,
				'actual' => $returnValue
			];
		}

		$this->initializeWPHooks();

		$actual = $GLOBALS['wp_filter'];

		$GLOBALS['wp_filter'] = [];
		foreach ($results as $count => $result) {
			$expected = $wpAddFunction(
				$result['call'][0], // $hookName
				$result['call'][1], // $callback
				$result['call'][2], // $priority
				$result['call'][3]  // $acceptedArgs
			);

			$message = $prefix . 'HookManager::' . $hmAddFunction . ' return value does not match ' . $wpAddFunction . ' for call #' . $count;
			$this->assertSame($expected, $result['actual'], $message);
		}

		$this->assertEquals($GLOBALS['wp_filter'], $actual, $prefix . 'Final state does not match WordPress output');
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function addHookDataProvider(): Generator {
		/**
		 * 	yield [
		 *		// test label
		 * 		$testLabel,
		 *		// array of calls to run
		 *		[
		 *			[$hookName, $callback, $priority, $acceptedArgs, $expectation],
		 *			...
		 *		]
		 * 	];
		 */

		yield [
			'Single hook with string callback.',
			[
				['test-hook', 'string_callback', 5, 2, true]
			]
		];

		yield [
			'Single hook with static array callback.',
			[
				['test-hook', ['static', 'callback'], 5, 2, true]
			]
		];

		yield [
			'Single hook with object array callback.',
			[
				['test-hook', [new stdClass(), 'callback'], 5, 2, true]
			]
		];

		yield [
			'Multiple hooks, all parameters different.',
			[
				['test-hook1', 'string_callback1', 1, 1, true],
				['test-hook2', 'string_callback2', 2, 2, true],
				['test-hook3', 'string_callback3', 3, 3, true],
				['test-hook4', 'string_callback4', 4, 4, true],
			]
		];

		yield [
			'Multiple hooks, all parameters different. Order reversed.',
			[
				['test-hook4', 'string_callback4', 4, 4, true],
				['test-hook3', 'string_callback3', 3, 3, true],
				['test-hook2', 'string_callback2', 2, 2, true],
				['test-hook1', 'string_callback1', 1, 1, true],
			]
		];

		yield [
			'Multiple hooks, same hook name.',
			[
				['test-hook1', 'string_callback1', 1, 1, true],
				['test-hook1', 'string_callback2', 2, 2, true],
				['test-hook1', 'string_callback3', 3, 3, true],
				['test-hook1', 'string_callback4', 4, 4, true],
			]
		];

		yield [
			'Multiple hooks, same hook name. Priority order reversed.',
			[
				['test-hook1', 'string_callback4', 4, 4, true],
				['test-hook1', 'string_callback3', 3, 3, true],
				['test-hook1', 'string_callback2', 2, 2, true],
				['test-hook1', 'string_callback1', 1, 1, true],
			]
		];

		yield [
			'Multiple hooks, same priority.',
			[
				['test-hook', 'string_callback1', 1, 1, true],
				['test-hook', 'string_callback2', 1, 2, true],
				['test-hook', 'string_callback3', 1, 3, true],
				['test-hook', 'string_callback4', 1, 4, true],
			]
		];

		yield [
			'Duplicate hooks with different accepted arg count.',
			[
				['test-hook', 'string_callback1', 1, 1, true],
				['test-hook', 'string_callback1', 1, 2, true],
			]
		];
	}
}
