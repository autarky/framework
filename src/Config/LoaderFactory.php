<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Config;

use Autarky\Container\ContainerInterface;

class LoaderFactory
{
	protected $container;
	protected $loaderClasses = [];
	protected $loaders = [];
	protected $extensions = [];

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function addLoader($extensions, $loaderClass)
	{
		foreach ((array) $extensions as $extension) {
			$this->extensions[] = $extension;
			if (is_string($loaderClass)) {
				$this->loaderClasses[$extension] = $loaderClass;
			} else if (is_object($loaderClass)) {
				$this->loaders[$extension] = $loaderClass;
			}
		}
	}

	public function getExtensions()
	{
		if ($this->extensions === null) {
			$this->extensions = array_unique(array_merge(
				array_keys($this->loaderClasses), array_keys($this->loaders)
			));
		}

		return $this->extensions;
	}

	public function getForPath($path)
	{
		$extension = $this->getExtension($path);

		if (!isset($this->loaders[$extension])) {
			$this->resolveLoader($extension);
		}

		return $this->loaders[$extension];
	}

	public function resolveLoader($extension)
	{
		if (!isset($this->loaderClasses[$extension])) {
			throw new \RuntimeException("Invalid extension: $extension");
		}

		$this->loaders[$extension] = $this->container
			->resolve($this->loaderClasses[$extension]);
	}

	public function getExtension($path)
	{
		return substr($path, strrpos($path, '.') + 1);
	}
}
