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
class YamlFileLoader implements LoaderInterface
{
	/**
	 * The symfony YAML parser instance.
	 *
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @param Parser $parser
	 */
	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		$yaml = file_get_contents($path);

		return $this->parser->parse($yaml);
	}
}
