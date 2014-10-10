<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Config\Loaders;

use Autarky\Config\LoaderInterface;
use Symfony\Component\Yaml\Parser;

/**
 * YAML/YML config file loader.
 */
class CachingYamlFileLoader implements LoaderInterface
{
	protected $loader;
	protected $cacheDir;

	public function __construct(YamlFileLoader $loader, $cacheDir = null)
	{
		$this->loader = $loader;
		$this->cacheDir = $cacheDir;
	}

	public function setCacheDir($cacheDir)
	{
		$this->cacheDir = $cacheDir;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		if ($this->cacheDir !== null) {
			$cachePath = $this->cacheDir.'/'.md5($path);
			if (file_exists($cachePath) && filemtime($cachePath) >= filemtime($path)) {
				return require $cachePath;
			}
		}

		$data = $this->loader->load($path);

		if ($this->cacheDir !== null) {
			$cachePath = isset($cachePath) ? $cachePath : $this->cacheDir.'/'.md5($path);
			file_put_contents($cachePath, '<?php return '.var_export($data, true).";\n");
		}

		return $data;
	}
}
