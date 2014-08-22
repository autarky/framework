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

	/**
	 * @var array
	 */
	protected $contextHandlers = [];

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

	/**
	 * Get the Twig environment.
	 *
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		return $this->twig;
	}

	protected function loadExtensions()
	{
		$loader = new ExtensionsLoader($this->twig, $this->app);

		$loader->loadCoreExtensions([
			'PartialExtension',
			'Autarky\Routing\RoutingServiceProvider' => 'UrlGenerationExtension',
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
		$data += $this->getContext($name);

		return $this->twig->loadTemplate($name)
			->render($data);
	}

	protected function getContext($template)
	{
		if (!array_key_exists($template, $this->contextHandlers)) {
			return [];
		}

		$context = [];

		foreach ($this->contextHandlers[$template] as $handler) {
			$context = array_merge($context, $this->getContextFromHandler($handler));
		}

		return $context;
	}

	protected function getContextFromHandler($handler)
	{
		if ($handler instanceof \Closure) {
			return $handler();
		}

		list($class, $method) = \Autarky\splitclm($handler, 'getContext');
		$obj = $this->app->resolve($class);

		return $obj->$method();
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerContextHandler($template, $handler)
	{
		$this->contextHandlers[$template][] = $handler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addGlobalVariable($name, $value)
	{
		$this->twig->addGlobal($name, $value);
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
