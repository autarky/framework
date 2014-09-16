<?php
namespace Autarky\Templating\Twig;

use Twig_LoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Autarky\Events\EventDispatcherAwareInterface;
use Autarky\Templating\TemplateEvent;

class Environment extends \Twig_Environment implements EventDispatcherAwareInterface
{
	protected $eventDispatcher;

	public function __construct(Twig_LoaderInterface $loader = null, $options = array())
	{
		$options['base_template_class'] = 'Autarky\Templating\Twig\Template';
		parent::__construct($loader, $options);
	}

	public function setEventDispatcher(EventDispatcherInterface $dispatcher)
	{
		$this->eventDispatcher = $dispatcher;
	}

	public function loadTemplate($name, $index = null)
	{
		$template = new \Autarky\Templating\Template($name);
		$this->eventDispatcher->dispatch('autarky.template.creating: '.$name,
			new TemplateEvent($template));
		$twigTemplate = parent::loadTemplate($name, $index);
		$twigTemplate->setTemplate($template);
		$twigTemplate->setEventDispatcher($this->eventDispatcher);
		return $twigTemplate;
	}
}
