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

namespace ShineUnited\WordPress\Hooks\Tests\Type;

/**
 * Typed Interface
 */
interface Typed {

	/**
	 * Get the hook type.
	 *
	 * @param boolean $initialCaps If true capitalize the type.
	 *
	 * @return string The hook type.
	 */
	public function getHookType(bool $initialCaps = false): string;
}
