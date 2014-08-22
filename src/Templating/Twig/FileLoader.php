<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating\Twig;

use Twig_Loader_Filesystem;

/**
 * Override the default twig filesystem loader to use our custom namespace
 * syntax of 'namespace:template' over '@namespace/template'
 */
class FileLoader extends Twig_Loader_Filesystem
{
	protected function parseName($name, $default = self::MAIN_NAMESPACE)
	{
		if (($pos = strpos($name, ':')) !== false) {
			$namespace = substr($name, 0, $pos);
			$shortname = substr($name, $pos + 1);
			return [$namespace, $shortname];
		}

		return [$default, $name];
	}
}
