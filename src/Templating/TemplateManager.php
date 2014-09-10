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

use Autarky\Kernel\Application;
use Autarky\Events\EventDispatcherAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TemplateManager implements EventDispatcherAwareInterface
{
	/**
	 * @var \Autarky\Templating\TemplatingEngineInterface
	 */
	protected $engine;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $eventDispatcher;

	public function __construct(TemplatingEngineInterface $engine)
	{
		$this->engine = $engine;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEventDispatcher(EventDispatcherInterface $dispatcher)
	{
		$this->eventDispatcher = $dispatcher;
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
		$template = $this->getTemplate($name, $context);

		if ($this->eventDispatcher !== null) {
			$this->eventDispatcher->dispatch(
				'autarky.template.rendering: '.$template->getName(),
				new Events\RenderingTemplateEvent($template)
			);
		}

		return $this->engine->render($template);
	}

	protected function getTemplate($name, array $context)
	{
		$template = new Template($name, $context);

		if ($this->eventDispatcher !== null) {
			$this->eventDispatcher->dispatch(
				'autarky.template.creating: '.$template->getName(),
				new Events\CreatingTemplateEvent($template)
			);
		}

		return $template;
	}

	public function creating($name, $handler, $priority = 0)
	{
		$this->addEventListener('creating', $name, $handler, $priority = 0);
	}

	public function rendering($name, $handler, $priority = 0)
	{
		$this->addEventListener('rendering', $name, $handler, $priority = 0);
	}

	protected function addEventListener($event, $name, $handler, $priority = 0)
	{
		if ($this->eventDispatcher === null) {
			throw new \RuntimeException('Cannot register templating event listeners without first setting the EventDispatcher on the TemplateManager.');
		}

		$this->eventDispatcher->addListener("autarky.template.$event: $name", $handler, $priority);
	}
}
