<?php
namespace Autarky\Files;

class PathResolver
{
	protected $paths;
	protected $mounts;

	public function __construct($pathOrPaths = [])
	{
		$this->paths = (array) $pathOrPaths;
	}

	public function addPath($path)
	{
		$this->paths[] = $path;
	}

	public function mount($location, $path)
	{
		$this->mounts[$location][] = $path;
	}

	public function resolve($path)
	{
		$paths = $this->getPaths($path);

		return $paths;
	}

	protected function getPaths($path)
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
}
