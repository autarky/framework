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
	/**
	 * The container instance.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * The loader classes.
	 *
	 * @var string[]
	 */
	protected $loaderClasses = [];

	/**
	 * The loader class instances.
	 *
	 * @var LoaderInterface[]
	 */
	protected $loaders = [];

	/**
	 * The registered extensions.
	 *
	 * @var string[]
	 */
	protected $extensions = [];

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Add a loader.
	 *
	 * @param  string $extensions
	 * @param  string $loaderClass
	 *
	 * @return void
	 */
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

	/**
	 * Get the available extensions.
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		if ($this->extensions === null) {
			$this->extensions = array_unique(array_merge(
				array_keys($this->loaderClasses), array_keys($this->loaders)
			));
		}

		return $this->extensions;
	}

	/**
	 * Get the loader for a given path.
	 *
	 * @param  string $path
	 *
	 * @return LoaderInterface
	 */
	public function getForPath($path)
	{
		$extension = $this->getExtension($path);

		if (!isset($this->loaders[$extension])) {
			$this->resolveLoader($extension);
		}

		return $this->loaders[$extension];
	}

	protected function resolveLoader($extension)
	{
		if (!isset($this->loaderClasses[$extension])) {
			throw new \RuntimeException("Invalid extension: $extension");
		}

		$this->loaders[$extension] = $this->container
			->resolve($this->loaderClasses[$extension]);
	}

	protected function getExtension($path)
	{
		return substr($path, strrpos($path, '.') + 1);
	}
}
