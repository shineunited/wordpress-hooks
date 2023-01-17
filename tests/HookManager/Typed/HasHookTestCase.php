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

namespace ShineUnited\WordPress\Hooks\Tests\HookManager\Typed;

use ShineUnited\WordPress\Hooks\HookManager;
use Generator;
use stdClass;

/**
 * Base Has Hook Test Case
 */
abstract class HasHookTestCase extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$hmHasFunction = 'has' . $this->getHookType(true);
		$wpHasFunction = 'has_' . $this->getHookType(false);

		$mock = $this->mockGlobalFunction($wpHasFunction);

		$mock
			->expects($this->once())
			->method($wpHasFunction)
			->with(
				$this->identicalTo('test-hook'),
				$this->identicalTo('test_callback')
			)
			->willReturn(true)
		;

		HookManager::$hmHasFunction('test-hook', 'test_callback');
	}

	/**
	 * Verifies the HookManager has function against the WordPress equivalent.
	 *
	 * @dataProvider hasHookDataProvider
	 *
	 * @param string $label The current test label.
	 * @param array  $hooks An array of hooks to add.
	 * @param array  $calls An array of has calls to run.
	 *
	 * @return void
	 */
	public function testHasHook(string $label, array $hooks, array $calls): void {
		$hmHasFunction = 'has' . $this->getHookType(true);
		$wpHasFunction = 'has_' . $this->getHookType(false);
		$hmAddFunction = 'add' . $this->getHookType(true);
		$wpAddFunction = 'add_' . $this->getHookType(false);

		$prefix = $hmHasFunction . ' Test: ' . $label . "\n";

		foreach ($hooks as $hook) {
			HookManager::$hmAddFunction(
				$hook[0], // $hookName
				$hook[1], // $callback
				$hook[2], // $priority
				$hook[3]  // $acceptedArgs
			);
		}

		$results = [];
		$count = 0;
		foreach ($calls as $call) {
			$count++;

			$returnValue = HookManager::$hmHasFunction(
				$call[0], // $hookName
				$call[1]  // $callback
			);

			$this->assertSame($call[2], $returnValue, $prefix . 'Unexpected HookManager::' . $hmHasFunction . ' return value for check #' . $count);

			$results[$count] = [
				'call'   => $call,
				'actual' => $returnValue
			];
		}

		$this->initializeWPHooks();

		$actual = $GLOBALS['wp_filter'];

		$GLOBALS['wp_filter'] = [];
		foreach ($hooks as $hook) {
			$wpAddFunction(
				$hook[0], // $hookName
				$hook[1], // $callback
				$hook[2], // $priority
				$hook[3]  // $acceptedArgs
			);
		}

		foreach ($results as $count => $result) {
			$expected = $wpHasFunction(
				$result['call'][0], // $hookName
				$result['call'][1]  // $callback
			);

			$message = $prefix . 'HookManager::' . $hmHasFunction . ' return value does not match ' . $wpHasFunction . ' for call #' . $count;
			$this->assertSame($expected, $result['actual'], $message);
		}

		//$this->assertEquals($GLOBALS['wp_filter'], $actual, $prefix . 'Final state does not match WordPress output');
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function hasHookDataProvider(): Generator {
		/**
		 * 	yield [
		 *		// test label
		 * 		$testLabel,
		 *		// array of filters to add
		 *		[
		 *			[$hookName, $callback, $priority, $acceptedArgs],
		 *			...
		 *		],
		 *		// array of calls to run
		 *		[
		 *			[$hookName, $callback, $expectation],
		 *			...
		 *		]
		 * 	];
		 */


		yield [
			'Single filter with string callback. Check with callback, without callback and missing.',
			[
				['test-filter', 'string_callback', 5, 2]
			],
			[
				['test-filter', 'string_callback', 5],
				['test-filter', false, true],
				['missing-filter', 'string_callback', false],
				['missing-filter', false, false]
			]
		];

		yield [
			'Single filter with static array callback. Check with callback.',
			[
				['test-filter', ['static', 'callback'], 5, 2]
			],
			[
				['test-filter', ['static', 'callback'], 5]
			]
		];

		$object = new stdClass();
		yield [
			'Single filter with object array callback. Check with callback.',
			[
				['test-filter', [$object, 'callback'], 5, 2]
			],
			[
				['test-filter', [$object, 'callback'], 5]
			]
		];

		yield [
			'Multiple filters, all parameters different. Check for second filter with and without callback.',
			[
				['test-filter1', 'string_callback1', 1, 1],
				['test-filter2', 'string_callback2', 2, 2],
				['test-filter3', 'string_callback3', 3, 3],
				['test-filter4', 'string_callback4', 4, 4],
			],
			[
				['test-filter2', 'string_callback2', 2],
				['test-filter2', false, true]
			]
		];

		yield [
			'Multiple filters, all parameters different. Order reversed. Check for second filter with and without callback.',
			[
				['test-filter4', 'string_callback4', 4, 4],
				['test-filter3', 'string_callback3', 3, 3],
				['test-filter2', 'string_callback2', 2, 2],
				['test-filter1', 'string_callback1', 1, 1],
			],
			[
				['test-filter2', 'string_callback2', 2],
				['test-filter2', false, true]
			]
		];

		yield [
			'Multiple filters, same hook name. Check for second filter with and without callback.',
			[
				['test-filter1', 'string_callback1', 1, 1],
				['test-filter1', 'string_callback2', 2, 2],
				['test-filter1', 'string_callback3', 3, 3],
				['test-filter1', 'string_callback4', 4, 4],
			],
			[
				['test-filter1', 'string_callback2', 2],
				['test-filter1', false, true]
			]
		];

		yield [
			'Multiple filters, same hook name. Priority order reversed. Check for second filter with and without callback.',
			[
				['test-filter1', 'string_callback4', 4, 4],
				['test-filter1', 'string_callback3', 3, 3],
				['test-filter1', 'string_callback2', 2, 2],
				['test-filter1', 'string_callback1', 1, 1],
			],
			[
				['test-filter1', 'string_callback2', 2],
				['test-filter1', false, true]
			]
		];

		yield [
			'Multiple filters, same priority. Check for second filter with and without callback.',
			[
				['test-filter', 'string_callback1', 1, 1],
				['test-filter', 'string_callback2', 1, 2],
				['test-filter', 'string_callback3', 1, 3],
				['test-filter', 'string_callback4', 1, 4],
			],
			[
				['test-filter', 'string_callback2', 1],
				['test-filter', false, true]
			]
		];

		yield [
			'Duplicate filters with different accepted arg count. Check for second filter with and without callback.',
			[
				['test-filter', 'string_callback1', 1, 1],
				['test-filter', 'string_callback1', 1, 2],
			],
			[
				['test-filter', 'string_callback1', 1],
				['test-filter', false, true]
			]
		];
	}
}
