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

use Autarky\Kernel\ServiceProvider;

class ErrorHandlerProvider extends ServiceProvider
{
	public function register()
	{
		$manager = new ErrorHandlerManager();

		$manager->setDefaultHandler(new SymfonyErrorHandler);

		$this->app->setErrorHandler($manager);
	}
}
