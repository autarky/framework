<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Config;

use Autarky\Support\NamespacedResourceResolverInterface;

interface LoaderInterface extends NamespacedResourceResolverInterface
{
	public function get($key, $default = null);
	public function set($key, $value);
}
