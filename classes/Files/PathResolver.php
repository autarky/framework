<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Files;

/**
 * Class that resolves possible paths based on various factors.
 */
class PathResolver
{
	/**
	 * Main paths.
	 *
	 * @var string[]
	 */
	protected $paths;

	/**
	 * External paths mounted onto locations on the main path.
	 *
	 * @var string[][]
	 */
	protected $mounts;

	/**
	 * Constructor
	 *
	 * @param string|array $pathOrPaths
	 */
	public function __construct($pathOrPaths = [])
	{
		$this->paths = (array) $pathOrPaths;
	}

	/**
	 * Add a main path.
	 *
	 * @param string $path
	 */
	public function addPath($path)
	{
		$this->paths[] = $path;
	}

	/**
	 * Mount a path onto a location.
	 *
	 * @param  string $location
	 * @param  string $path
	 */
	public function mount($location, $path)
	{
		$this->mounts[$location][] = $path;
	}

	/**
	 * Resolve possible paths for a relative path.
	 *
	 * @param  string $path
	 *
	 * @return array
	 */
	public function resolve($path)
	{
		$paths = [];

		foreach ($this->paths as $configuredPath) {
			$paths[] = $configuredPath.'/'.$path;
		}

		$parts = explode('/', $path);
		if (count($parts) == 1) {
			return $paths;
		}

		$current = '';
		$mountPaths = [];
		foreach ($parts as $part) {
			if ($current) {
				$current .= '/'.$part;
			} else {
				$current = $part;
			}

			if (isset($this->mounts[$current])) {
				foreach ($this->mounts[$current] as $mount) {
					// relative to mount directory
					$relativePath = str_replace($current, '', $path);
					$mountPaths[] = $mount.$relativePath;
				}
			}
		}

		return array_merge($mountPaths, $paths);
	}

	/**
	 * Based on a set of basenames (filename without extension) and a set of
	 * possible extensions, find the files that exist.
	 *
	 * @param  string|string[] $basenameOrNames
	 * @param  string|string[] $extensionOrExtensions
	 *
	 * @return string[]
	 */
	public function locate($basenameOrNames, $extensionOrExtensions)
	{
		$basenames = (array) $basenameOrNames;
		$extensions = (array) $extensionOrExtensions;

		$located = [];

		foreach ($basenames as $basename) {
			foreach ($extensions as $ext) {
				$path = $basename.$ext;
				if (file_exists($path)) {
					$located[] = $path;
				}
			}
		}

		return $located;
	}
}
