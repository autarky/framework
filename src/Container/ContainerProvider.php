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

use Autarky\Kernel\ServiceProvider;

class ContainerProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->setContainer(new Container);
	}
}
