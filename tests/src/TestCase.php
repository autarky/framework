<?php
namespace Autarky\Tests;

use PHPUnit_Framework_TestCase;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;
use Autarky\Errors\StubErrorHandler;
use Autarky\Kernel\Application;
use Autarky\Kernel\Errors\SymfonyErrorHandler;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
	protected function makeApplication($providers = array(), $env = 'testing')
	{
		$app = new Application($env, (array) $providers);
		$app->setContainer(new Container);
		$app->setConfig(new ArrayStore);
		$app->setErrorHandler(new StubErrorHandler);
		return $app;
	}
}
