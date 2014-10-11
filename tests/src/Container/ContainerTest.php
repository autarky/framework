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
		$c->define('foo', [__NAMESPACE__.'\\StubFactory', 'makeFoo']);
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function aliasedInterfaceFactory()
	{
		$c = $this->makeContainer();
		$c->define('foo', [__NAMESPACE__.'\\StubFactoryInterface', 'makeFoo']);
		$c->alias(__NAMESPACE__.'\\StubFactory', __NAMESPACE__.'\\StubFactoryInterface');
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
		$o = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $o->lc);
		$this->assertNull($o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithAlias()
	{
		$c = $this->makeContainer();
		$c->alias(__NAMESPACE__.'\\OptionalClass', __NAMESPACE__.'\\OptionalInterface');
		$o = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $o->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParams()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\\OptionalDependencyClass', [
			__NAMESPACE__.'\\OptionalInterface' => __NAMESPACE__.'\\OptionalClass',
		]);
		$o = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $o->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParamsUsingVariableNames()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\\OptionalDependencyClass', [
			'$opt' => __NAMESPACE__.'\\OptionalClass',
		]);
		$o = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $o->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $o->opt);
	}

	/** @test */
	public function paramsAreMerged()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\ParamStub', ['$foo' => 'old_foo']);
		$c->params(__NAMESPACE__.'\ParamStub', ['$foo' => 'new_foo', '$bar' => 'bar']);
		$o = $c->resolve(__NAMESPACE__.'\ParamStub');
		$this->assertEquals('new_foo', $o->foo);
		$this->assertEquals('bar', $o->bar);
	}

	/** @test */
	public function containerAware()
	{
		$c = $this->makeContainer();
		$o = $c->resolve(__NAMESPACE__.'\ContainerAware');
		$this->assertSame($c, $o->getContainer());
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
		$this->setExpectedException('Autarky\Container\Exception\NotInstantiableException');
		$c->resolve('Iterator');
	}

	/** @test */
	public function unresolvableArgumentThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException');
		$c->resolve(__NAMESPACE__.'\\UnresolvableStub');
	}

	/** @test */
	public function canResolveWithDependencyDefaultValue()
	{
		$c = $this->makeContainer();
		$o = $c->resolve(__NAMESPACE__.'\\DefaultValueStub');
		$this->assertEquals('foo', $o->value);
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
	public function resolvingCallbacksAreCalledForAliases()
	{
		$c = $this->makeContainer();
		$c->alias(__NAMESPACE__.'\\OptionalClass', __NAMESPACE__.'\\OptionalInterface');
		$called = false;
		$c->resolving(__NAMESPACE__.'\\OptionalInterface', function() use(&$called) {
			$called = true;
		});
		$c->resolve(__NAMESPACE__.'\\OptionalInterface');
		$this->assertEquals(true, $called);
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
	public function canInvokeObjectMethod()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke([new StubFactory, 'makeFoo']);
		$this->assertEquals('foo', $retval);
	}

	/** @test */
	public function invokeWithObjectLooksUpClassParams()
	{
		$c = $this->makeContainer();
		$c->params(__NAMESPACE__.'\StubFactory', ['$suffix' => 'bar']);
		$retval = $c->invoke([new StubFactory, 'makeFoo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeThrowsExceptionOnUnresolvableArgument()
	{
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($foo) of Autarky\Tests\Container\StaticStub::f');
		$c = $this->makeContainer();
		$c->invoke([__NAMESPACE__.'\\StaticStub', 'f']);
	}

	/** @test */
	public function invokeExceptionMessageIsCorrectForClosures()
	{
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($foo) of closure in '.__CLASS__.' on line');
		$c = $this->makeContainer();
		$c->invoke(function($foo){});
	}

	/** @test */
	public function invokeCanInvokeStaticMethods()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke([__NAMESPACE__.'\\StaticStub', 'f'], ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeCanInvokeStaticMethodsWithString()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke(__NAMESPACE__.'\\StaticStub::f', ['$foo' => 'foo']);
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

	/** @test */
	public function internalClassesAreProtected()
	{
		$c = $this->makeContainer();
		$c->internal(__NAMESPACE__.'\\LowerClass');
		$c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->setExpectedException('Autarky\Container\Exception\ResolvingInternalException');
		$c->resolve(__NAMESPACE__ .'\\LowerClass');
	}

	/** @test */
	public function cannotAutoresolveIfAutowiringIsDisabled()
	{
		$c = $this->makeContainer();
		$c->setAutowire(false);
		$this->setExpectedException('Autarky\Container\Exception\ResolvingException');
		$c->resolve(__NAMESPACE__ .'\\UpperClass');
	}

	/** @test */
	public function containerAndContainerInterfaceAreShared()
	{
		$c = $this->makeContainer();
		$this->assertSame($c, $c->resolve('Autarky\Container\Container'));
		$this->assertSame($c, $c->resolve('Autarky\Container\ContainerInterface'));
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
class ContainerAware implements \Autarky\Container\ContainerAwareInterface
{
	use \Autarky\Container\ContainerAwareTrait;
	public function getContainer()
	{
		return $this->container;
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
interface StubFactoryInterface {
	public function makeFoo($suffix = '');
}
class StubFactory implements StubFactoryInterface {
	public function makeFoo($suffix = '') {
		return 'foo' . $suffix;
	}
}
class StaticStub {
	public static function f($foo) {
		return $foo.'bar';
	}
}
class ParamStub {
	public function __construct($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}
