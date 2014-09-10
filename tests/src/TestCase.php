<?php
namespace Autarky\Tests;

use PHPUnit_Framework_TestCase;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;
use Autarky\Kernel\Application;
use Autarky\Kernel\Errors\SymfonyErrorHandler;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
	protected function makeApplication($providers = array())
	{
		$errorHandler = $this->makeErrorHandler();
		$app = new Application('testing', new Container, new ArrayStore, $errorHandler);
		if ($providers) {
			$app->getConfig()->set('app.providers', (array) $providers);
		}
		return $app;
	}

	protected function makeErrorHandler()
	{
		$errorHandler = new SymfonyErrorHandler;
		$errorHandler->setRethrow(true);
		return $errorHandler;
	}
}
