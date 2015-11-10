<?php

class AutarkyFunctionalTest extends PHPUnit_Framework_TestCase
{
	private function makeRequest($url = '/')
	{
		return Symfony\Component\HttpFoundation\Request::create($url);
	}

	/** @test */
	public function simple_route()
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

	/** @test */
	public function controller_route()
	{
		$app = new Autarky\Application('test', [
		    new Autarky\Container\ContainerProvider,
		    new Autarky\Routing\RoutingProvider,
		]);
		$app->boot();

		$app->route('GET', '/', ['StubControllerWithDependency', 'respond'],
			'test.route', ['container_params' => [
				'StubInterface' => 'StubDependency',
				'$parameter' => 'test: ',
			]]);

		$request = $this->makeRequest();
		$response = $app->run($request, false);
		$this->assertEquals('test: StubDependency', $response->getContent());
	}
}

class StubControllerWithDependency {
	public function __construct(StubInterface $dependency) {
		$this->dependency = $dependency;
	}
	public function respond($parameter) {
		return $parameter . get_class($this->dependency);
	}
}
interface StubInterface {}
class StubDependency implements StubInterface {}
