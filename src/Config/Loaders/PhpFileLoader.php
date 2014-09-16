<?php
namespace Autarky\Config\Loaders;

class PhpFileLoader
{
	public function load($path)
	{
		$data = require $path;

		if (!is_array($data)) {
			throw new \InvalidArgumentException("Config file \"$path\" must return an array");
		}

		return $data;
	}
}
