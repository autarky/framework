<?php

class PathResolverTest extends PHPUnit\Framework\TestCase
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

	/** @test */
	public function locatesFiles()
	{
		$basenames = [
			TESTS_RSC_DIR.'/files/root/filename',
			TESTS_RSC_DIR.'/files/other_root/filename',
		];
		$extensions = ['.foo', '.bar'];
		$paths = $this->makeResolver()->locate($basenames, $extensions);
		$expected = [
			TESTS_RSC_DIR.'/files/root/filename.foo',
			TESTS_RSC_DIR.'/files/other_root/filename.bar',
		];
		$this->assertEquals($expected, $paths);
	}
}
