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

		$ctrl = ['StubControllerWithDependency', 'respond'];
		$app->route('GET', '/', $ctrl, 'test.route', [
			'params' => ['$parameter' => 'test: '],
			'constructor_params' => ['StubInterface' => 'StubDependency']
		]);

		$request = $this->makeRequest();
		$response = $app->run($request, false);
		$this->assertEquals('test: StubDependency', $response->getContent());
	}

	/** @test */
	public function response_transformation()
	{
		$app = new Autarky\Application('test', [
		    new Autarky\Container\ContainerProvider,
		    new Autarky\Routing\RoutingProvider,
		]);
		$app->boot();

		$app->route('GET', '/string', function() {
			return 'Hello world!';
		});
		$app->route('GET', '/array', function() {
			return ['msg' => 'Hello world!'];
		});

		$response = $app->run($this->makeRequest('/string'), false);
		$this->assertContains('text/html', $response->headers->get('content-type'));
		$response = $app->run($this->makeRequest('/array'), false);
		$this->assertContains('application/json', $response->headers->get('content-type'));
		$this->assertEquals('{"msg":"Hello world!"}', $response->getContent());
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
