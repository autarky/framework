<?php

use Autarky\Tests\TestCase;
use Autarky\Utils\ArrayUtil;

class ArrayUtilTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider getGetData
	 */
	public function getReturnsCorrectValue(array $data, $key, $expected)
	{
		$this->assertEquals($expected, ArrayUtil::get($data, $key));
	}

	public function getGetData()
	{
		return [
			[[], 'foo', null],
			[['foo' => 'bar'], 'foo', 'bar'],
			[['foo' => 'bar'], 'bar', null],
			[['foo' => 'bar'], 'foo.bar', null],
			[['foo' => ['bar' => 'baz']], 'foo.bar', 'baz'],
			[['foo' => ['bar' => 'baz']], 'foo.baz', null],
			[['foo' => ['bar' => 'baz']], 'foo', ['bar' => 'baz']],
		];
	}

	/**
	 * @test
	 * @dataProvider getSetData
	 */
	public function setSetsCorrectKeyToValue(array $input, $key, $value, array $expected)
	{
		ArrayUtil::set($input, $key, $value);
		$this->assertEquals($expected, $input);
	}

	public function getSetData()
	{
		return [
			[
				['foo' => 'bar'],
				'foo',
				'baz',
				['foo' => 'baz'],
			],
			[
				[],
				'foo',
				'bar',
				['foo' => 'bar'],
			],
			[
				[],
				'foo.bar',
				'baz',
				['foo' => ['bar' => 'baz']],
			],
			[
				['foo' => ['bar' => 'baz']],
				'foo.baz',
				'foo',
				['foo' => ['bar' => 'baz', 'baz' => 'foo']],
			],
			[
				['foo' => ['bar' => 'baz']],
				'foo.baz.bar',
				'baz',
				['foo' => ['bar' => 'baz', 'baz' => ['bar' => 'baz']]],
			],
			[
				[],
				'foo.bar.baz',
				'foo',
				['foo' => ['bar' => ['baz' => 'foo']]],
			],
		];
	}

	/** @test */
	public function setThrowsExceptionOnNonArraySegment()
	{
		$data = ['foo' => 'bar'];
		$this->expectException('UnexpectedValueException', 'Non-array segment encountered');
		ArrayUtil::set($data, 'foo.bar', 'baz');
	}

	/**
	 * @test
	 * @dataProvider getHasData
	 */
	public function hasReturnsCorrectly(array $input, $key, $expected)
	{
		$this->assertEquals($expected, ArrayUtil::has($input, $key));
	}

	public function getHasData()
	{
		return [
			[[], 'foo', false],
			[['foo' => 'bar'], 'foo', true],
			[['foo' => 'bar'], 'bar', false],
			[['foo' => 'bar'], 'foo.bar', false],
			[['foo' => ['bar' => 'baz']], 'foo.bar', true],
			[['foo' => ['bar' => 'baz']], 'foo.baz', false],
			[['foo' => ['bar' => 'baz']], 'foo', true],
			[['foo' => null], 'foo', true],
			[['foo' => ['bar' => null]], 'foo.bar', true],
		];
	}
}
