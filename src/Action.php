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

use Attribute;

/**
 * Action Attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Action extends Hook {

	/**
	 * {@inheritdoc}
	 */
	public function register(callable $callback): bool {
		return HookManager::addAction($this->getName(), $callback, $this->getPriority());
	}
}
