<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky;

/**
 * A configurator is a class that configures one or more other classes after the
 * application has booted.
 */
interface ConfiguratorInterface
{
	/**
	 * Run the configuration.
	 *
	 * @return void
	 */
	public function configure();
}
