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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

use Autarky\Config\ConfigInterface;
use Autarky\Container\ContainerInterface;
use Autarky\Routing\RouterInterface;

/**
 * The main application of the framework.
 */
class Application implements HttpKernelInterface, TerminableInterface, ArrayAccess
{
	/**
	 * @var \SplPriorityQueue
	 */
	protected $middlewares;

	/**
	 * @var \Stack\Builder
	 */
	protected $stack;

	/**
	 * @var array
	 */
	protected $providers = [];

	/**
	 * @var \Autarky\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * @var \Autarky\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \Autarky\Routing\RouterInterface
	 */
	protected $router;

	/**
	 * @var \Closure|string
	 */
	protected $environment;

	/**
	 * @var boolean
	 */
	protected $booted = false;

	/**
	 * @var \SplStack
	 */
	protected $configCallbacks;

	/**
	 * Bootstrap the application instance with the default container and config
	 * loader for convenience.
	 *
	 * @param  string $rootPath     Path to your app root. The config loader
	 *                              will look for the "config" directory in this
	 *                              path.
	 * @param  mixed  $environment  See setEnvironment()
	 *
	 * @return static
	 */
	public static function bootstrap($rootPath, $environment)
	{
		return new static(
			$environment,
			new \Autarky\Container\IlluminateContainer,
			new \Autarky\Config\PhpFileStore($rootPath.'/config')
		);
	}

	/**
	 * @param mixed $environment See setEnvironment()
	 * @param mixed $container   See setContainer()
	 * @param mixed $config      See setConfig()
	 */
	public function __construct(
		$environment,
		ContainerInterface $container,
		ConfigInterface $config
	) {
		$this->middlewares = new SplPriorityQueue;
		$this->configCallbacks = new SplStack;

		$this->setEnvironment($environment);
		$this->setContainer($container);
		$this->setConfig($config);

		$this->errorHandler = new ErrorHandler($config->get('app.debug', false));
		$this->errorHandler->register();
	}

	/**
	 * Push a configuration on top of the stack. The config callbacks will be
	 * executed when the application is booted. If the application is already
	 * booted, the callback will be executed at once.
	 *
	 * @param  callable $callback
	 *
	 * @return void
	 */
	public function config(callable $callback)
	{
		if ($this->booted) {
			call_user_func($callback, $this);
		} else {
			$this->configCallbacks->push($callback);
		}
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

	/**
	 * Resolve the environment.
	 *
	 * @return void
	 */
	protected function resolveEnvironment()
	{
		if ($this->environment instanceof Closure) {
			$environment = $this->environment;
			$this->environment = $environment();
		}

		if ($this->config !== null) {
			$this->config->setEnvironment($this->environment);
		}
	}

	/**
	 * Get the current environment.
	 *
	 * @return string
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	public function getErrorHandler()
	{
		return $this->errorHandler;
	}

	/**
	 * Set the application's container.
	 *
	 * @param \Autarky\Container\ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
		$this->container->share('Autarky\Container\ContainerInterface', $this->container);
		$this->container->share(get_class($this->container), $this->container);
		$container->share(get_class($this), $this);
	}

	/**
	 * Get the application's container.
	 *
	 * @return \Autarky\Container\ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the application's config store.
	 *
	 * @param \Autarky\Config\ConfigInterface $config
	 */
	public function setConfig(ConfigInterface $config)
	{
		$this->config = $config;
		$this->container->share('Autarky\Config\ConfigInterface', $this->config);
		$this->container->share(get_class($this->config), $this->config);
	}

	/**
	 * Get the application's config store.
	 *
	 * @return \Autarky\Config\ConfigInterface
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Set the application's router.
	 *
	 * @param \Autarky\Routing\RouterInterface $router
	 */
	public function setRouter(RouterInterface $router)
	{
		$this->router = $router;
		$this->container->share('Autarky\Routing\RouterInterface', $this->router);
		$this->container->share(get_class($this->router), $this->router);
	}

	/**
	 * Get the application's router.
	 *
	 * @return \Autarky\Routing\RouterInterface
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Add a middleware to the application.
	 *
	 * @param \Closure|string|array $middleware
	 * @param int                   $priority
	 */
	public function addMiddleware($middleware, $priority = null)
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

		$providers = $this->config->get('app.providers', []);

		foreach ($providers as $provider) {
			(new $provider($this))->register();
		}

		foreach ($this->configCallbacks as $callback) {
			call_user_func($callback, $this);
		}

		$this->booted = true;
	}

	/**
	 * Run the application.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request $request
	 * @param  bool $send
	 *
	 * @return mixed
	 */
	public function run(Request $request = null, $send = true)
	{
		$this->boot();

		if ($request === null) {
			$request = Request::createFromGlobals();
		}

		$this->stack = new StackBuilder;

		foreach ($this->middlewares as $middleware) {
			call_user_func_array([$this->stack, 'push'], (array) $middleware);
		}

		$response = $this->stack->resolve($this)
			->handle($request);

		return $send ? $response->send() : $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($this->router === null) {
			throw new \Exception('No router set!');
		}

		try {
			$response = $this->router->dispatch($request);
		} catch (\Exception $exception) {
			$response = $this->errorHandler->handle($exception);
		}

		$response->prepare($request);

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function terminate(Request $request, Response $response)
	{
		//
	}

	/**
	 * @see \Autarky\Container\ContainerInterface::resolve()
	 */
	public function resolve()
	{
		return call_user_func_array([$this->container, 'resolve'], func_get_args());
	}

	/**
	 * @see \Autarky\Container\ContainerInterface::bind()
	 */
	public function bind()
	{
		return call_user_func_array([$this->container, 'bind'], func_get_args());
	}

	/**
	 * @see \Autarky\Container\ContainerInterface::share()
	 */
	public function share()
	{
		return call_user_func_array([$this->container, 'share'], func_get_args());
	}

	/**
	 * @see \Autarky\Container\ContainerInterface::alias()
	 */
	public function alias()
	{
		return call_user_func_array([$this->container, 'alias'], func_get_args());
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
