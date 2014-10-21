<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Proxy;

use Autarky\Container\ContainerInterface;

abstract class AbstractProxy
{
	/**
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * @var ContainerInterface
	 */
	protected static $container;

	public static function setProxyContainer(ContainerInterface $container = null)
	{
		static::$instances = [];
		static::$container = $container;
	}

	public static function setProxyInstance($instance = null)
	{
		static::$instances[static::getProxyContainerKey()] = $instance;
	}

	protected static function resolveProxyInstance()
	{
		return static::$container->resolve(static::getProxyContainerKey());
	}

	public static function __callStatic($method, array $args)
	{
		$key = static::getProxyContainerKey();

		if (!array_key_exists($key, static::$instances)) {
			static::$instances[$key] = static::resolveProxyInstance();
		}

		return call_user_func_array([static::$instances[$key], $method], $args);
	}

	/**
	 * @return string
	 */
	protected static function getProxyContainerKey()
	{
		// abstract static methods are not allowed, so do this instead
		throw new \RuntimeException('Method '.__FUNCTION__.' must be implemented.');
	}
}
