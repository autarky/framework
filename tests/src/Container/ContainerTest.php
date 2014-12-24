<?php

use Mockery as m;

use Autarky\Container\ContainerInterface;
use Autarky\Container\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		m::close();
	}

	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function defineSharedServiceWithClosure()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$c->share('foo');
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function defineSharedServiceAndAliasIt()
	{
		$c = $this->makeContainer();
		$c->define('LowerClass', function() { return new LowerClass; });
		$c->share('LowerClass');
		$c->alias('LowerClass', 'foo');
		$this->assertTrue($c->isBound('LowerClass'));
		$this->assertTrue($c->isBound('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('UpperClass')->cl);
	}

	/** @test */
	public function putInstanceOntoContainer()
	{
		$c = $this->makeContainer();
		$c->instance('foo', $o = new \StdClass);
		$this->assertSame($o, $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareWithoutDefiningFirst()
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
	public function defineWithResolvableFactoryArray()
	{
		$c = $this->makeContainer();
		$c->define('foo', ['StubFactory', 'makeFoo']);
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function defineWithResolvableFactoryArrayWithAliasedInterface()
	{
		$c = $this->makeContainer();
		$c->define('foo', ['StubFactoryInterface', 'makeFoo']);
		$c->alias('StubFactory', 'StubFactoryInterface');
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function automaticallyResolvesDependencies()
	{
		$c = $this->makeContainer();
		$o1 = $c->resolve('UpperClass');
		$this->assertInstanceOf('UpperClass', $o1);
		$this->assertInstanceOf('LowerClass', $o1->cl);
		$o2 = $c->resolve('UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertNotSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function automaticallyResolvesDependenciesIncludingSharedInstances()
	{
		$c = $this->makeContainer();
		$c->share('LowerClass');
		$o1 = $c->resolve('UpperClass');
		$this->assertInstanceOf('UpperClass', $o1);
		$this->assertInstanceOf('LowerClass', $o1->cl);
		$o2 = $c->resolve('UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function resolveOptionalDependencyIsNullWhenNotConfigured()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertNull($o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithAlias()
	{
		$c = $this->makeContainer();
		$c->alias('OptionalClass', 'OptionalInterface');
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParams()
	{
		$c = $this->makeContainer();
		$c->params('OptionalDependencyClass', [
			'OptionalInterface' => 'OptionalClass',
		]);
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParamsUsingVariableNames()
	{
		$c = $this->makeContainer();
		$c->params('OptionalDependencyClass', [
			'$opt' => 'OptionalClass',
		]);
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function multipleParamCallsAddUp()
	{
		$c = $this->makeContainer();
		$c->params('ParamStub', ['$foo' => 'old_foo']);
		$c->params('ParamStub', ['$foo' => 'new_foo', '$bar' => 'bar']);
		$o = $c->resolve('ParamStub');
		$this->assertEquals('new_foo', $o->foo);
		$this->assertEquals('bar', $o->bar);
	}

	/** @test */
	public function setContainerIsCalledOnContainerAwareInterfaceClasses()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('ContainerAware');
		$this->assertSame($c, $o->getContainer());
	}

	/** @test */
	public function isBoundReturnsTrueWhenBound()
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
		$c->resolve('UnresolvableStub');
	}

	/** @test */
	public function resolveWithNonClassDependencyThatHasDefaultValue()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('DefaultValueStub');
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
		$c->alias('OptionalClass', 'OptionalInterface');
		$called = false;
		$c->resolving('OptionalInterface', function() use(&$called) {
			$called = true;
		});
		$c->resolve('OptionalInterface');
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
	public function invokeCanInvokeClosure()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke(function() { return 42; });
		$this->assertEquals(42, $retval);
	}

	/** @test */
	public function invokeCanInvokeObjectMethod()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke([new StubFactory, 'makeFoo']);
		$this->assertEquals('foo', $retval);
	}

	/** @test */
	public function invokeWithObjectLooksUpClassParams()
	{
		$c = $this->makeContainer();
		$c->params('StubFactory', ['$suffix' => 'bar']);
		$retval = $c->invoke([new StubFactory, 'makeFoo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeThrowsExceptionOnUnresolvableArgument()
	{
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($foo) of StaticStub::f');
		$c = $this->makeContainer();
		$c->invoke(['StaticStub', 'f']);
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
		$retval = $c->invoke(['StaticStub', 'f'], ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeCanInvokeStaticMethodsWithString()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke('StaticStub::f', ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeResolvesDependencies()
	{
		$c = $this->makeContainer();
		$callback = function(LowerClass $lc) { return $lc; };
		$retval = $c->invoke($callback);
		$this->assertInstanceOf('LowerClass', $retval);
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
		$c->internal('LowerClass');
		$c->resolve('UpperClass');
		$this->setExpectedException('Autarky\Container\Exception\ResolvingInternalException');
		$c->resolve('LowerClass');
	}

	/** @test */
	public function cannotAutoresolveIfAutowiringIsDisabled()
	{
		$c = $this->makeContainer();
		$c->setAutowire(false);
		$this->setExpectedException('Autarky\Container\Exception\ResolvingException');
		$c->resolve('UpperClass');
	}

	/** @test */
	public function containerAndContainerInterfaceAreShared()
	{
		$c = $this->makeContainer();
		$this->assertSame($c, $c->resolve('Autarky\Container\Container'));
		$this->assertSame($c, $c->resolve('Autarky\Container\ContainerInterface'));
	}

	/** @test */
	public function canDefineDynamicFactoryParam()
	{
		$c = $this->makeContainer();
		$c->define('var.service', function(ContainerInterface $container, $variable) {
			return strtoupper($variable);
		});
		$c->params('ParamStub', [
			'$foo' => $c->getFactory('var.service', ['$variable' => 'foo']),
			'$bar' => $c->getFactory('var.service', ['$variable' => 'bar']),
		]);
		$obj = $c->resolve('ParamStub');
		$this->assertEquals('FOO', $obj->foo);
		$this->assertEquals('BAR', $obj->bar);
	}

	/** @test */
	public function canDefineNonFactoryArrayAsParameter()
	{
		$c = $this->makeContainer();
		$c->params('ParamStub', [
			'$foo' => ['foo', 'bar', 'baz'],
			'$bar' => ['baz', 'bar', 'foo'],
		]);
		$obj = $c->resolve('ParamStub');
		$this->assertEquals(['foo', 'bar', 'baz'], $obj->foo);
		$this->assertEquals(['baz', 'bar', 'foo'], $obj->bar);
	}

	/** @test */
	public function canDefineDynamicFactoryParamWithClasses()
	{
		$c = $this->makeContainer();
		$c->define('ValueLowerClass', function(ContainerInterface $container, $value) {
			return new ValueLowerClass($value);
		});
		$c->params('UpperClass', [
			'LowerClass' => $c->getFactory('ValueLowerClass', ['$value' => 'foobar']),
		]);
		$obj = $c->resolve('UpperClass');
		$this->assertInstanceOf('ValueLowerClass', $obj->cl);
		$this->assertEquals('foobar', $obj->cl->value);
	}
}

class LowerClass {}
class ValueLowerClass extends LowerClass {
	public function __construct($value) {
		$this->value = $value;
	}
}
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
