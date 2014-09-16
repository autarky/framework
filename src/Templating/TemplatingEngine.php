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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Autarky\Events\EventDispatcherAwareInterface;

class TemplatingEngine implements EventDispatcherAwareInterface
{
	protected $twig;
	protected $eventDispatcher;

	public function __construct(Twig\Environment $twig)
	{
		$this->twig = $twig;
	}

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
		$this->twig->setEventDispatcher($eventDispatcher);
	}

	/**
	 * Render a template.
	 *
	 * @param  string $name
	 * @param  array  $context
	 *
	 * @return string
	 */
	public function render($name, array $context = array())
	{
		return $this->twig->loadTemplate($name)
			->render($context);
	}

	/**
	 * Register an event listener for when a template is being created.
	 *
	 * @param  string           $name
	 * @param  \Closure|string  $handler
	 * @param  integer          $priority
	 *
	 * @return void
	 */
	public function creating($name, $handler, $priority = 0)
	{
		$this->addEventListener('creating', $name, $handler, $priority = 0);
	}

	/**
	 * Register an event listener for when a template is being rendered.
	 *
	 * @param  string           $name
	 * @param  \Closure|string  $handler
	 * @param  integer          $priority
	 *
	 * @return void
	 */
	public function rendering($name, $handler, $priority = 0)
	{
		$this->addEventListener('rendering', $name, $handler, $priority = 0);
	}

	protected function addEventListener($event, $name, $handler, $priority = 0)
	{
		if ($this->eventDispatcher === null) {
			throw new \RuntimeException('Cannot register templating event listeners without first setting the EventDispatcher on the TemplateManager.');
		}

		$this->eventDispatcher->addListener("template.$event: $name", $handler, $priority);
	}

	/**
	 * Add a global variable.
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 *
	 * @return void
	 */
	public function addGlobal($name, $value)
	{
		$this->twig->addGlobal($name, $value);
	}

	/**
	 * Register a template namespace.
	 *
	 * @param  string $namespace
	 * @param  string $location
	 *
	 * @return void
	 */
	public function addNamespace($namespace, $location)
	{
		$this->twig->getLoader()
			->addPath($location, $namespace);
	}
}
