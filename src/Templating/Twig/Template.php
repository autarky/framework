<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
		$this->template->getContext()->replace($context);

		$this->eventDispatcher->dispatch(
			'autarky.template.rendering: '.$this->template->getName(),
			new TemplateEvent($this->template)
		);

		return parent::display($this->template->getContext()->toArray(), $blocks);
	}
}
