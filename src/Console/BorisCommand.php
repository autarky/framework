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

use Boris\Boris;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A console command that spawns an interactive shell with the help of the
 * composer package "boris".
 */
class BorisCommand extends Command
{
	/**
	 * The boris instance.
	 *
	 * @var Boris
	 */
	protected $boris;

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setName('boris')
			->setDescription('Start an interactive boris shell')
			->setHelp(<<<'EOS'
If the package d11wtq/boris is installed via composer, this command will start an interactive PHP shell where you can play around in an application-like environment.

The main instance of Autarky\Kernel\Application is available as the global variable $app.
EOS
);
	}

	/**
	 * Set the boris instance.
	 *
	 * @param Boris $boris
	 */
	public function setBoris(Boris $boris)
	{
		$this->boris = $boris;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!class_exists('Boris\Boris')) {
			throw new \RuntimeException('Install d11wtq/boris via composer to use this command.');
		}

		// prevent the error handler from outputting any information
		$this->app->getErrorHandler()->prependHandler(function($e) { return ''; });

		$boris = $this->boris ?: new \Boris\Boris('> ');

		// make the $app variable available
		$boris->setLocal(['app' => $this->app]);

		// this will loop forever until ctrl+C is pressed
		$boris->start();

		return 0;
	}
}
