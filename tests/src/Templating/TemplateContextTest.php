<?php

use Mockery as m;

class TemplateContextTest extends PHPUnit_Framework_TestCase
{
	public function makeCtx()
	{
		return new \Autarky\Templating\TemplateContext;
	}

	/** @test */
	public function canConcatenateOntoContextVariable()
	{
		$ctx = $this->makeCtx();
		$ctx->foo = 'foo';
		$ctx->foo .= 'bar';

		$this->assertEquals('foobar', $ctx->foo);
	}

	/** @test */
	public function canAddToArrayContextVariable()
	{
		$ctx = $this->makeCtx();
		$ctx->arr = ['foo' => 'bar'];
		$ctx->arr[] = 'baz';

		$this->assertEquals(['foo' => 'bar', 'baz'], $ctx->arr);
	}

	/** @test */
	public function getNonexistantDataThrowsException()
	{
		$ctx = $this->makeCtx();
		$this->setExpectedException('OutOfBoundsException');
		$ctx->foo;
	}
}
