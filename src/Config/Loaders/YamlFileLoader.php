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

class YamlFileLoader implements LoaderInterface
{
	protected $parser;

	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		if (!file_exists($path)) {
			throw new \InvalidArgumentException("File does not exist: $path");
		}

		$yaml = file_get_contents($path);

		return $this->parser->parse($yaml);
	}
}