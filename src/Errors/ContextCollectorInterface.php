<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Errors;

/**
 * Class that collects an array of context data from an application instance.
 */
interface ContextCollectorInterface
{
	/**
	 * Get an array of context data for the application.
	 *
	 * @return array
	 */
	public function getContext();
}
