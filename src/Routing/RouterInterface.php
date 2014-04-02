<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

use Symfony\Component\HttpFoundation\Request;

interface RouterInterface
{
	public function dispatch(Request $request);
	public function addRoute($method, $path, $handler, $name = null);
	public function getRouteUrl($name, array $params = array());
}
