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

class Application extends SymfonyApplication
{
	protected $app;

	public function setAutarkyApplication($app)
	{
		$this->app = $app;
	}

	public function add(SymfonyCommand $command)
	{
		if ($command instanceof AutarkyCommand) {
			$command->setAutarkyApplication($this->app);
		}

		return parent::add($command);
	}
}
