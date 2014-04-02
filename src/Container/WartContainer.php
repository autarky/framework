<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container;

use Wart;

class WartContainer implements ContainerInterface
{
	public function __construct(Wart $wart = null)
	{
		$this->wart = $wart ?: new Wart;
	}

	public function bind($abstract, $concrete = null)
	{
		if ($concrete === null) {
			$concrete = $abstract;
		}

		if (is_string($concrete)) {
			$concrete = function() use($concrete) {
				return $wart->build($concrete);
			};
		}

		return $this->wart->factory($abstract, $concrete);
	}

	public function share($abstract, $concrete = null)
	{
		if ($concrete === null) {
			$concrete = $abstract;
		}

		if (is_string($concrete)) {
			$concrete = function() use($concrete) {
				return $wart->build($concrete);
			};
		}

		return $this->wart->offsetSet($abstract, $concrete);
	}

	public function resolve($abstract)
	{
		return $this->wart->offsetGet($abstract);
	}
}
