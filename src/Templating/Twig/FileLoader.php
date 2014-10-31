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
	/**
	 * The main templates directory.
	 *
	 * @var string
	 */
	protected $mainPath;

	/**
	 * Constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path)
	{
		// Twig_Loader_Filesystem compatibility. Can it be safely removed?
		if (is_array($path) && count($path) == 1) {
			$path = $path[0];
		}

		if (!is_string($path)) {
			throw new \InvalidArgumentException('$path must be string, '.gettype($path).' given');
		}

		$this->mainPath = $path;

		parent::__construct([$path]);
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

	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($namespace, $location)
	{
		$this->addPath($location, $namespace);

		if (is_dir($dir = $this->mainPath.'/'.$namespace)) {
			$this->prependPath($dir, $namespace);
		}
	}
}
