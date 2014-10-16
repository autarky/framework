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

/**
 * Provides error handling.
 */
class ErrorHandlerProvider extends ServiceProvider
{
	/**
	 * @var boolean Whether or not the error handler should register itself as handler of native PHP errors as well as application exceptions.
	 */
	protected $register;

	/**
	 * @param boolean $register Whether or not the error handler should register itself as handler of native PHP errors as well as application exceptions.
	 */
	public function __construct($register = true)
	{
		$this->register = (bool) $register;
	}

	public function register()
	{
		$dic = $this->app->getContainer();
		$debug = $this->app->getConfig()->get('app.debug');

		$manager = new ErrorHandlerManager(
			new HandlerResolver($dic)
		);

		$manager->setDefaultHandler(new DefaultErrorHandler($debug));

		$this->app->setErrorHandler($manager);

		if ($this->register) {
			$manager->register();
		}

		$dic->instance('Autarky\Errors\ErrorHandlerManager', $manager);
		$dic->alias('Autarky\Errors\ErrorHandlerManager', 'Autarky\Errors\ErrorHandlerManagerInterface');
	}
}
