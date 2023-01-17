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

namespace ShineUnited\WordPress\Hooks\Tests\Attribute;

use ShineUnited\WordPress\Hooks\Tests\TestCase as BaseTestCase;
use ShineUnited\WordPress\Hooks\Tests\Type\Typed;
use ShineUnited\WordPress\Hooks\Hook;
use ShineUnited\WordPress\Hooks\HookManager;
use Attribute;
use Generator;
use ReflectionClass;

/**
 * Base Attribute Test Case
 */
abstract class TestCase extends BaseTestCase implements Typed {

	/**
	 * @return void
	 */
	public function testConstructor(): void {
		$classname = 'ShineUnited\\WordPress\\Hooks\\' . $this->getHookType(true);

		$action = new $classname('test-hook');

		$this->assertInstanceOf(Hook::class, $action);
	}

	/**
	 * @return void
	 */
	public function testFlags(): void {
		$classname = 'ShineUnited\\WordPress\\Hooks\\' . $this->getHookType(true);
		$class = new ReflectionClass($classname);

		$attributes = $class->getAttributes(Attribute::class);

		$this->assertCount(1, $attributes, $classname . ' must be an attribute.');

		$attribute = $attributes[0]->newInstance();

		$this->assertInstanceOf(Attribute::class, $attribute);

		$flags = [
			'TARGET_CLASS'          => true,
			'TARGET_FUNCTION'       => true,
			'TARGET_METHOD'         => true,
			'TARGET_PROPERTY'       => false,
			'TARGET_CLASS_CONSTANT' => false,
			'TARGET_PARAMETER'      => false,
			'IS_REPEATABLE'         => true
		];

		foreach ($flags as $flag => $bool) {
			$name = 'Attribute::' . $flag;
			$mask = constant($name);

			$message = $name . ' must not be set.';
			if ($bool) {
				$message = $name . ' must be set.';
			}

			$this->assertEquals($bool, ($attribute->flags & $mask) == true, $message);
		}
	}

	/**
	 * Verifies the $hook->getName() function.
	 *
	 * @dataProvider hookAttributeDataProvider
	 *
	 * @param string  $name     Name of the hook.
	 * @param integer $priority Priority of the hook.
	 *
	 * @return void
	 */
	public function testGetName(string $name, ?int $priority): void {
		$classname = 'ShineUnited\\WordPress\\Hooks\\' . $this->getHookType(true);

		$hook = new $classname($name, null);

		$this->assertSame($name, $hook->getName());
	}

	/**
	 * Verifies the $hook->getPriority() function.
	 *
	 * @dataProvider hookAttributeDataProvider
	 *
	 * @param string  $name     Name of the hook.
	 * @param integer $priority Priority of the hook.
	 *
	 * @return void
	 */
	public function testGetPriority(string $name, ?int $priority): void {
		$classname = 'ShineUnited\\WordPress\\Hooks\\' . $this->getHookType(true);

		$hook = new $classname('test-hook', $priority);

		$this->assertSame($priority ?? HookManager::DEFAULT_PRIORITY, $hook->getPriority());
	}

	/**
	 * @return void
	 */
	public function testRegister(): void {
		$classname = 'ShineUnited\\WordPress\\Hooks\\' . $this->getHookType(true);
		$addFunction = 'add_' . $this->getHookType(false);

		$hook = new $classname('test-hook', null);
		$callback = function ($one, $two, $three, $four, $five): void {
			// ...
		};

		$mock = $this->mockGlobalFunction($addFunction);

		$mock
			->expects($this->once())
			->method($addFunction)
			->with(
				$this->identicalTo('test-hook'),
				$this->identicalTo($callback),
				$this->identicalTo(HookManager::DEFAULT_PRIORITY),
				$this->identicalTo(5)
			)
			->willReturn(true)
		;

		$hook->register($callback);
	}

	/**
	 * @return Generator Test dataProvider
	 */
	public function hookAttributeDataProvider(): Generator {
		yield [
			'hook-priority-null',
			null
		];

		yield [
			'hook-priority-1',
			1
		];

		yield [
			'hook-priority-5',
			5
		];

		yield [
			'hook-priority-25',
			25
		];
	}
}
