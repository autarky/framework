<?php

class PathResolverTest extends PHPUnit_Framework_TestCase
{
	private function makeResolver()
	{
		return new Autarky\Files\PathResolver('/root');
	}

	/** @test */
	public function resolvesSingleFile()
	{
		$resolver = $this->makeResolver();
		$this->assertEquals(['/root/foo'], $resolver->resolve('foo'));
	}

	/** @test */
	public function resolvesMountedDirectory()
	{
		$resolver = $this->makeResolver();
		$resolver->mount('foo', '/other_root');
		$expected = ['/other_root/bar', '/root/foo/bar'];
		$this->assertEquals($expected, $resolver->resolve('foo/bar'));
	}
}
