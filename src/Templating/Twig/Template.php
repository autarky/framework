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
use Autarky\Events\EventDispatcherAwareTrait;

/**
 * Extension of the Twig_Template class to enable event dispatching as well as
 * working with template context objects.
 */
abstract class Template extends \Twig_Template implements EventDispatcherAwareInterface
{
	use EventDispatcherAwareTrait;

	/**
	 * @var \Autarky\Templating\Template
	 */
	protected $template;

	/**
	 * Set the Autarky Template instance.
	 *
	 * @param \Autarky\Templating\Template $template
	 */
	public function setTemplate(\Autarky\Templating\Template $template)
	{
		$this->template = $template;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(array $context, array $blocks = array())
	{
		$this->template->getContext()
			->replace($context);

		if ($this->eventDispatcher !== null) {
			$this->eventDispatcher->dispatch(
				'template.rendering: '.$this->template->getName(),
				new TemplateEvent($this->template)
			);
		}

		parent::display($this->template->getContext()->toArray(), $blocks);
	}
}
