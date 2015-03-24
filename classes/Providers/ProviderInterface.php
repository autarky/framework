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

interface ProviderInterface
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register();
}
