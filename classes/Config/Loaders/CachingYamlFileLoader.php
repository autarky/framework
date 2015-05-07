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

use Symfony\Component\Yaml\Parser;

/**
 * Caching wrapper for the YAML/YML config file loader.
 */
class CachingYamlFileLoader extends YamlFileLoader
{
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
	 * Constructor.
	 *
	 * @param Parser $parser
	 * @param string $cacheDir
	 * @param bool   $stat
	 */
	public function __construct(Parser $parser, $cacheDir, $stat = true)
	{
		parent::__construct($parser);
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

		$data = parent::load($path);

		$this->filesys->write($cachePath, '<?php return '.var_export($data, true).";\n");

		return $data;
	}

	protected function shouldLoadCache($path, $cachePath)
	{
		if (!file_exists($cachePath)) {
			return false;
		}

		if (!$this->stat) {
			return true;
		}

		// if the cache file is more recent than the real file,
		// the cache file should be loaded
		return filemtime($cachePath) > filemtime($path);
	}
}
