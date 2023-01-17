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

use ShineUnited\WordPress\Hooks\Tests\TestCase as BaseTestCase;

/**
 * Base Hook Manager Test Case
 */
abstract class TestCase extends BaseTestCase {

	/**
	 * Initializes the native WP_Hook system.
	 *
	 * @return void
	 */
	protected function initializeWPHooks(): void {
		require_once(__DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/plugin.php');
	}
}
