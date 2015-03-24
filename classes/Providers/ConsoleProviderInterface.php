<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Providers;

use Symfony\Component\Console\Application as ConsoleApplication;

interface ConsoleProviderInterface
{
	/**
	 * Register the service provider with the console application.
	 *
	 * @param  ConsoleApplication $console
	 *
	 * @return void
	 */
	public function registerConsole(ConsoleApplication $console);
}
