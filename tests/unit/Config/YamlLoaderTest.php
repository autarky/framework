<?php

use Mockery as m;

class YamlLoaderTest extends PHPUnit\Framework\TestCase
{
	public function tearDown()
	{
		m::close();
	}

	private function makeLoader($parser)
	{
		if ($parser === null) {
			$parser = new \Symfony\Component\Yaml\Parser;
		}

		return new \Autarky\Config\Loaders\YamlFileLoader($parser);
	}

	private function makeCacheLoader($parser, $cachePath = null, $stat = true)
	{
		if ($parser === null) {
			$parser = new \Symfony\Component\Yaml\Parser;
		}

		return new \Autarky\Config\Loaders\CachingYamlFileLoader($parser, $cachePath, $stat);
	}

	private function mockParser()
	{
		return m::mock('Symfony\Component\Yaml\Parser');
	}

	private function getYmlPath($file)
	{
		$path = TESTS_RSC_DIR.'/yaml/'.$file;
		$cachePath = $path.'/cache/'.md5($path);
		if (file_exists($cachePath)) unlink($cachePath);
		return $path;
	}

	/** @test */
	public function writesToCachePath()
	{
		$path = $this->getYmlPath('test.yml');
		$loader = $this->makeCacheLoader($parser = $this->mockParser(), TESTS_RSC_DIR.'/yaml/cache');
		// if parser is called more than once, the cached data is not being read
		$parser->shouldReceive('parse')->once()->andReturn($data = ['foo' => 'bar']);
		touch($path, time());
		$this->assertEquals($data, $loader->load($path));
		touch($path, time() - 1);
		$this->assertEquals($data, $loader->load($path));
	}
}
