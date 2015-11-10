<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

use Autarky\Container\CallableInvokerInterface;

/**
 * {@inheritdoc}
 *
 * @internal
 */
class Invoker implements InvokerInterface
{
	/**
	 * @var CallableInvokerInterface
	 */
	protected $container;

	/**
	 * @param CallableInvokerInterface $container
	 */
	public function __construct(CallableInvokerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function invoke($callable, array $params = [], $constructorArgs = [])
	{
		if (is_string($callable) && strpos($callable, '::') !== false) {
			$callable = explode('::', $callable, 2);
		}

		if (is_array($callable) && is_string($callable[0]) && $constructorArgs) {
			$callable[0] = $this->container->resolve($callable[0], $constructorArgs);
		}

		return $this->container->invoke($callable, $params);
	}
}
