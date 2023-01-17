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

use ShineUnited\WordPress\Hooks\Tests\HookManager\TestCase as BaseTestCase;

/**
 * Base Hook Manager Register Test Case
 */
abstract class TestCase extends BaseTestCase {

	/**
	 * Load and include file.
	 *
	 * @param string $include File to include.
	 *
	 * @return array Pre-processed test data.
	 */
	protected function loadInclude(string $include): array {
		return require(__DIR__ . '/inc/' . $include);
	}

	/**
	 * Process the test data.
	 *
	 * @param string       $include   File to include.
	 * @param string|array $hookTypes List of hook types to check.
	 *
	 * @return array Processed test data.
	 */
	public function loadTestData(string $include, string|array $hookTypes): array {
		$test = $this->loadInclude($include);

		$test['filters'] = [];
		$test['actions'] = [];

		if (is_string($hookTypes)) {
			$hookTypes = [$hookTypes];
		}

		$types = [];
		foreach (['callback', 'object', 'class', 'static'] as $type) {
			if (in_array($type, $hookTypes)) {
				$types[] = $type;
			}
		}

		foreach ($types as $type) {
			if (!isset($test[$type]) || !is_array($test[$type])) {
				continue;
			}

			if (isset($test[$type]['filters'])) {
				$test['filters'] = array_merge($test['filters'], $test[$type]['filters']);
			}

			if (isset($test[$type]['actions'])) {
				$test['actions'] = array_merge($test['actions'], $test[$type]['actions']);
			}
		}

		return $test;
	}

	/**
	 * @param array $filters Array of expected add_filter calls.
	 * @param array $actions Array of expected add_action calls.
	 *
	 * @return void
	 */
	protected function hookExpectations(array $filters = [], array $actions = []): void {
		$filterArgs = [];
		foreach ($filters as $filter) {
			$filterArgs[] = [
				$this->identicalTo($filter[0]),
				$this->identicalTo($filter[1]),
				$this->identicalTo($filter[2]),
				$this->identicalTo($filter[3])
			];
		}

		$actionArgs = [];
		foreach ($actions as $action) {
			$actionArgs[] = [
				$this->identicalTo($action[0]),
				$this->identicalTo($action[1]),
				$this->identicalTo($action[2]),
				$this->identicalTo($action[3])
			];
		}

		$mock = $this->mockGlobalFunction(['add_filter', 'add_action']);

		$mock
			->expects($this->exactly(count($filters)))
			->method('add_filter')
			->withConsecutive(...$filterArgs)
			->willReturn(true)
		;

		$mock
			->expects($this->exactly(count($actions)))
			->method('add_action')
			->withConsecutive(...$actionArgs)
			->willReturn(true)
		;
	}
}
