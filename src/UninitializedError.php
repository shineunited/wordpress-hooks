<?php

/**
 * This file is part of WordPress Hooks.
 *
 * (c) Shine United LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package ShineUnited\WordPress\Hooks
 */

declare(strict_types=1);

namespace ShineUnited\WordPress\Hooks;

use Error;

/**
 * Uninitialized Error
 */
class UninitializedError extends Error {

	/**
	 * Initialze the error.
	 *
	 * @param Error $error The previous error (optional).
	 */
	public function __construct(?Error $error = null) {
		parent::__construct('WordPress has not been initialized', 0, $error);
	}
}
