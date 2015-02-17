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
use Autarky\Files\LockingWriteOperation;

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
	 * @var string
	 */
	protected $cacheDir;

	/**
	 * Whether or not to check if cache files are outdated.
	 *
	 * @var bool
	 */
	protected $stat;

	/**
	 * @param YamlFileLoader $loader
	 * @param string         $cacheDir
	 * @param bool           $stat
	 */
	public function __construct(YamlFileLoader $loader, $cacheDir, $stat = true)
	{
		$this->loader = $loader;
		$this->cacheDir = $cacheDir;
		$this->stat = (bool) $stat;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		$cachePath = $this->cacheDir.'/'.md5($path);

		if ($this->shouldLoadCache($path, $cachePath)) {
			return require $cachePath;
		}

		$data = $this->loader->load($path);

		$writer = new LockingWriteOperation($cachePath);
		$writer->write('<?php return '.var_export($data, true).";\n");

		return $data;
	}

	protected function shouldLoadCache($path, $cachePath)
	{
		if (!$this->stat) {
			return true;
		}

		if (!file_exists($cachePath)) {
			return false;
		}

		return filemtime($cachePath) >= filemtime($path);
	}
}
