<?php

use Autarky\Tests\TestCase;
use Autarky\Support\ArrayUtils;

class ArrayUtilsTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider getGetData
	 */
	public function getCallReturnsCorrectValue(array $data, $key, $expected)
	{
		$this->assertEquals($expected, ArrayUtils::get($data, $key));
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
	public function setCallSetsCorrectKeyToValue(array $input, $key, $value, array $expected)
	{
		ArrayUtils::set($input, $key, $value);
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
		$this->setExpectedException('UnexpectedValueException', 'Non-array segment encountered');
		ArrayUtils::set($data, 'foo.bar', 'baz');
	}

	/**
	 * @test
	 * @dataProvider getHasData
	 */
	public function hasCallReturnsCorrectly(array $input, $key, $result)
	{
		$this->assertEquals($result, ArrayUtils::has($input, $key));
	}

	public function getHasData()
	{
		return [
			[[], 'foo', false],
			[['foo' => 'bar'], 'foo', true],
			[['foo' => 'bar'], 'bar', false],
			[['foo' => ['bar' => 'baz']], 'foo.bar', true],
			[['foo' => ['bar' => 'baz']], 'foo.baz', false],
			[['foo' => ['bar' => 'baz']], 'foo', true],
			[['foo' => null], 'foo', true],
			[['foo' => ['bar' => null]], 'foo.bar', true],
		];
	}
}
