<?php
namespace Autarky\Files;

class Locator
{
	public function locate($basenames, $extensionOrExtensions)
	{
		$basenames = (array) $basenames;
		$extensions = (array) $extensionOrExtensions;

		$located = [];

		foreach ($basenames as &$basename) {
			foreach ($extensions as &$ext) {
				$path = $basename.$ext;
				if (file_exists($path)) {
					$located[] = $path;
				}
			}
		}

		return $located;
	}
}
