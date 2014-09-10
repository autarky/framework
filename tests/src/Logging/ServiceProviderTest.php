<?php
namespace Autarky\Tests\Logging;

use Autarky\Tests\TestCase;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function getLogDir()
	{
		$dirpath = TESTS_RSC_DIR.'/logs';
		foreach (glob($dirpath.'/*.log') as $file) {
			unlink($file);
		}
		return $dirpath;
	}

	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Logging\LogServiceProvider');
		$app->boot();
		$object = $app->getContainer()->resolve('Monolog\Logger');
		$this->assertInstanceOf('Monolog\Logger', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Monolog\Logger'));
	}

	/** @test */
	public function canResolveInterface()
	{
		$app = $this->makeApplication('Autarky\Logging\LogServiceProvider');
		$app->boot();
		$object = $app->getContainer()->resolve('Psr\Log\LoggerInterface');
		$this->assertInstanceOf('Psr\Log\LoggerInterface', $object);
	}

	/** @test */
	public function writesToLogPath()
	{
		$app = $this->makeApplication('Autarky\Logging\LogServiceProvider');
		$app->getConfig()->set('path.logs', $logdir = $this->getLogDir());
		$app->boot();
		$logger = $app->getContainer()->resolve('Monolog\Logger');
		$logger->debug('foo bar baz');
		$pattern = '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] testing\.DEBUG\: foo bar baz \[\] \[\]\n$/';
		$logfile = file_get_contents($logdir.'/cli.log');
		$this->assertTrue(preg_match($pattern, $logfile) === 1, "Expected regex: $pattern\nActual: $logfile");
	}
}
