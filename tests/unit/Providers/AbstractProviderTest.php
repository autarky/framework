<?php

use Mockery as m;
use Autarky\Tests\TestCase;

class AbstractProviderTest extends TestCase
{
	/** @test */
	public function serviceProviderIsCalledOnBoot()
	{
		$app = $this->makeApplication([__NAMESPACE__.'\\StubServiceProvider']);
		$this->assertFalse(StubServiceProvider::$called);
		$app->boot();
		$this->assertTrue(StubServiceProvider::$called);
	}
}

class StubServiceProvider extends \Autarky\Providers\AbstractProvider
{
	public static $called = false;
	public function register()
	{
		static::$called = true;
	}
}
