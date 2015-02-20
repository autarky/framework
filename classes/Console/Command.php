<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * {@inheritdoc}
 */
class Command extends SymfonyCommand
{
	/**
	 * @var \Autarky\Application
	 */
	protected $app;

	/**
	 * {@inheritdoc}
	 */
	public function setAutarkyApplication(\Autarky\Application $app)
	{
		$this->app = $app;
	}
}
