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
 * Provides the Twig templating engine.
 */
class TwigTemplatingProvider extends ServiceProvider
{
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->share('Autarky\Templating\TemplatingEngine');

		$dic->define('Autarky\Templating\Twig\Environment', [$this, 'makeTwigEnvironment']);
		$dic->share('Autarky\Templating\Twig\Environment');
		$dic->alias('Autarky\Templating\Twig\Environment', 'Twig_Environment');
	}

	public function makeTwigEnvironment()
	{
		$config = $this->app->getConfig();
		$loader = new Twig\FileLoader($config->get('path.templates'));
		$options = ['debug' => $config->get('app.debug')];

		if ($config->has('path.templates_cache')) {
			$options['cache'] = $config->get('path.templates_cache');
		} else if ($config->has('path.storage')) {
			$options['cache'] = $config->get('path.storage').'/twig';
		}

		$env = new Twig\Environment($loader, $options);

		$extLoader = new Twig\ExtensionLoader($env, $this->app);

		$extLoader->loadCoreExtensions([
			'Autarky\Container\ContainerInterface' => 'PartialExtension',
			'Autarky\Routing\UrlGenerator' => 'UrlGenerationExtension',
			'Symfony\Component\HttpFoundation\Session\Session' => 'SessionExtension',
		]);

		if ($extensions = $this->app->getConfig()->get('twig.extensions')) {
			$extLoader->loadUserExtensions($extensions);
		}

		return $env;
	}
}
