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
use Symfony\Component\Yaml\Exception\ParseException;

use Autarky\Config\LoadException;
use Autarky\Config\LoaderInterface;
use Autarky\Files\LockingFilesystem;

/**
 * YAML/YML config file loader.
 */
class YamlFileLoader implements LoaderInterface
{
	/**
	 * The symfony YAML parser instance.
	 *
	 * @var Parser
	 */
	protected $parser;

	/**
	 * The filesystem instance.
	 *
	 * @var LockingFilesystem
	 */
	protected $filesys;

	/**
	 * @param Parser $parser
	 */
	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
		$this->filesys = new LockingFilesystem;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		$yaml = $this->filesys->read($path);

		try {
			return $this->parser->parse($yaml);
		} catch (ParseException $e) {
			throw new LoadException($e->getMessage(), $e->getCode(), $e);
		}
	}
}
