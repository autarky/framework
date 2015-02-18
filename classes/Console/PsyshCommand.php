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

use Psy\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A console command that spawns an interactive shell with the help of the
 * composer package "psysh".
 */
class PsyshCommand extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setName('psysh')
			->setDescription('Start an interactive psysh shell')
			->setHelp(<<<'EOS'
If the package psy/psysh is installed via composer, this command will start an interactive PHP shell where you can play around in an application-like environment.

The main instance of Autarky\Application is available as the global variable $app.
EOS
);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!class_exists('Psy\Shell')) {
			throw new \RuntimeException('Install psy/psysh via composer to use this command.');
		}

		// prevent the error handler from outputting any information
		$this->app->getErrorHandler()->prependHandler(function($e) { return ''; });

		$psysh = new Shell();

		// make the $app variable available
		$psysh->setScopeVariables(['app' => $this->app]);

		// this will loop forever until ctrl+C is pressed
		$psysh->run();

		return 0;
	}
}
