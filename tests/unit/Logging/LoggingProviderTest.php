<?php

use Mockery as m;
use Autarky\Tests\TestCase;

class LoggingProviderTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Logging\LoggingProvider');
		$app->boot();
		$object = $app->resolve('Autarky\Logging\ChannelManager');

		$this->assertInstanceOf('Autarky\Logging\ChannelManager', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Autarky\Logging\ChannelManager'));
	}

	/** @test */
	public function canResolveLogger()
	{
		$app = $this->makeApplication('Autarky\Logging\LoggingProvider');
		$app->getConfig()->set('path.logs', TESTS_RSC_DIR.'/logs');
		$app->config('Autarky\Logging\DefaultLogConfigurator');
		$app->boot();
		$logger = $app->resolve('Psr\Log\LoggerInterface');

		$this->assertInstanceOf('Psr\Log\LoggerInterface', $logger);
		$this->assertInstanceOf('Monolog\Logger', $logger);
	}

	/** @test */
	public function canResolveCustomChannelAsDependency()
	{
		$app = $this->makeApplication('Autarky\Logging\LoggingProvider');
		$container = $app->getContainer();
		$app->boot();
		$mock = m::mock('Autarky\Logging\ChannelManager');
		$mockLogger = m::mock('Psr\Log\LoggerInterface');
		$mock->shouldReceive('getChannel')->with('custom')->times(3)
			->andReturn($mockLogger);
		$container->instance('Autarky\Logging\ChannelManager', $mock);

		$o = $container->resolve(__NAMESPACE__.'\LoggerDependentStub', [
			'Psr\Log\LoggerInterface' => $container->getFactory('Psr\Log\LoggerInterface', ['$channel' => 'custom']),
		]);
		$this->assertSame($mockLogger, $o->logger);

		$o = $container->resolve(__NAMESPACE__.'\LoggerDependentStub', [
			'$logger' => $container->getFactory('Psr\Log\LoggerInterface', ['$channel' => 'custom']),
		]);
		$this->assertSame($mockLogger, $o->logger);

		$container->params(__NAMESPACE__.'\LoggerDependentStub', [
			'$logger' => $container->getFactory('Psr\Log\LoggerInterface', ['$channel' => 'custom']),
		]);
		$o = $container->resolve(__NAMESPACE__.'\LoggerDependentStub');
		$this->assertSame($mockLogger, $o->logger);
	}
}

class LoggerDependentStub
{
	public $logger;
	public function __construct(Psr\Log\LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
