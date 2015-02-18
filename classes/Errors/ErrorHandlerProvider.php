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

use Autarky\Provider;

/**
 * Provides error handling.
 */
class ErrorHandlerProvider extends Provider
{
	/**
	 * @var boolean  Whether or not the error handler should register itself as
	 * handler of native PHP errors as well as application exceptions.
	 */
	protected $register;

	/**
	 * @var ErrorHandlerManager
	 */
	protected $manager;

	/**
	 * @param boolean $register Whether or not the error handler should register
	 * itself as handler of native PHP errors as well as application exceptions.
	 */
	public function __construct($register = true)
	{
		$this->register = (bool) $register;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();
		$debug = $this->app->getConfig()->get('app.debug');

		$this->manager = new ErrorHandlerManager(
			new HandlerResolver($dic)
		);

		$this->manager->setDefaultHandler(new DefaultErrorHandler($debug));

		$this->app->setErrorHandler($this->manager);

		if ($this->register) {
			$this->manager->register();
		}

		$dic->instance('Autarky\Errors\ErrorHandlerManager', $this->manager);
		$dic->alias('Autarky\Errors\ErrorHandlerManager', 'Autarky\Errors\ErrorHandlerManagerInterface');

		$this->app->config([$this, 'configureErrorHandler']);
	}

	public function configureErrorHandler()
	{
		$errorHandlers = $this->app->getConfig()->get('app.error_handlers', []);

		foreach ($errorHandlers as $errorHandler) {
			$this->manager->appendHandler($errorHandler);
		}
	}
}
