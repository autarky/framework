<?php
namespace Autarky\Tests;

use Autarky\Application;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;
use Autarky\Errors\StubErrorHandler;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
	protected function makeApplication($providers = [], $env = 'testing')
	{
		$app = new Application($env, (array) $providers);
		$app->setContainer($container = new Container);
		$app->setConfig($config = new ArrayStore);
		$container->instance('Autarky\Config\ConfigInterface', $config);
		$app->setErrorHandler(new StubErrorHandler);
		return $app;
	}
}
