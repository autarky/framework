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

use Autarky\Utils\ArrayUtil;
use Autarky\Files\PathResolver;

/**
 * File-based config implementation.
 *
 * Reads files from one or multiple directories, with the possibility of
 * cascading for different environments and overriding of namespaces.
 */
class FileStore implements ConfigInterface
{
	/**
	 * The path resolver instance.
	 *
	 * @var PathResolver
	 */
	protected $pathResolver;

	/**
	 * The file locator instance.
	 *
	 * @var Locator
	 */
	protected $fileLocator;

	/**
	 * The loader factory instance.
	 *
	 * @var LoaderFactory
	 */
	protected $loaderFactory;

	/**
	 * The current environment.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * The loaded config data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Constructor.
	 *
	 * @param PathResolver  $pathResolver
	 * @param LoaderFactory $loaderFactory
	 * @param string|null   $environment
	 */
	public function __construct(
		PathResolver $pathResolver,
		LoaderFactory $loaderFactory,
		$environment = null
	) {
		$this->pathResolver = $pathResolver;
		$this->loaderFactory = $loaderFactory;
		$this->environment = $environment;
	}

	/**
	 * Get the loader factory instance.
	 *
	 * @return LoaderFactory
	 */
	public function getLoaderFactory()
	{
		return $this->loaderFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function mount($location, $path)
	{
		$this->pathResolver->mount($location, $path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		$this->loadData($key);

		return ArrayUtil::has($this->data, $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		$this->loadData($key);

		return ArrayUtil::get($this->data, $key, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		$this->loadData($key);

		ArrayUtil::set($this->data, $key, $value);
	}

	protected function loadData($key)
	{
		$basename = $this->getBasename($key);

		if (array_key_exists($basename, $this->data)) {
			return;
		}

		$paths = $this->getPaths($basename);

		foreach ($paths as $path) {
			$data = $this->getDataFromFile($path);

			if (isset($this->data[$basename])) {
				$this->data[$basename] = array_replace(
					$this->data[$basename], $data);
			} else {
				$this->data[$basename] = $data;
			}
		}
	}

	protected function getBasename($key)
	{
		return current(explode('.', $key));
	}

	protected function getPaths($basename)
	{
		$basenames = $this->pathResolver->resolve($basename);

		if ($this->environment) {
			$envBasenames = array_map(function($basename) {
				return $basename.'.'.$this->environment;
			}, $basenames);

			$basenames = array_merge($basenames, $envBasenames);
		}

		$extensions = $this->loaderFactory->getExtensions();

		return $this->pathResolver->locate($basenames, $extensions);
	}

	protected function getDataFromFile($path)
	{
		if (!is_readable($path)) {
			throw new LoadException("File is not readable: $path");
		}

		$loader = $this->loaderFactory->getForPath($path);

		return $loader->load($path);
	}
}
