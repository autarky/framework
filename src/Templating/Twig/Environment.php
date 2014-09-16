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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig_LoaderInterface;

use Autarky\Events\EventDispatcherAwareInterface;
use Autarky\Events\EventDispatcherAwareTrait;
use Autarky\Templating\TemplateEvent;

class Environment extends \Twig_Environment implements EventDispatcherAwareInterface
{
	use EventDispatcherAwareTrait;

	public function __construct(Twig_LoaderInterface $loader = null, $options = array())
	{
		$options['base_template_class'] = 'Autarky\Templating\Twig\Template';

		parent::__construct($loader, $options);
	}

	public function loadTemplate($path, $index = null)
	{
		$name = $this->normalizeName($path);

		$template = new \Autarky\Templating\Template($name);

		if ($this->eventDispatcher !== null) {
			$this->eventDispatcher->dispatch('template.creating: '.$name,
				new TemplateEvent($template));
		}

		$twigTemplate = parent::loadTemplate($path, $index);
		$twigTemplate->setTemplate($template);
		$twigTemplate->setEventDispatcher($this->eventDispatcher);

		return $twigTemplate;
	}

	protected function normalizeName($path)
	{
		return str_replace(['.html.twig', '.twig'], '', $path);
	}
}
