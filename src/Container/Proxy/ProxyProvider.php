<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Proxy;

use Autarky\Provider;

class ProxyProvider extends Provider
{
	public function register()
	{
		AbstractProxy::setProxyContainer($this->app->getContainer());
	}
}
