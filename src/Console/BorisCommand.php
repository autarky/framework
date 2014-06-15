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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BorisCommand extends Command
{
	protected function configure()
	{
		$this->setName('boris')
			->setDescription('Start an interactive boris shell.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!class_exists('Boris\Boris')) {
			throw new \RuntimeException('Install d11wtq/boris via composer to use this command.');
		}

		restore_error_handler(); restore_exception_handler();
		(new \Boris\Boris('> '))->start();
	}
}
