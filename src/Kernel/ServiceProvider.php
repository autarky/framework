<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel;

abstract class ServiceProvider
{
	public function __construct($app)
	{
		$this->app = $app;
	}

	abstract public function register();
}
