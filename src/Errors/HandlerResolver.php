<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Errors;

use Autarky\Container\ContainerInterface;

class HandlerResolver
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function resolve($handler)
	{
		if (!is_string($handler)) {
			return $handler;
		}

		$handler = $this->container->resolve($handler);

		if (!is_callable($handler) && !$handler instanceof ErrorHandlerInterface) {
			$type = is_object($handler) ? get_class($handler) : gettype($handler);
			throw new \UnexpectedValueException("Resolved error handler is not a valid handler - must be callable or an instance of Autarky\Errors\ErrorHandlerInterface, $type given");
		}

		return $handler;
	}
}
