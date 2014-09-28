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

use ArrayAccess;
use Closure;
use Exception;
use SplPriorityQueue;
use SplStack;
use Stack\Builder as StackBuilder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Autarky\Config\ConfigInterface;
use Autarky\Console\Application as ConsoleApplication;
use Autarky\Container\ContainerException;
use Autarky\Container\ContainerInterface;
use Autarky\Errors\ErrorHandlerInterface;
use Autarky\Routing\RouterInterface;

/**
 * The main application of the framework.
 */
class Application implements HttpKernelInterface, ArrayAccess
{
	/**
	 * The framework version.
	 */
	const VERSION = '0.5.x';

	/**
	 * The application's service providers.
	 *
	 * @var array
	 */
	protected $providers = [];

	/**
	 * @var \SplPriorityQueue
	 */
	protected $middlewares;

	/**
	 * @var \Autarky\Kernel\HttpKernel
	 */
	protected $kernel;

	/**
	 * @var \Stack\Builder
	 */
	protected $stack;

	/**
	 * @var \Autarky\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * @var \Autarky\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \Autarky\Errors\ErrorHandlerInterface
	 */
	protected $errorHandler;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|false
	 */
	protected $eventDispatcher;

	/**
	 * @var \Symfony\Component\Console\Application
	 */
	protected $console;

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
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requests;

	/**
	 * Construct a new application instance.
	 *
	 * @param \Closure|string   $environment
	 * @param ServiceProvider[] $providers
	 */
	public function __construct($environment, array $providers)
	{
		$this->middlewares = new SplPriorityQueue;
		$this->configCallbacks = new SplStack;
		$this->requests = new RequestStack;
		$this->environment = $environment;
		
		foreach ($providers as $provider) {
			$class = is_object($provider) ? get_class($provider) : (string) $provider;
			$this->providers[$class] = $provider;
		}
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
	 * Set the environment of the application. Has to be called before boot().
	 *
	 * @param \Closure|string $environment
	 */
	public function setEnvironment($environment)
	{
		if ($this->booted) {
			throw new \RuntimeException("Cannot set environment after application has booted");
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
			if ($this->errorHandler !== null) {
				$this->errorHandler->setDebug($this->config->get('app.debug'));
			}
		}
	}

	/**
	 * Get the current environment.
	 *
	 * @return string
	 */
	public function getEnvironment()
	{
		if (!$this->environment || $this->environment instanceof Closure) {
			throw new \RuntimeException('Environment has not yet been resolved');
		}

		return $this->environment;
	}

	/**
	 * Get the application's providers.
	 *
	 * @return string[] array of provider class names
	 */
	public function getProviders()
	{
		return array_keys($this->providers);
	}

	public function setErrorHandler(ErrorHandlerInterface $errorHandler)
	{
		$this->errorHandler = $errorHandler;
		$this->errorHandler->setApplication($this);
		$this->errorHandler->register();
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
		$class = get_class($container);
		$this->container->alias($class, 'Autarky\Container\ContainerInterface');
		$this->container->instance($class, $this->container);
		$container->instance(get_class($this), $this);
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
	 * Get the application's router.
	 *
	 * @return \Autarky\Routing\RouterInterface
	 */
	public function getRouter()
	{
		return $this->container->resolve('Autarky\Routing\RouterInterface');
	}

	/**
	 * Get the application's event dispatcher.
	 *
	 * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
	 */
	public function getEventDispatcher()
	{
		if ($this->eventDispatcher === null) {
			try {
				$this->eventDispatcher = $this->container->resolve('Symfony\Component\EventDispatcher\EventDispatcherInterface');
			} catch (ContainerException $e) {
				$this->eventDispatcher = false;
			}
		}

		return $this->eventDispatcher ?: null;
	}

	/**
	 * Get the application's request stack.
	 *
	 * @return \Symfony\Component\HttpFoundation\RequestStack
	 */
	public function getRequestStack()
	{
		return $this->requests;
	}

	public function getStack()
	{
		return $this->stack;
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
	 * Boot a console application.
	 *
	 * @return \Symfony\Component\Console\Application
	 */
	public function bootConsole()
	{
		$this->console = new ConsoleApplication('Autarky', static::VERSION);

		$this->console->setAutarkyApplication($this);

		$this->boot();

		return $this->console;
	}

	/**
	 * Boot the application.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->booted) return;

		$this->registerProviders();
		$this->resolveEnvironment();
		$this->callConfigCallbacks();
		$this->resolveStack();

		$this->booted = true;
	}

	protected function registerProviders()
	{
		foreach ($this->providers as $provider) {
			if (is_string($provider)) {
				$provider = new $provider();
			}
			$this->registerProvider($provider);
		}
	}

	protected function registerProvider(ServiceProvider $provider)
	{
		$provider->setApplication($this);
		$provider->register();

		if ($this->console) {
			$provider->registerConsole($this->console);
		}
	}

	protected function callConfigCallbacks()
	{
		foreach ($this->configCallbacks as $callback) {
			call_user_func($callback, $this);
		}
	}

	protected function resolveStack()
	{
		if ($this->stack !== null) return $this->stack;

		$this->stack = new StackBuilder;

		foreach ($this->middlewares as $middleware) {
			if (!is_array($middleware)) {
				$middleware = [$middleware];
			}
			call_user_func_array([$this->stack, 'push'], $middleware);
		}

		return $this->stack;
	}

	protected function resolveKernel()
	{
		if ($this->kernel !== null) return $this->kernel;

		$kernel = new HttpKernel(
			$this->getRouter(), $this->errorHandler, $this->requests, $this->getEventDispatcher()
		);

		return $this->kernel = $this->resolveStack()
			->resolve($kernel);
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
		if ($request === null) {
			$request = Request::createFromGlobals();
		}

		$response = $this->handle($request);

		return $send ? $response->send() : $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $throw = false)
	{
		$this->boot();

		return $this->resolveKernel()
			->handle($request, $type, $throw);
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
