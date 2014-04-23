<?php
namespace Autarky\Tests\Container;

use PHPUnit_Framework_TestCase;

use Autarky\Container\IlluminateContainer;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new IlluminateContainer;
	}

	/** @test */
	public function shareClosure()
	{
		$c = $this->makeContainer();
		$c->share('foo', function() {
			return new \StdClass;
		});
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
	public function alias()
	{
		$c = $this->makeContainer();
		$c->share('foo', function() { return new LowerClass; });
		$c->alias(__NAMESPACE__.'\\LowerClass', 'foo');
		$this->assertSame($c->resolve('foo'), $c->resolve(__NAMESPACE__.'\\UpperClass')->cl);
	}
}

class LowerClass {}
class UpperClass {
	public function __construct(LowerClass $cl) {
		$this->cl = $cl;
	}
}
