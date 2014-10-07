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
 * Split a string into class name and method.
 *
 * 'Class:method' returns ['Class', 'method']
 * 'Class' returns ['Class', $default]
 *
 * @param  string $string
 * @param  string $default  Default method
 *
 * @return string[]
 */
function splitclm($string, $default)
{
	$segments = explode(':', $string);
	$class = $segments[0];
	$method = isset($segments[1]) ? $segments[1] : $default;

	return [$class, $method];
}
