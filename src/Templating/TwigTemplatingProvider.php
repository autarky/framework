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

		// merge core framework extensions with user extensions
		$extensions = array_merge([
			'Autarky\Templating\Twig\PartialExtension',
			'Autarky\Templating\Twig\UrlGenerationExtension' =>
				['Autarky\Routing\UrlGenerator'],
			'Autarky\Templating\Twig\SessionExtension' =>
				['Symfony\Component\HttpFoundation\Session\Session'],
		], $this->app->getConfig()->get('twig.extensions', []));

		// iterate through the array of extensions. if the array key is an
		// integer, there are no dependencies defined for that extension and we
		// can simply add it. if the array key is a string, the key is the class
		// name of the extension and the value is an array of class dependencies
		// that must be bound to the service container in order for the
		// extension to be loaded.
		foreach ($extensions as $extension => $dependencies) {
			if (is_int($extension)) {
				$env->addExtension($dic->resolve($dependencies));
			} else {
				foreach ((array) $dependencies as $dependency) {
					if (!$dic->isBound($dependency)) {
						// break out of this inner foreach loop and continue to
						// the next iteration of the outer foreach loop,
						// effectively preventing the extension from loading
						continue 2;
					}
				}

				// if any of the dependencies are not met in the above loop,
				// this line of code will not be executed
				$env->addExtension($dic->resolve($extension));
			}
		}

		return $env;
	}
}
