<?php
namespace Autarky\Errors;

use Autarky\Kernel\ServiceProvider;

class ErrorHandlerProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->setErrorHandler(new SymfonyErrorHandler);
	}
}
