<?php
namespace Autarky\Templating\Twig;

use Autarky\Templating\TemplateEvent;
use Autarky\Events\EventDispatcherAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class Template extends \Twig_Template
{
	protected $template;
	protected $eventDispatcher;

	public function setTemplate(\Autarky\Templating\Template $template)
	{
		$this->template = $template;
	}

	public function setEventDispatcher(EventDispatcherInterface $dispatcher)
	{
		$this->eventDispatcher = $dispatcher;
	}

	public function display(array $context, array $blocks = array())
	{
		$context = $this->template->getContext();
		foreach ($context as $key => $value) {
			$context->$key = $value;
		}

		$this->eventDispatcher->dispatch(
			'autarky.template.rendering: '.$this->template->getName(),
			new TemplateEvent($this->template)
		);

		return parent::display($this->template->getContext()->toArray(), $blocks);
	}
}
