<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating;

use Autarky\Kernel\ServiceProvider;

class TwigServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->getContainer()
		->share('Autarky\Templating\TemplatingEngineInterface', function() {
			return new TwigEngine($this->app);
		});
	}
}
