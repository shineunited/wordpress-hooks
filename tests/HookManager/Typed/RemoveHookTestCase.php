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
 * Base Remove Hook Test Case
 */
abstract class RemoveHookTestCase extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$hmRemoveFunction = 'remove' . $this->getHookType(true);
		$wpRemoveFunction = 'remove_' . $this->getHookType(false);

		$mock = $this->mockGlobalFunction($wpRemoveFunction);

		$mock
			->expects($this->once())
			->method($wpRemoveFunction)
			->with(
				$this->identicalTo('test-hook'),
				$this->identicalTo('test_callback'),
				$this->identicalTo(5)
			)
			->willReturn(true)
		;

		HookManager::$hmRemoveFunction('test-hook', 'test_callback', 5);
	}

	/**
	 * * Verifies the HookManager remove function against the WordPress equivalent.
	 *
	 * @dataProvider removeHookDataProvider
	 *
	 * @param string $label The current test label.
	 * @param array  $hooks An array of hooks to add.
	 * @param array  $calls An array of remove calls to run.
	 *
	 * @return void
	 */
	public function testRemoveHook(string $label, array $hooks, array $calls): void {
		$hmRemoveFunction = 'remove' . $this->getHookType(true);
		$wpRemoveFunction = 'remove_' . $this->getHookType(false);
		$hmAddFunction = 'add' . $this->getHookType(true);
		$wpAddFunction = 'add_' . $this->getHookType(false);

		$prefix = $hmRemoveFunction . ' Test: ' . $label . "\n";

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

			$returnValue = HookManager::$hmRemoveFunction(
				$call[0], // $hookName
				$call[1], // $callback,
				$call[2]  // $priority
			);

			$this->assertSame($call[3], $returnValue, $prefix . 'Unexpected HookManager::' . $hmRemoveFunction . ' return value for call #' . $count);

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
			$expected = $wpRemoveFunction(
				$result['call'][0], // $hookName
				$result['call'][1], // $callback
				$result['call'][2]  // $priority
			);

			$message = $prefix . 'HookManager::' . $hmRemoveFunction . ' return value does not match ' . $wpRemoveFunction . ' for call #' . $count;
			$this->assertSame($expected, $result['actual'], $message);
		}

		$this->assertEquals($GLOBALS['wp_filter'], $actual, $prefix . 'Final state does not match WordPress output');
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function removeHookDataProvider(): Generator {
		/**
		 * 	yield [
		 *		// test label
		 * 		$testLabel,
		 *		// array of hooks to add
		 *		[
		 *			[$hookName, $callback, $priority, $acceptedArgs],
		 *			...
		 *		],
		 *		// array of calls to run
		 *		[
		 *			[$hookName, $callback, $priority, $expectation],
		 *			...
		 *		]
		 * 	];
		 */


		yield [
			'Single filter with string callback. Check mismatches and duplicate removal.',
			[
				['test-hook', 'string_callback', 5, 2]
			],
			[
				['missing-hook', 'string_callback', 5, false], // hookName does not match
				['test-hook', 'missing_callback', 5, false], // callback does not match
				['test-hook', 'string_callback', 10, false], // priority does not match
				['test-hook', 'string_callback', 5, true], // everything matches
				['test-hook', 'string_callback', 5, false] // hook already removed
			]
		];

		yield [
			'Single hook with static array callback. Check mismatches and duplicate removal.',
			[
				['test-hook', ['static', 'callback'], 5, 2]
			],
			[
				['missing-hook', ['static', 'callback'], 5, false], // hookName does not match
				['test-hook', 'missing_callback', 5, false], // callback does not match
				['test-hook', ['static', 'callback'], 10, false], // priority does not match
				['test-hook', ['static', 'callback'], 5, true], // everything matches
				['test-hook', ['static', 'callback'], 5, false] // hook already removed
			]
		];

		$object = new stdClass();
		yield [
			'Single hook with object array callback. Check mismatches and duplicate removal.',
			[
				['test-hook', [$object, 'callback'], 5, 2]
			],
			[
				['missing-hook', [$object, 'callback'], 5, false], // hookName does not match
				['test-hook', 'missing_callback', 5, false], // callback does not match
				['test-hook', [$object, 'callback'], 10, false], // priority does not match
				['test-hook', [$object, 'callback'], 5, true], // everything matches
				['test-hook', [$object, 'callback'], 5, false] // hook already removed
			]
		];


		$hooks = [
			['test-hook1', 'string_callback1', 1, 1],
			['test-hook1', 'string_callback2', 2, 2],
			['test-hook1', 'string_callback3', 3, 3],
			['test-hook1', 'string_callback4', 4, 4],
			['test-hook2', 'string_callback1', 5, 1],
			['test-hook2', 'string_callback2', 5, 2],
			['test-hook2', 'string_callback3', 6, 3],
			['test-hook2', 'string_callback4', 6, 4],
			['test-hook', 'string_callback', 5, 2],
			['test-hook', ['static', 'callback'], 5, 2],
			['test-hook', [$object, 'callback'], 5, 2],
		];


		yield [
			'Multiple hooks, various names/priorites/callbacks. Check remove of single string hook.',
			$hooks,
			[
				['test-hook', 'string_callback', 5, true],
				['test-hook', 'string_callback', 5, false],
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check remove of single static array hook.',
			$hooks,
			[
				['test-hook', ['static', 'callback'], 5, true],
				['test-hook', ['static', 'callback'], 5, false],
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check remove of single object array hook.',
			$hooks,
			[
				['test-hook', [$object, 'callback'], 5, true],
				['test-hook', [$object, 'callback'], 5, false],
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check removal of all hooks for name.',
			$hooks,
			[
				['test-hook2', 'string_callback1', 5, true],
				['test-hook2', 'string_callback2', 5, true],
				['test-hook2', 'string_callback3', 6, true],
				['test-hook2', 'string_callback4', 6, true]
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check removal of all hooks for name/priority.',
			$hooks,
			[
				['test-hook2', 'string_callback3', 6, true],
				['test-hook2', 'string_callback4', 6, true]
			]
		];
	}
}
