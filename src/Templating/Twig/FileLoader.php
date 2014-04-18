<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating\Twig;

use Twig_Loader_Filesystem;
use Twig_Error_Loader;

/**
 * Override the default twig filesystem loader to use our custom namespace
 * syntax of 'namespace:template' over '@namespace/template'
 */
class FileLoader extends Twig_Loader_Filesystem
{
	protected function findTemplate($name)
	{
		$name = $this->normalizeName($name);

		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		$this->validateName($name);

		$namespace = self::MAIN_NAMESPACE;
		$shortname = $name;

		if (($pos = strpos($name, ':')) !== false) {
			$namespace = substr($name, 0, $pos);
			$shortname = substr($name, $pos + 1);
		}

		if (!isset($this->paths[$namespace])) {
			throw new Twig_Error_Loader(sprintf('There are no registered paths for namespace "%s".', $namespace));
		}

		foreach ($this->paths[$namespace] as $path) {
			if (is_file($path.'/'.$shortname)) {
				return $this->cache[$name] = $path.'/'.$shortname;
			}
		}

		throw new Twig_Error_Loader(sprintf(
			'Unable to find template "%s" (looked into: %s).',
			$name, implode(', ', $this->paths[$namespace])
		));
	}
}
