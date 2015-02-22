<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky;

use Closure;
use SplPriorityQueue;
use SplDoublyLinkedList;
use Stack\Builder as StackBuilder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Autarky\Config\ConfigInterface;
use Autarky\Console\Application as ConsoleApplication;
use Autarky\Container\ContainerInterface;
use Autarky\Errors\ErrorHandlerManagerInterface;
use Autarky\Kernel\HttpKernel;

/**
 * The main application of the framework.
 */
class Application implements HttpKernelInterface
{
	/**
	 * The framework version.
	 */
	const VERSION = '0.8.1';

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
	 * @var \Autarky\Errors\ErrorHandlerManagerInterface
	 */
	protected $errorHandler;

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
	protected $booting = false;

	/**
	 * @var boolean
	 */
	protected $booted = false;

	/**
	 * @var \SplDoublyLinkedList
	 */
	protected $configurators;

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requests;

	/**
	 * Construct a new application instance.
	 *
	 * @param \Closure|string   $environment
	 * @param Provider[] $providers
	 */
	public function __construct($environment, array $providers)
	{
		$this->middlewares = new SplPriorityQueue;
		$this->configurators = new SplDoublyLinkedList;
		$this->requests = new RequestStack;
		$this->setEnvironment($environment);
		
		foreach ($providers as $provider) {
			$class = is_object($provider) ? get_class($provider) : (string) $provider;
			$this->providers[$class] = $provider;
		}
	}

	/**
	 * Push a configurator on top of the stack. The configurators will be
	 * executed when the application is booted. If the application is already
	 * booted, the configurator will be executed at once.
	 *
	 * @param  callable|string|ConfiguratorInterface $configurator
	 *
	 * @return void
	 */
	public function config($configurator)
	{
		if ($this->booted) {
			$this->invokeConfigurator($configurator);
		} else {
			$this->configurators->push($configurator);
		}
	}

	/**
	 * Invoke a single configurator.
	 *
	 * @param  callable|string|ConfiguratorInterface $configurator
	 *
	 * @return void
	 */
	protected function invokeConfigurator($configurator)
	{
		if (is_callable($configurator)) {
			call_user_func($configurator, $this);
			return;
		}

		if (is_string($configurator)) {
			$configurator = $this->container->resolve($configurator);
		}

		if ($configurator instanceof ConfiguratorInterface) {
			$configurator->configure();
		} else {
			throw new \UnexpectedValueException('Invalid configurator');
		}
	}

	/**
	 * Set the environment of the application. Has to be called before boot().
	 *
	 * @param string $environment
	 */
	public function setEnvironment($environment)
	{
		if ($this->booting) {
			throw new \RuntimeException('Cannot set environment after application has booted');
		}

		if ($environment instanceof Closure) {
			$environment = call_user_func($environment);
		}

		if (!is_string($environment)) {
			throw new \InvalidArgumentException('Environment must be a string');
		}

		$this->environment = $environment;
	}

	/**
	 * Get the current environment.
	 *
	 * @return string
	 */
	public function getEnvironment()
	{
		if (!$this->booting) {
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

	/**
	 * Set the application's error handler.
	 *
	 * @param ErrorHandlerManagerInterface $errorHandler
	 */
	public function setErrorHandler(ErrorHandlerManagerInterface $errorHandler)
	{
		$this->errorHandler = $errorHandler;
	}

	/**
	 * Get the application's error handler.
	 *
	 * @return ErrorHandlerManagerInterface
	 */
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
		$container->instance('Autarky\Application', $this);
		$container->instance('Symfony\Component\HttpFoundation\RequestStack', $this->requests);
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
	 * Get the application's request stack.
	 *
	 * @return \Symfony\Component\HttpFoundation\RequestStack
	 */
	public function getRequestStack()
	{
		return $this->requests;
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

		$this->booting = true;

		$this->registerProviders();

		foreach ($this->configurators as $configurator) {
			$this->invokeConfigurator($configurator);
		}

		$this->resolveStack();

		$this->booted = true;
	}

	/**
	 * Register all of the application's service providers.
	 *
	 * @return void
	 */
	protected function registerProviders()
	{
		foreach ($this->providers as $provider) {
			if (is_string($provider)) {
				$provider = new $provider();
			}
			$this->registerProvider($provider);
		}
	}

	/**
	 * Register a single service provider.
	 *
	 * @param  Provider $provider
	 *
	 * @return void
	 */
	protected function registerProvider(Provider $provider)
	{
		$provider->setApplication($this);
		$provider->register();

		if ($this->console) {
			$provider->registerConsole($this->console);
		}
	}

	/**
	 * Resolve the stack builder.
	 *
	 * @return \Stack\Builder
	 */
	protected function resolveStack()
	{
		if ($this->stack !== null) {
			return $this->stack;
		}

		$this->stack = new StackBuilder;

		foreach ($this->middlewares as $middleware) {
			call_user_func_array([$this->stack, 'push'], (array) $middleware);
		}

		return $this->stack;
	}

	/**
	 * Resolve the HTTP kernel.
	 *
	 * @return HttpKernelInterface
	 */
	protected function resolveKernel()
	{
		if ($this->kernel !== null) {
			return $this->kernel;
		}

		$class = 'Symfony\Component\EventDispatcher\EventDispatcherInterface';
		$eventDispatcher = $this->container->isBound($class) ?
			$this->container->resolve($class) : null;

		$kernel = new HttpKernel(
			$this->getRouter(), $this->requests, $this->errorHandler, $eventDispatcher
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
	 * @see \Autarky\Container\ContainerInterface::define()
	 */
	public function define()
	{
		return call_user_func_array([$this->container, 'define'], func_get_args());
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

	/**
	 * @see \Autarky\Container\ContainerInterface::params()
	 */
	public function params()
	{
		return call_user_func_array([$this->container, 'params'], func_get_args());
	}

	/**
	 * @see \Autarky\Container\ContainerInterface::invoke()
	 */
	public function invoke()
	{
		return call_user_func_array([$this->container, 'invoke'], func_get_args());
	}

	/**
	 * @see \Autarky\Routing\RouterInterface::addRoute()
	 */
	public function route()
	{
		return call_user_func_array([$this->getRouter(), 'addRoute'], func_get_args());
	}
}
