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
use Twig_Loader_Filesystem;

use Autarky\Templating\Twig\ExtensionsLoader;

class TwigEngine implements TemplatingEngineInterface
{
	protected $twig;
	protected $app;

	public function __construct($app, Twig_Environment $env = null)
	{
		if ($env === null) {
			$loader = new Twig_Loader_Filesystem($app->getConfig()->get('path.templates'));
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
			'RoutingExtension'
		]);

		if ($extensions = $this->app->getConfig()->get('twig.extensions')) {
			$loader->loadUserExtensions($extensions);
		}
	}

	public function render($view, array $data = array())
	{
		return $this->twig->loadTemplate($view)
			->render($data);
	}

	public function addNamespace($namespace, $location)
	{
		$this->twig->getLoader()
			->addPath($location, $namespace);
	}
}
