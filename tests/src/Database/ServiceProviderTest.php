<?php
namespace Autarky\Tests\Database;

use Autarky\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Database\DatabaseServiceProvider');
		$app->boot();
		$object = $app->resolve('Autarky\Database\MultiPdoContainer');
		$this->assertInstanceOf('Autarky\Database\MultiPdoContainer', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Autarky\Database\MultiPdoContainer'));
	}
}
