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

use Symfony\Component\EventDispatcher\Event;

class TemplateEvent extends Event
{
	/**
	 * @var Template
	 */
	protected $template;

	public function __construct(Template $template)
	{
		$this->template = $template;
	}

	/**
	 * Get the template instance.
	 *
	 * @return Template
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Get the template's context instance.
	 *
	 * @return \Autarky\Templating\TemplateContext
	 */
	public function getContext()
	{
		return $this->template->getContext();
	}
}
