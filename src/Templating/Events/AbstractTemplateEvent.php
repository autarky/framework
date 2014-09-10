<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating\Events;

use Symfony\Component\EventDispatcher\Event;
use Autarky\Templating\Template;

abstract class AbstractTemplateEvent extends Event
{
	protected $template;

	public function __construct(Template $template)
	{
		$this->template = $template;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getContext()
	{
		return $this->template->getContext();
	}
}
