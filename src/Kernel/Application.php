<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel;

use Closure;
use SplStack;
use ArrayAccess;
use SplPriorityQueue;
use Stack\Builder as StackBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Autarky\Config\LoaderInterface as ConfigLoaderInterface;
use Autarky\Container\ContainerInterface;
use Autarky\Routing\RouterInterface;

class Application implements HttpKernelInterface, ArrayAccess
{
	protected $middlewares;
	protected $stack;
	protected $providers = [];
	protected $config;
	protected $container;
	protected $router;

	protected $environment;
	protected $booted = false;
	protected $configCallbacks;

	/**
	 * Bootstrap the application instance with the default container and config
	 * loader for convenience.
	 *
	 * @param  string $rootPath      Path to your app root. The config loader
	 * will look for the "config" directory in this path.
	 * @param  string|\Closure $environment  @see setEnvironment()
	 *
	 * @return static
	 */
	public static function bootstrap($rootPath, $environment)
	{
		$app = new static($environment);

		$app->setContainer(new \Autarky\Container\WartContainer);

		$app->setConfig(new \Autarky\Config\PhpLoader($rootPath.DIRECTORY_SEPARATOR.'config'));

		return $app;
	}

	public function __construct($environment)
	{
		$this->middlewares = new SplPriorityQueue;
		$this->configCallbacks = new SplStack;
		$this->setEnvironment($environment);
	}

	public function config(Closure $callback)
	{
		$this->configCallbacks->push($callback);
	}

	/**
	 * Set the application environment.
	 *
	 * @param \Closure|string $env If a closure, it will be invoked upon the
	 * application initializing.
	 */
	public function setEnvironment($environment)
	{
		if ($this->booted) {
			throw new \RuntimeException('Cannot set environment after booting');
		}

		$this->environment = $environment;
	}

	protected function resolveEnvironment()
	{
		if ($this->environment instanceof \Closure) {
			$environment = $this->environment;
			$this->environment = $environment();
		}

		$this->config->setEnvironment($this->environment);
	}

	public function getEnvironment()
	{
		return $this->environment;
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
		$this->container->share('\Autarky\Container\ContainerInterface', $this->container);
		$this->container->share('\\'.get_class($this->container), $this->container);
		$container->share('\\'.get_class($this), $this);
	}

	public function getContainer()
	{
		return $this->container;
	}

	public function setConfig(ConfigLoaderInterface $config)
	{
		$this->config = $config;
		$this->container->share('\Autarky\Config\LoaderInterface', $this->config);
		$this->container->share('\\'.get_class($this->config), $this->config);
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function setRouter(RouterInterface $router)
	{
		$this->router = $router;
		$this->container->share('\Autarky\Routing\RouterInterface', $this->router);
		$this->container->share('\\'.get_class($this->router), $this->router);
	}

	public function getRouter()
	{
		return $this->router;
	}

	public function addMiddleware(HttpKernelInterface $middleware, $priority = null)
	{
		$this->middlewares->insert($middleware, (int) $priority);
	}

	/**
	 * Boot the application.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->booted) return;

		$this->resolveEnvironment();

		$providers = $this->config->get('app.providers');

		foreach ($providers as $provider) {
			(new $provider($this))->register();
		}

		foreach ($this->configCallbacks as $callback) {
			$callback($this);
		}

		$this->booted = true;
	}

	/**
	 * Run the application.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request $request Optional
	 *
	 * @return mixed
	 */
	public function run(Request $request = null)
	{
		$this->boot();

		if ($request === null) {
			$request = Request::createFromGlobals();
		}

		$this->stack = new StackBuilder;

		foreach ($this->middlewares as $middleware) {
			$this->stack->push($middleware);
		}

		return $this->stack->resolve($this)
			->handle($request)
			->send();
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($this->router === null) {
			throw new \Exception('No router set!');
		}

		return $this->router->dispatch($request);
	}

	public function offsetGet($key)
	{
		return $this->container->resolve($key);
	}

	public function offsetExists($key)
	{
		// @todo ?
	}

	public function offsetSet($key, $value)
	{
		$this->container->bind($key, $value);
	}

	public function offsetUnset($key)
	{
		// @todo ?
	}
}
