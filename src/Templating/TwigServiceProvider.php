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

/**
 * Simple service provider to bind the twig templating engine onto the IoC
 * container.
 */
class TwigServiceProvider extends ServiceProvider
{
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->share('Autarky\Templating\TemplatingEngine');

		$dic->share('Twig_Environment', [$this, 'makeTwigEnvironment']);
		$dic->alias('Autarky\Templating\Twig\Environment', 'Twig_Environment');
	}

	public function makeTwigEnvironment()
	{
		$config = $this->app->getConfig();
		$loader = new Twig\FileLoader($config->get('path.templates'));

		$env = new Twig\Environment($loader, [
			'cache' => $config->get('path.templates-cache'),
			'debug' => $config->get('app.debug'),
		]);

		$loader = new Twig\ExtensionLoader($env, $this->app);

		$loader->loadCoreExtensions([
			'PartialExtension',
			'Autarky\Routing\RoutingServiceProvider' => 'UrlGenerationExtension',
			'Autarky\Session\SessionServiceProvider' => 'SessionExtension',
		]);

		if ($extensions = $this->app->getConfig()->get('twig.extensions')) {
			$loader->loadUserExtensions($extensions);
		}

		return $env;
	}
}
