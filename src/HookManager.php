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

namespace ShineUnited\WordPress\Hooks;

use Closure;
use Error;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;

/**
 * WordPress Hook Manager
 */
class HookManager {
	public const REGISTER_CALLBACK = 1;
	public const REGISTER_OBJECT = 2;
	public const REGISTER_CLASS = 4;
	public const REGISTER_STATIC = 8;
	public const REGISTER_ALL = 15;

	public const DEFAULT_PRIORITY = 10;
	public const DEFAULT_ACCEPTED_ARGS = 1;

	private static array $registeredCallbacks = [];
	private static array $registeredObjects = [];
	private static array $registeredClasses = [];

	/**
	 * Register a hook or set of hooks with WordPress. Automatically registers any callable,
	 * class or object according to $mode. May attempt to register multiple ways if applicable.
	 *
	 * Special Cases:
	 *   + Closures are callables and objects. By default register as callbacks only.
	 *   + Objects with the '__invoke' function are both callables and objects. Will register __invoke
	 *     function as a callback, and the object separately.
	 *   + Objects with static functions have their instance context functions registered
	 *     and their static functions registered as well if REGISTER_STATIC is set.
	 *
	 * @param callable|string|object $target Target to register.
	 * @param integer                $mode   Registration mode.
	 *
	 * @return void
	 */
	public static function register(callable|string|object $target, int $mode = self::REGISTER_ALL): void {
		if (($mode & self::REGISTER_CALLBACK) && is_callable($target)) {
			static::registerCallback($target);
		}

		if (($mode & self::REGISTER_OBJECT) && is_object($target) && !$target instanceof Closure) {
			static::registerObject($target, ($mode & self::REGISTER_STATIC) == true);
		}

		if (($mode & self::REGISTER_CLASS) && is_string($target) && class_exists($target, true)) {
			static::registerClass($target);
		}
	}

	/**
	 * Registers hooks for a callback with WordPress.
	 *
	 * Will only register callables with Hook attributes.
	 *
	 * @param callable $callback The callback to register.
	 *
	 * @return void
	 */
	public static function registerCallback(callable $callback): void {
		// generate de-duplication signature
		$signature = static::getCallbackSignature($callback);

		if (isset(self::$registeredCallbacks[$signature])) {
			// callback already registered
			return;
		} else {
			// mark as registered
			self::$registeredCallbacks[$signature] = true;
		}

		$hooks = static::getCallbackHookAttributes($callback);
		foreach ($hooks as $hook) {
			$hook->register($callback);
		}
	}

	/**
	 * Registers hooks for an object with WordPress.
	 *
	 * Will only register publically accessible methods with relevant attributes.
	 *
	 * @param object  $object        Object to register.
	 * @param boolean $includeStatic Optional. If false static methods will not be registered.
	 *
	 * @return void
	 */
	public static function registerObject(object $object, bool $includeStatic = true): void {
		// registers hooks for an object

		if ($object instanceof Closure) {
			// Closure objects need to be registered as callables

			return;
		}

		// generate de-duplication signature
		$signature = static::getObjectSignature($object);

		if (isset(self::$registeredObjects[$signature])) {
			// object already registered
			return;
		} else {
			// mark as registered
			self::$registeredObjects[$signature] = true;
		}

		$reflector = new ReflectionObject($object);
		foreach ($reflector->getMethods() as $method) {
			if ($method->isStatic()) {
				// method is static, skip
				continue;
			}

			if (!$method->isPublic()) {
				// method is non-public, skip
				continue;
			}

			if ($method->getName() == '__invoke') {
				// skip __invoke method
				continue;
			}

			$callback = [$object, $method->getName()];
			static::registerCallback($callback);
		}

		if ($includeStatic) {
			static::registerClass($object::class);
		}
	}

	/**
	 * Registers hooks for a class with WordPress.
	 *
	 * Will only register publically accessible static methods with Hook attributes.
	 *
	 * @param string $classname The full classname.
	 *
	 * @return void
	 */
	public static function registerClass(string $classname): void {
		if (!class_exists($classname, true)) {
			// invalid classname
			return;
		}

		// generate de-duplication signature
		$signature = static::getClassSignature($classname);

		if (isset(self::$registeredClasses[$signature])) {
			// classname already registered
			return;
		} else {
			// mark as registered
			self::$registeredClasses[$signature] = true;
		}

		$reflector = new ReflectionClass($classname);
		foreach ($reflector->getMethods() as $method) {
			if (!$method->isStatic()) {
				// method is non-static, skip
				continue;
			}

			if (!$method->isPublic()) {
				// method is non-public, skip
				continue;
			}

			$callback = [$classname, $method->getName()];
			static::registerCallback($callback);
		}
	}

	/**
	 * Adds a callback function to a filter hook.
	 *
	 * @param string                $hookName     The name of the filter to add a the callback to.
	 * @param callable|string|array $callback     The callback to be run when the filter is applied.
	 * @param integer               $priority     Optional. Used to specify the order in which the functions
	 *                                            associated with a particular filter are executed.
	 *                                            Lower numbers correspond with earlier execution,
	 *                                            and functions with the same priority are executed
	 *                                            in the order in which they were added to the filter.
	 * @param integer               $acceptedArgs Optional. The number of arguments the function accepts.
	 *
	 * @return boolean Always returns true.
	 */
	public static function addFilter(string $hookName, callable|string|array $callback, ?int $priority = null, ?int $acceptedArgs = null): bool {
		if (is_null($priority)) {
			$priority = self::DEFAULT_PRIORITY;
		}

		if (is_null($acceptedArgs)) {
			if (is_callable($callback)) {
				$acceptedArgs = static::getCallbackParameterCount($callback);
			} else {
				$acceptedArgs = self::DEFAULT_ACCEPTED_ARGS;
			}
		}

		if (function_exists('add_filter')) {
			// wordpress has been initialized, use add_filter instead
			return add_filter($hookName, $callback, $priority, $acceptedArgs);
		}

		if (!isset($GLOBALS['wp_filter'])) {
			// define wp_filter global
			$GLOBALS['wp_filter'] = [];
		}

		$hookExists = isset($GLOBALS['wp_filter'][$hookName]);
		if (!$hookExists) {
			// create new hook name
			$GLOBALS['wp_filter'][$hookName] = [];
		}

		$priorityExists = isset($GLOBALS['wp_filter'][$hookName][$priority]);
		if (!$priorityExists) {
			// create new priority
			$GLOBALS['wp_filter'][$hookName][$priority] = [];
		}

		// add hook to global wp_filter
		$GLOBALS['wp_filter'][$hookName][$priority][] = [
			'accepted_args' => $acceptedArgs,
			'function'      => $callback
		];

		if (!$priorityExists && count($GLOBALS['wp_filter'][$hookName]) > 1) {
			// sort hook priorities
			ksort($GLOBALS['wp_filter'][$hookName], SORT_NUMERIC);
		}

		return true;
	}

	/**
	 * Calls the callback functions that have been added to a filter hook.
	 *
	 * @param string $hookName The name of the filter hook.
	 * @param mixed  $value    The value to filter.
	 * @param mixed  ...$args  Additional parameters to pass to the callback functions.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public static function applyFilters(string $hookName, mixed $value, mixed ...$args): mixed {
		try {
			return apply_filters(...func_get_args());
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Calls the callback functions that have been added to a filter hook, specifying arguments in an array.
	 *
	 * @param string $hookName The name of the filter hook.
	 * @param array  $args     The arguments supplied to the functions hooked to `$hookName`.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public static function applyFiltersRefArray(string $hookName, array $args): mixed {
		try {
			return apply_filters_ref_array($hookName, $args);
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Checks if any filter has been registered for a hook.
	 *
	 * Note: the native WP_Hook functions will result in overriding if the callback signature matches
	 * an existing callback, but this funtion will allow duplicates. This should be irrelevant since
	 * the duplicated callback will be overridden when wordpress loads the filters.
	 *
	 * @param string                      $hookName The name of the filter hook.
	 * @param callable|string|array|false $callback Optional. The callback to check for.
	 *                                              This function can be called unconditionally to speculatively check
	 *                                              a callback that may or may not exist. Default false.
	 *
	 * @return boolean|integer If `$callback` is omitted, returns boolean for whether the hook has
	 *                         anything registered. When checking a specific function, the priority
	 *                         of that hook is returned, or false if the function is not attached.
	 */
	public static function hasFilter(string $hookName, callable|string|array|false $callback = false): bool|int {
		if (function_exists('has_filter')) {
			// wordpress has been initialized, use has_filter instead
			return has_filter($hookName, $callback);
		}

		if (!isset($GLOBALS['wp_filter'])) {
			// define wp_filter global
			$GLOBALS['wp_filter'] = [];
		}

		if (!isset($GLOBALS['wp_filter'][$hookName])) {
			// hook name does not exist
			return false;
		}

		if ($callback === false) {
			// hook name exists but callback is false
			return true;
		}

		$signature = static::getCallbackSignature($callback);
		foreach ($GLOBALS['wp_filter'][$hookName] as $priority => $hooks) {
			foreach ($hooks as $hook) {
				if ($signature == static::getCallbackSignature($hook['function'])) {
					// callback matches existing callback
					return $priority;
				}
			}
		}

		// there is no matching callback defined for the specified hook name
		return false;
	}

	/**
	 * Removes a callback function from a filter hook.
	 *
	 * @param string                $hookName The filter hook to which the function to be removed is hooked.
	 * @param callable|string|array $callback The callback to be removed from running when the filter is applied.
	 *                                        This function can be called unconditionally to speculatively remove
	 *                                        a callback that may or may not exist.
	 * @param integer               $priority Optional. The exact priority used when adding the original
	 *                                        filter callback. Default 10.
	 *
	 * @return boolean Whether the function existed before it was removed.
	 */
	public static function removeFilter(string $hookName, callable|string|array $callback, int $priority = self::DEFAULT_PRIORITY): bool {
		if (function_exists('remove_filter')) {
			// wordpress has been initialized, use remove_filter instead
			return remove_filter($hookName, $callback, $priority);
		}

		if (!isset($GLOBALS['wp_filter'])) {
			// define wp_filter global
			$GLOBALS['wp_filter'] = [];
		}

		if (!isset($GLOBALS['wp_filter'][$hookName])) {
			// hook name is not defined
			return false;
		}

		if (!isset($GLOBALS['wp_filter'][$hookName][$priority])) {
			// priority is not defined
			return false;
		}

		$signature = static::getCallbackSignature($callback);
		$found = false;
		$remaining = [];
		foreach ($GLOBALS['wp_filter'][$hookName][$priority] as $hook) {
			if ($found || $signature != static::getCallbackSignature($hook['function'])) {
				$remaining[] = $hook;
			} else {
				$found = true;
			}
		}

		if ($found) {
			$GLOBALS['wp_filter'][$hookName][$priority] = $remaining;

			if (count($GLOBALS['wp_filter'][$hookName][$priority]) == 0) {
				// no callbacks on priority
				unset($GLOBALS['wp_filter'][$hookName][$priority]);
			}

			if (count($GLOBALS['wp_filter'][$hookName]) == 0) {
				// no callbacks on hook name
				unset($GLOBALS['wp_filter'][$hookName]);
			}
		}

		return $found;
	}

	/**
	 * Removes all of the callback functions from a filter hook.
	 *
	 * @param string          $hookName The filter to remove callbacks from.
	 * @param integer|boolean $priority Optional. The priority number to remove them from.
	 *                                  Default false.
	 *
	 * @return boolean Always returns true.
	 */
	public static function removeAllFilters(string $hookName, int|bool $priority = false): bool {
		if (function_exists('remove_all_filters')) {
			// wordpress has been initialized, use remove_all_filters instead
			return remove_all_filters($hookName, $priority);
		}

		if (!isset($GLOBALS['wp_filter'])) {
			// define wp_filter global
			$GLOBALS['wp_filter'] = [];
		}

		if (!isset($GLOBALS['wp_filter'][$hookName])) {
			// hook name does not exist
			return true;
		}

		if ($priority === false) {
			// hook name exists, but priority is false
			unset($GLOBALS['wp_filter'][$hookName]);
		} elseif (isset($GLOBALS['wp_filter'][$hookName][$priority])) {
			// priority exists
			unset($GLOBALS['wp_filter'][$hookName][$priority]);
		}

		return true;
	}

	/**
	 * Retrieves the name of the current filter hook.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return string Hook name of the current filter.
	 */
	public static function currentFilter(): string {
		try {
			return current_filter();
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Returns whether or not a filter hook is currently being processed.
	 *
	 * @param null|string $hookName Optional. Filter hook to check. Defaults to null,
	 *                              which checks if any filter is currently being run.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return boolean Whether the filter is currently in the stack.
	 */
	public static function doingFilter(null|string $hookName = null): bool {
		try {
			return doing_filter($hookName);
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Adds a callback function to an action hook.
	 *
	 * @param string                $hookName     The name of the action to add the callback to.
	 * @param callable|string|array $callback     The callback to be run when the action is called.
	 * @param integer               $priority     Optional. Used to specify the order in which the functions
	 *                                            associated with a particular action are executed.
	 *                                            Lower numbers correspond with earlier execution,
	 *                                            and functions with the same priority are executed
	 *                                            in the order in which they were added to the action.
	 * @param integer               $acceptedArgs Optional. The number of arguments the function accepts.
	 *
	 * @return boolean Always returns true.
	 */
	public static function addAction(string $hookName, callable|string|array $callback, ?int $priority = null, ?int $acceptedArgs = null): bool {
		if (is_null($priority)) {
			$priority = self::DEFAULT_PRIORITY;
		}

		if (is_null($acceptedArgs)) {
			if (is_callable($callback)) {
				$acceptedArgs = static::getCallbackParameterCount($callback);
			} else {
				$acceptedArgs = self::DEFAULT_ACCEPTED_ARGS;
			}
		}

		if (function_exists('add_action')) {
			// wordpress has been initialized, use add_action instead
			return add_action($hookName, $callback, $priority, $acceptedArgs);
		}

		return static::addFilter($hookName, $callback, $priority, $acceptedArgs);
	}

	/**
	 * Calls the callback functions that have been added to an action hook.
	 *
	 * @param string $hookName The name of the action to be executed.
	 * @param mixed  ...$args  Optional. Additional arguments which are passed on to the
	 *                         functions hooked to the action. Default empty.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return void
	 */
	public static function doAction(string $hookName, mixed ...$args): void {
		try {
			do_action(...func_get_args());
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Calls the callback functions that have been added to an action hook, specifying arguments in an array.
	 *
	 * @param string $hookName The name of the action to be executed.
	 * @param array  $args     The arguments supplied to the functions hooked to `$hookName`.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return void
	 */
	public static function doActionRefArray(string $hookName, array $args): void {
		try {
			do_action_ref_array($hookName, $args);
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Checks if any action has been registered for a hook.
	 *
	 * @param string                      $hookName The name of the action hook.
	 * @param callable|string|array|false $callback Optional. The callback to check for.
	 *                                              This function can be called unconditionally to speculatively check
	 *                                              a callback that may or may not exist. Default false.
	 *
	 * @return boolean|integer If `$callback` is omitted, returns boolean for whether the hook has
	 *                  anything registered. When checking a specific function, the priority
	 *                  of that hook is returned, or false if the function is not attached.
	 */
	public static function hasAction(string $hookName, callable|string|array|false $callback = false): bool|int {
		if (function_exists('has_action')) {
			// wordpress has been initialized, use has_action instead
			return has_action($hookName, $callback);
		}

		return static::hasFilter($hookName, $callback);
	}

	/**
	 * Removes a callback function from an action hook.
	 *
	 * @param string                $hookName The action hook to which the function to be removed is hooked.
	 * @param callable|string|array $callback The name of the function which should be removed.
	 *                                        This function can be called unconditionally to speculatively remove
	 *                                        a callback that may or may not exist.
	 * @param integer               $priority Optional. The exact priority used when adding the original
	 *                                        action callback. Default 10.
	 *
	 * @return boolean Whether the function is removed.
	 */
	public static function removeAction(string $hookName, callable|string|array $callback, int $priority = self::DEFAULT_PRIORITY): bool {
		if (function_exists('remove_action')) {
			// wordpress has been initialized, use remove_action instead
			return remove_action($hookName, $callback, $priority);
		}

		return static::removeFilter($hookName, $callback, $priority);
	}

	/**
	 * Removes all of the callback functions from an action hook.
	 *
	 * @param string        $hookName The action to remove callbacks from.
	 * @param integer|false $priority Optional. The priority number to remove them from.
	 *                                Default false.
	 *
	 * @return boolean Always returns true.
	 */
	public static function removeAllActions(string $hookName, int|false $priority = false): bool {
		if (function_exists('remove_all_actions')) {
			// wordpress has been initialized, use remove_all_actions instead
			return remove_all_actions($hookName, $priority);
		}

		return static::removeAllFilters($hookName, $priority);
	}

	/**
	 * Retrieves the name of the current action hook.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return string Hook name of the current action.
	 */
	public static function currentAction(): string {
		try {
			return current_action();
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Returns whether or not an action hook is currently being processed.
	 *
	 * @param string|null $hookName Optional. Action hook to check. Defaults to null,
	 *                              which checks if any action is currently being run.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return boolean Whether the action is currently in the stack.
	 */
	public static function doingAction(string|null $hookName = null): bool {
		try {
			return doing_action($hookName);
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Retrieves the number of times an action has been fired during the current request.
	 *
	 * @param string $hookName The name of the action hook.
	 *
	 * @throws UninitializedError If WordPress has not been initialized.
	 *
	 * @return integer The number of times the action hook has been fired.
	 */
	public static function didAction(string $hookName): int {
		try {
			return did_action($hookName);
		} catch (Error $error) {
			throw new UninitializedError($error);
		}
	}

	/**
	 * Generate a signature for callback matching.
	 *
	 * @param callable|string|array $callback The callback function to generate a signature for.
	 *
	 * @return string The signature string.
	 */
	protected static function getCallbackSignature(callable|string|array $callback): string {
		if (is_string($callback)) {
			return $callback;
		}

		if ($callback instanceof Closure) {
			$callback = [$callback, ''];
		} elseif (is_object($callback)) {
			$callback = [$callback, '__invoke'];
		} else {
			$callback = (array) $callback;
		}

		if (is_object($callback[0])) {
			return 'callback-' . spl_object_hash($callback[0]) . $callback[1];
		} elseif (is_string($callback[0])) {
			return 'callback-' . $callback[0] . '::' . $callback[1];
		}

		// should be impossible, all possible conditions have been handled
		return 'callback';
	}

	/**
	 * Generate a signature for object matching.
	 *
	 * @param object $object The object to generate a signature for.
	 *
	 * @return string The signature string.
	 */
	protected static function getObjectSignature(object $object): string {
		return 'object-' . spl_object_hash($object);
	}

	/**
	 * Generate a signature for class matching.
	 *
	 * @param string $classname The classname to generate a signature for.
	 *
	 * @return string The signature string.
	 */
	protected static function getClassSignature(string $classname): string {
		return 'class-' . $classname;
	}

	/**
	 * Get Hooks for a callback.
	 *
	 * @param callable $callback The callback.
	 *
	 * @return Hook[] Array of callback hooks.
	 */
	protected static function getCallbackHookAttributes(callable $callback): iterable {
		$reflector = false;
		if (is_string($callback)) {
			$reflector = new ReflectionFunction($callback);
		} elseif (is_array($callback)) {
			$reflector = new ReflectionMethod($callback[0], $callback[1]);
		} elseif ($callback instanceof Closure) {
			$reflector = new ReflectionFunction($callback);
		} elseif (is_object($callback) && is_callable([$callback, '__invoke'])) {
			$reflector = new ReflectionObject($callback);
		} else {
			// unknown/invalid callback type
			return [];
		}

		$attributes = $reflector->getAttributes(Hook::class, ReflectionAttribute::IS_INSTANCEOF);
		if (count($attributes) == 0) {
			// no hook attributes defined
			return [];
		}

		$hooks = [];
		foreach ($attributes as $attribute) {
			$hooks[] = $attribute->newInstance();
		}

		return $hooks;
	}

	/**
	 * Get the parameter count for a callback.
	 *
	 * @param callable $callback The callback.
	 *
	 * @return integer The parameter count.
	 */
	protected static function getCallbackParameterCount(callable $callback): int {
		$reflector = false;
		if (is_string($callback)) {
			$reflector = new ReflectionFunction($callback);
		} elseif (is_array($callback)) {
			$reflector = new ReflectionMethod($callback[0], $callback[1]);
		} elseif ($callback instanceof Closure) {
			$reflector = new ReflectionFunction($callback);
		} elseif (is_object($callback) && is_callable([$callback, '__invoke'])) {
			$reflector = new ReflectionMethod($callback, '__invoke');
		} else {
			// unknown/invalid callback type
			return 0;
		}

		return $reflector->getNumberOfParameters();
	}
}
