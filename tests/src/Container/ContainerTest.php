<?php
namespace Autarky\Tests\Container;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Autarky\Container\IlluminateContainer;
use Autarky\Container\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
		m::close();
	}

	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function shareClosure()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$c->share('foo');
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function alias()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new LowerClass; });
		$c->share('foo');
		$c->alias('foo', __NAMESPACE__.'\\LowerClass');
		$this->assertTrue($c->isBound(__NAMESPACE__.'\\LowerClass'));
		$this->assertSame($c->resolve('foo'), $c->resolve(__NAMESPACE__.'\\UpperClass')->cl);
	}

	/** @test */
	public function instance()
	{
		$c = $this->makeContainer();
		$c->instance('foo', $o = new \StdClass);
		$this->assertSame($o, $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareWithoutFactory()
	{
		$c = $this->makeContainer();
		$c->share('StdClass');
		$this->assertInstanceOf('StdClass', $c->resolve('StdClass'));
		$this->assertSame($c->resolve('StdClass'), $c->resolve('StdClass'));
	}

	/** @test */
	public function factoriesAreNotSharedByDefault()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$this->assertNotSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function externalClassFactory()
	{
		$c = $this->makeContainer();
		$c->define('foo.factory', function() { return new StubFactory; });
		$c->define('foo', ['foo.factory', 'makeFoo']);
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function resolveDependencies()
	{
		$c = $this->makeContainer();
		$o1 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertInstanceOf(__NAMESPACE__ .'\\UpperClass', $o1);
		$this->assertInstanceOf(__NAMESPACE__ .'\\LowerClass', $o1->cl);
		$o2 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertNotSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function resolveSharedDependencies()
	{
		$c = $this->makeContainer();
		$c->share(__NAMESPACE__.'\\LowerClass');
		$o1 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertInstanceOf(__NAMESPACE__ .'\\UpperClass', $o1);
		$this->assertInstanceOf(__NAMESPACE__ .'\\LowerClass', $o1->cl);
		$o2 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function resolveOptionalDependencyIsNullWhenNotConfigured()
	{
		$c = $this->makeContainer();
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertNull($obj->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithAlias()
	{
		$c = $this->makeContainer();
		$c->alias(__NAMESPACE__.'\\OptionalClass', __NAMESPACE__.'\\OptionalInterface');
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $obj->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParams()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\\OptionalDependencyClass', [
			__NAMESPACE__.'\\OptionalInterface' => __NAMESPACE__.'\\OptionalClass',
		]);
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $obj->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParamsUsingVariableNames()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\\OptionalDependencyClass', [
			'$opt' => __NAMESPACE__.'\\OptionalClass',
		]);
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $obj->opt);
	}

	/** @test */
	public function containerAware()
	{
		$c = $this->makeContainer();
		$o = $c->resolve(__NAMESPACE__.'\CA');
		$this->assertSame($c, $o->container);
	}

	/** @test */
	public function boundReturnsTrueWhenBound()
	{
		$c = $this->makeContainer();

		$this->assertEquals(false, $c->isBound('StdClass'));
		$c->share('StdClass');
		$this->assertEquals(true, $c->isBound('StdClass'));

		$this->assertEquals(false, $c->isBound('foo'));
		$c->define('foo', function() { return 'foo'; });
		$this->assertEquals(true, $c->isBound('foo'));

		$this->assertEquals(false, $c->isBound('bar'));
		$c->instance('bar', 'bar');
		$this->assertEquals(true, $c->isBound('bar'));
	}

	/** @test */
	public function nonExistingClassesThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('ReflectionException');
		$c->resolve('thisclassdoesnotexist');
	}

	/** @test */
	public function nonInstantiableClassNameThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\NotInstantiableException');
		$c->resolve('Iterator');
	}

	/** @test */
	public function unresolvableArgumentThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\UnresolvableArgumentException');
		$c->resolve(__NAMESPACE__.'\\UnresolvableStub');
	}

	/** @test */
	public function canResolveWithDependencyDefaultValue()
	{
		$c = $this->makeContainer();
		$obj = $c->resolve(__NAMESPACE__.'\\DefaultValueStub');
		$this->assertEquals('foo', $obj->value);
	}

	/** @test */
	public function resolvingCallbacksAreCalled()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$c->resolving('foo', function($o, $c) { $o->bar = 'baz'; });
		$this->assertEquals('baz', $c->resolve('foo')->bar);
	}

	/** @test */
	public function resolvingAnyCallbacksAreCalled()
	{
		$c = $this->makeContainer();
		$c->resolvingAny(function($o, $c) { $o->bar = 'baz'; });
		$this->assertEquals('baz', $c->resolve('StdClass')->bar);
	}

	/** @test */
	public function invokeInvokes()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke(function() { return 42; });
		$this->assertEquals(42, $retval);
	}

	/** @test */
	public function invokeCanInvokeStaticMethods()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke([__NAMESPACE__.'\\StaticStub', 'f'], ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeResolvesDependencies()
	{
		$c = $this->makeContainer();
		$callback = function(LowerClass $lc) { return $lc; };
		$retval = $c->invoke($callback);
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $retval);
	}

	/** @test */
	public function invokeCanBePassedParams()
	{
		$c = $this->makeContainer();
		$callback = function($param) { return $param; };
		$retval = $c->invoke($callback, ['$param' => 42]);
		$this->assertEquals(42, $retval);
	}

	/** @test */
	public function invokeCanBePassedObjectParam()
	{
		$c = $this->makeContainer();
		$lc = new LowerClass;
		$callback = function(LowerClass $lc) { return $lc; };
		$retval = $c->invoke($callback, ['$lc' => $lc]);
		$this->assertSame($lc, $retval);
	}
}

class LowerClass {}
class UpperClass {
	public function __construct(LowerClass $cl) {
		$this->cl = $cl;
	}
}
interface OptionalInterface {}
class OptionalClass implements OptionalInterface {}
class OptionalDependencyClass {
	public function __construct(LowerClass $lc, OptionalInterface $opt = null) {
		$this->lc = $lc; $this->opt = $opt;
	}
}
class CA implements \Autarky\Container\ContainerAwareInterface
{
	public $container;
	public function setContainer(\Autarky\Container\ContainerInterface $container)
	{
		$this->container = $container;
	}
}
class UnresolvableStub {
	public function __construct($value) {}
}
class DefaultValueStub {
	public $value;
	public function __construct($value = 'foo')
	{
		$this->value = $value;
	}
}
class StubFactory {
	public function makeFoo() {
		return 'foo';
	}
}
class StaticStub {
	public static function f($foo) {
		return $foo.'bar';
	}
}
