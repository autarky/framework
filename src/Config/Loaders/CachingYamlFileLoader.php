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

/**
 * Caching wrapper for the YAML/YML config file loader.
 */
class CachingYamlFileLoader implements LoaderInterface
{
	/**
	 * The internal YAML file loader instance. It will be responsible for
	 * parsing files that haven't been cached.
	 *
	 * @var YamlFileLoader
	 */
	protected $loader;

	/**
	 * The directory in which to look for cached files.
	 *
	 * @var string|null
	 */
	protected $cacheDir;

	/**
	 * @param YamlFileLoader $loader
	 * @param string|null    $cacheDir
	 */
	public function __construct(YamlFileLoader $loader, $cacheDir = null)
	{
		$this->loader = $loader;
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
