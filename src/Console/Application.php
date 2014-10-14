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

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Autarky\Console\Command as AutarkyCommand;

/**
 * {@inheritdoc}
 */
class Application extends SymfonyApplication
{
	/**
	 * The Autarky Application object instance.
	 *
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	/**
	 * Set the Autarky application instance.
	 *
	 * @param \Autarky\Kernel\Application $app
	 */
	public function setAutarkyApplication(\Autarky\Kernel\Application $app)
	{
		$this->app = $app;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add(SymfonyCommand $command)
	{
		if ($command instanceof AutarkyCommand) {
			$command->setAutarkyApplication($this->app);
		}

		return parent::add($command);
	}
}
