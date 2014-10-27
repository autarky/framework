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

use Autarky\Container\ContainerInterface;
use Autarky\Kernel\ServiceProvider;

/**
 * Provides the Twig templating engine.
 */
class TwigTemplatingProvider extends ServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->share('Autarky\Templating\TemplatingEngine');

		$dic->define('Twig_LoaderInterface', [$this, 'makeTwigLoader']);

		$dic->define('Autarky\Templating\Twig\Environment', [$this, 'makeTwigEnvironment']);
		$dic->share('Autarky\Templating\Twig\Environment');
		$dic->alias('Autarky\Templating\Twig\Environment', 'Twig_Environment');
	}

	/**
	 * Make the twig template loader.
	 *
	 * @return \Autarky\Templating\Twig\FileLoader
	 */
	public function makeTwigLoader()
	{
		return new Twig\FileLoader($this->app->getConfig()->get('path.templates'));
	}

	/**
	 * Make the twig environment.
	 *
	 * @return \Autarky\Templating\Twig\Environment
	 */
	public function makeTwigEnvironment(ContainerInterface $dic)
	{
		$config = $this->app->getConfig();
		$options = ['debug' => $config->get('app.debug')];

		if ($config->has('path.templates_cache')) {
			$options['cache'] = $config->get('path.templates_cache');
		} else if ($config->has('path.storage')) {
			$options['cache'] = $config->get('path.storage').'/twig';
		}

		$env = new Twig\Environment($dic->resolve('Twig_LoaderInterface'), $options);

		$extensions = array_merge([
			'Autarky\Templating\Twig\PartialExtension',
			'Autarky\Templating\Twig\UrlGenerationExtension' =>
				['Autarky\Routing\UrlGenerator'],
			'Autarky\Templating\Twig\SessionExtension' =>
				['Symfony\Component\HttpFoundation\Session\Session'],
		], $this->app->getConfig()->get('twig.extensions', []));

		foreach ($extensions as $extension => $dependencies) {
			if (is_int($extension)) {
				$env->addExtension($dic->resolve($dependencies));
			} else {
				$load = true;

				foreach ((array) $dependencies as $dependency) {
					if (!$dic->isBound($dependency)) {
						$load = false;
						break;
					}
				}

				if ($load) {
					$env->addExtension($dic->resolve($extension));
				}
			}
		}

		return $env;
	}
}
