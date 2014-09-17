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
		$app = new Application('testing', (array) $providers);
		$app->setContainer(new Container);
		$app->setConfig(new ArrayStore);
		return $app;
	}
}
