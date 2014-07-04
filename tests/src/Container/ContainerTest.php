<?php
namespace Autarky\Tests\Container;

use PHPUnit_Framework_TestCase;

use Autarky\Container\IlluminateContainer;
use Autarky\Container\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function shareClosure()
	{
		$c = $this->makeContainer();
		$c->share('foo', function() { return new \StdClass; });
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareObject()
	{
		$c = $this->makeContainer();
		$c->share('foo', $o = new \StdClass);
		$this->assertSame($o, $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareString()
	{
		$c = $this->makeContainer();
		$c->share('foo', 'StdClass');
		$this->assertInstanceOf('StdClass', $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareOnlyAbstract()
	{
		$c = $this->makeContainer();
		$c->share('StdClass');
		$this->assertInstanceOf('StdClass', $c->resolve('StdClass'));
		$this->assertSame($c->resolve('StdClass'), $c->resolve('StdClass'));
	}

	/** @test */
	public function bindClosure()
	{
		$c = $this->makeContainer();
		$c->bind('foo', function() { return new \StdClass; });
		$this->assertNotSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function bindObject()
	{
		$c = $this->makeContainer();
		$c->share('foo', $o = new \StdClass);
		$this->assertSame($o, $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function bindString()
	{
		$c = $this->makeContainer();
		$c->bind('foo', 'StdClass');
		$this->assertInstanceOf('StdClass', $c->resolve('foo'));
		$this->assertNotSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function resolveDependencies()
	{
		$c = $this->makeContainer();
		$o = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertInstanceOf(__NAMESPACE__ .'\\UpperClass', $o);
		$this->assertInstanceOf(__NAMESPACE__ .'\\LowerClass', $o->cl);
		$o2 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertNotSame($o, $o2);
		$this->assertNotSame($o->cl, $o2->cl);
	}

	/** @test */
	public function resolveSharedDependencies()
	{
		$c = $this->makeContainer();
		$c->share(__NAMESPACE__.'\\LowerClass');
		$o = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertInstanceOf(__NAMESPACE__ .'\\UpperClass', $o);
		$this->assertInstanceOf(__NAMESPACE__ .'\\LowerClass', $o->cl);
		$o2 = $c->resolve(__NAMESPACE__ .'\\UpperClass');
		$this->assertNotSame($o, $o2);
		$this->assertSame($o->cl, $o2->cl);
	}

	/** @test */
	public function resolveOptionalDependencies()
	{
		$c = $this->makeContainer();
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertNull($obj->opt);

		$c->bind(__NAMESPACE__.'\\OptionalInterface', __NAMESPACE__.'\\OptionalClass');
		$obj = $c->resolve(__NAMESPACE__.'\\OptionalDependencyClass');
		$this->assertInstanceOf(__NAMESPACE__.'\\LowerClass', $obj->lc);
		$this->assertInstanceOf(__NAMESPACE__.'\\OptionalClass', $obj->opt);

	}

	/** @test */
	public function alias()
	{
		$c = $this->makeContainer();
		$c->share('foo', function() { return new LowerClass; });
		$c->alias(__NAMESPACE__.'\\LowerClass', 'foo');
		$this->assertTrue($c->isBound(__NAMESPACE__.'\\LowerClass'));
		$this->assertSame($c->resolve('foo'), $c->resolve(__NAMESPACE__.'\\UpperClass')->cl);
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
		$this->assertEquals(false, $c->isBound('foo'));
		$this->assertEquals(false, $c->isBound('bar'));
		$c->bind('StdClass');
		$c->bind('foo', function() { return 'foo'; });
		$c->share('bar', function() { return 'foo'; });
		$this->assertEquals(true, $c->isBound('StdClass'));
		$this->assertEquals(true, $c->isBound('foo'));
		$this->assertEquals(true, $c->isBound('bar'));
	}

	/** @test */
	public function awareInterfacesAreBound()
	{
		$c = $this->makeContainer();
		$c->bind('foo', function() { return new \StdClass; });
		$c->aware(__NAMESPACE__.'\StubAwareInterface', 'setStub', 'foo');
		$o = $c->resolve(__NAMESPACE__.'\AwareStub');
		$this->assertInstanceOf('StdClass', $o->stub);
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
	public function unresolvableDependencyThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\UnresolvableDependencyException');
		$c->resolve(__NAMESPACE__.'\\UnresolvableStub');
	}

	/** @test */
	public function canResolveWithDependencyDefaultValue()
	{
		$c = $this->makeContainer();
		$obj = $c->resolve(__NAMESPACE__.'\\DefaultValueStub');
		$this->assertEquals('foo', $obj->value);
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
interface StubAwareInterface
{
	public function setStub($stub);
}
class AwareStub implements StubAwareInterface
{
	public $stub;
	public function setStub($stub)
	{
		$this->stub = $stub;
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