<?php
namespace Autarky\Tests\Config;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class YamlLoaderTest extends PHPUnit_Framework_TestCase
{
	public function makeLoader($parser, $cachePath = null)
	{
		if ($parser === null) {
			$parser = new \Symfony\Component\Yaml\Parser;
		}

		return new \Autarky\Config\Loaders\YamlFileLoader(
			$parser, $cachePath
		);
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
		$loader = $this->makeLoader($parser = $this->mockParser(), TESTS_RSC_DIR.'/yaml-cache');
		$parser->shouldReceive('parse')->once()->andReturn($data = ['foo' => 'bar']);
		$this->assertEquals($data, $loader->load($path));
		$this->assertEquals($data, $loader->load($path));
	}
}
