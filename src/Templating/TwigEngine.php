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
use Twig_Error_Loader;

use Autarky\Templating\Twig\ExtensionsLoader;
use Autarky\Support\Str;

class TwigEngine implements TemplatingEngineInterface
{
	protected $twig;
	protected $app;
	protected $namespaces = [];

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
		$view = $this->transformViewName($view);
		try {
			return $this->twig->loadTemplate($view)
				->render($data);
		} catch (Twig_Error_Loader $e) {
			throw new Twig_Error_Loader(str_replace($this->namespaces, 
					array_keys($this->namespaces), $e->getMessage()));
		}
	}

	public function addNamespace($namespace, $location)
	{
		$this->namespaces[$namespace] = $this->transformNamespace($namespace);
		$this->twig->getLoader()
			->addPath($location, $this->namespaces[$namespace]);
	}

	protected function transformNamespace($namespace)
	{
		return '{{'. str_replace('/', '_', $namespace) .'}}';
	}

	protected function transformViewName($name)
	{
		if (strpos($name, ':') !== false) {
			$name = '@' . str_replace(':', '/', $name);
		}
		if (Str::containsAny($name, array_keys($this->namespaces))) {
			$name = str_replace(array_keys($this->namespaces), $this->namespaces, $name);
		}
		return $name;
	}
}
