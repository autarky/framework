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
use Autarky\Support\NamespacedResourceResolverInterface;

/**
 * Override the default twig filesystem loader to use our custom namespace
 * syntax of 'namespace:template' over '@namespace/template'
 */
class FileLoader extends Twig_Loader_Filesystem implements NamespacedResourceResolverInterface
{
	protected $mainPath;

	public function __construct($paths = array())
	{
		if (is_array($paths) && count($paths) == 1) {
			$paths = $paths[0];
		}

		if (!is_string($paths)) {
			throw new \InvalidArgumentException('$paths must be string, '.gettype($paths).' given');
		}

		$this->mainPath = $paths;

		parent::__construct([$paths]);
	}

	protected function parseName($name, $default = self::MAIN_NAMESPACE)
	{
		if (($pos = strpos($name, ':')) !== false) {
			$namespace = substr($name, 0, $pos);
			$shortname = substr($name, $pos + 1);
			return [$namespace, $shortname];
		}

		return [$default, $name];
	}

	public function addNamespace($namespace, $location)
	{
		$this->addPath($location, $namespace);

		if (is_dir($dir = $this->mainPath.'/'.$namespace)) {
			$this->prependPath($dir, $namespace);
		}
	}
}
