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

use Twig_Environment;

use Autarky\Kernel\Application;
use Autarky\Templating\Twig\FileLoader;
use Autarky\Templating\Twig\ExtensionsLoader;

/**
 * Templating engine utilizing Twig.
 */
class TwigEngine implements TemplatingEngineInterface
{
	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	public function __construct(Application $app, Twig_Environment $env = null)
	{
		if ($env === null) {
			$loader = new FileLoader($app->getConfig()->get('path.templates'));
			$config = [
				'cache' => $app->getConfig()->get('path.templates-cache'),
				'debug' => $app->getConfig()->get('app.debug'),
			];
			$env = new Twig_Environment($loader, $config);
		}

		$this->twig = $env;
		$this->app = $app;

		$this->loadExtensions();
	}

	protected function loadExtensions()
	{
		$loader = new ExtensionsLoader($this->twig, $this->app);

		$loader->loadCoreExtensions([
			'PartialExtension',
			'Autarky\Routing\RoutingServiceProvider' => 'RoutingExtension',
			'Autarky\Session\SessionServiceProvider' => 'SessionExtension',
		]);

		if ($extensions = $this->app->getConfig()->get('twig.extensions')) {
			$loader->loadUserExtensions($extensions);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($name, array $data = array())
	{
		return $this->twig->loadTemplate($name)
			->render($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($namespace, $location)
	{
		$this->twig->getLoader()
			->addPath($location, $namespace);
	}
}
