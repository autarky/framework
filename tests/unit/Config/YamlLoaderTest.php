<?php

use Mockery as m;

class YamlLoaderTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeLoader($parser)
	{
		if ($parser === null) {
			$parser = new \Symfony\Component\Yaml\Parser;
		}

		return new \Autarky\Config\Loaders\YamlFileLoader($parser);
	}

	public function makeCacheLoader($parser, $cachePath = null, $stat = true)
	{
		if ($parser === null) {
			$parser = new \Symfony\Component\Yaml\Parser;
		}

		return new \Autarky\Config\Loaders\CachingYamlFileLoader($parser, $cachePath, $stat);
	}

	public function mockParser()
	{
		return m::mock('Symfony\Component\Yaml\Parser');
	}

	public function getYmlPath($file)
	{
		$path = TESTS_RSC_DIR.'/yaml/'.$file;
		$cachePath = TESTS_RSC_DIR.'/yaml-cache/'.md5($path);
		if (file_exists($cachePath)) unlink($cachePath);
		return $path;
	}

	/** @test */
	public function writesToCachePath()
	{
		$path = $this->getYmlPath('test.yml');
		$loader = $this->makeCacheLoader($parser = $this->mockParser(), TESTS_RSC_DIR.'/yaml-cache');
		$parser->shouldReceive('parse')->once()->andReturn($data = ['foo' => 'bar']);
		touch($path, time());
		$this->assertEquals($data, $loader->load($path));
		touch($path, time() - 1);
		$this->assertEquals($data, $loader->load($path));
	}
}
