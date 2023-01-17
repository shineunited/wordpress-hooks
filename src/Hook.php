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

/**
 * Hook Attribute Base Class
 */
abstract class Hook {
	private string $name;
	private int $priority;

	/**
	 * Initialize the attribute.
	 *
	 * @param string  $name     The hook name.
	 * @param integer $priority The hook priority.
	 */
	public function __construct(string $name, ?int $priority = null) {
		$this->name = $name;
		$this->priority = $priority ?? HookManager::DEFAULT_PRIORITY;
	}

	/**
	 * Get the hook name.
	 *
	 * @return string The hook name.
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get the hook priority.
	 *
	 * @return integer The hook priority.
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * Register the callback.
	 *
	 * @param callable $callback The callback to register.
	 *
	 * @return boolean Always return true.
	 */
	abstract public function register(callable $callback): bool;
}
