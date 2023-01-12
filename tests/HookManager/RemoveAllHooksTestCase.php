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
 * Base Remove All Hooks Test Case
 */
abstract class RemoveAllHooksTestCase extends TestCase {

	/**
	 * @return void
	 */
	public function testPassthru(): void {
		$hmRemoveAllFunction = 'removeAll' . $this->getHookType(true) . 's';
		$wpRemoveAllFunction = 'remove_all_' . $this->getHookType(false) . 's';

		$mock = $this->mockGlobalFunction($wpRemoveAllFunction);

		$mock
			->expects($this->once())
			->method($wpRemoveAllFunction)
			->with(
				$this->identicalTo('test-hook'),
				$this->identicalTo(5)
			)
			->willReturn(true)
		;

		HookManager::$hmRemoveAllFunction('test-hook', 5);
	}

	/**
	 * Verifies the HookManager remove all function against the WordPress equivalent.
	 *
	 * @dataProvider removeAllHooksDataProvider
	 *
	 * @param string $label The current test label.
	 * @param array  $hooks An array of hooks to add.
	 * @param array  $calls An array of remove all calls to run.
	 *
	 * @return void
	 */
	public function testRemoveAllHooks(string $label, array $hooks, array $calls): void {
		$hmRemoveAllFunction = 'removeAll' . $this->getHookType(true) . 's';
		$wpRemoveAllFunction = 'remove_all_' . $this->getHookType(false) . 's';
		$hmAddFunction = 'add' . $this->getHookType(true);
		$wpAddFunction = 'add_' . $this->getHookType(false);

		$prefix = $hmRemoveAllFunction . ' Test: ' . $label . "\n";

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

			$returnValue = HookManager::$hmRemoveAllFunction(
				$call[0], // $hookName
				$call[1]  // $priority
			);

			$this->assertSame($call[2], $returnValue, $prefix . 'Unexpected HookManager::' . $hmRemoveAllFunction . ' return value for call #' . $count);

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
			$expected = $wpRemoveAllFunction(
				$result['call'][0], // $hookName
				$result['call'][1]  // $priority
			);

			$message = $prefix . 'HookManager::' . $hmRemoveAllFunction . ' return value does not match ' . $wpRemoveAllFunction . ' for call #' . $count;
			$this->assertSame($expected, $result['actual'], $message);
		}

		$this->assertEquals($GLOBALS['wp_filter'], $actual, $prefix . 'Final state does not match WordPress output');
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function removeAllHooksDataProvider(): Generator {
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

		$object = new stdClass();

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
			'Multiple hooks, various names/priorities/callbacks. Check removal of non-matching names.',
			$hooks,
			[
				['missing-hook', 10, true],
				['missing-hook', 5, true],
				['test-hook', 10, true]
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check removal of all hooks for name.',
			$hooks,
			[
				['test-hook2', false, true]
			]
		];

		yield [
			'Multiple hooks, various names/priorites/callbacks. Check removal of all hooks for name/priority.',
			$hooks,
			[
				['test-hook2', 6, true]
			]
		];
	}
}
