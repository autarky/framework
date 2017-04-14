<?php

use Mockery as m;

use Psr\Log\LogLevel;
use Autarky\Logging\ChannelManager;

class ChannelManagerTest extends PHPUnit\Framework\TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeManager()
	{
		return new ChannelManager;
	}

	public function getMockLogger()
	{
		return m::mock('Psr\Log\LoggerInterface');
	}

	/** @test */
	public function logsToDefaultChannel()
	{
		$log = $this->makeManager();
		$log->setChannel('default', $mock = $this->getMockLogger());
		$mock->shouldReceive('log')->with(LogLevel::DEBUG, 'message', [])->once();
		$log->debug('message');
	}

	/** @test */
	public function logsToSpecificChannel()
	{
		$log = $this->makeManager();
		$log->setChannel('default', $mock = $this->getMockLogger());
		$mock->shouldReceive('log')->never();
		$mock->shouldReceive('debug')->never();
		$log->setChannel('specific', $mock = $this->getMockLogger());
		$mock->shouldReceive('debug')->with('message')->once();
		$log->getChannel('specific')->debug('message');
	}

	/** @test */
	public function deferredChannels()
	{
		$log = $this->makeManager();
		$log->setDeferredChannel('default', function() {
			$mock = $this->getMockLogger();
			$mock->shouldReceive('log')->with(LogLevel::DEBUG, 'message', [])->once();
			return $mock;
		});
		$log->debug('message');
	}
}
