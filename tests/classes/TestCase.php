<?php
namespace Autarky\Tests;

use Autarky\Application;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;
use Autarky\Errors\StubErrorHandler;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
	protected function makeApplication($providers = array(), $env = 'testing')
	{
		$app = new Application($env, (array) $providers);
		$app->setContainer($container = new Container);
		$app->setConfig($config = new ArrayStore);
		$container->instance('Autarky\Config\ConfigInterface', $config);
		$app->setErrorHandler(new StubErrorHandler);
		return $app;
	}
}
