<?php

class AutarkyFunctionalTest extends PHPUnit_Framework_TestCase
{
	private function makeRequest($url = '/')
	{
		return Symfony\Component\HttpFoundation\Request::create($url);
	}

	/** @test */
	public function can_run()
	{
		$app = new Autarky\Application('test', [
		    new Autarky\Container\ContainerProvider,
		    new Autarky\Routing\RoutingProvider,
		]);
		$app->boot();

		$app->route('GET', '/', function() {
		    return 'Hello world!';
		});

		$request = $this->makeRequest();
		$response = $app->run($request, false);
		$this->assertEquals('Hello world!', $response->getContent());
	}
}
