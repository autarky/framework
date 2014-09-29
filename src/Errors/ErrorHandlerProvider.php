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
		$dic = $this->app->getContainer();
		$debug = $this->app->getConfig()->get('app.debug');

		$manager = new ErrorHandlerManager(
			new HandlerResolver($dic),
			new ApplicationContextCollector($this->app)
		);

		$manager->setDefaultHandler(new DefaultErrorHandler($debug));

		$this->app->setErrorHandler($manager);

		$dic->instance('Autarky\Errors\ErrorHandlerManager', $manager);
		$dic->alias('Autarky\Errors\ErrorHandlerManager', 'Autarky\Errors\ErrorHandlerManagerInterface');
	}
}
