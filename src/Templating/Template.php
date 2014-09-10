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

class Template
{
	/**
	 * The name of the template - usually its file path.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The context of the template.
	 *
	 * @var TemplateContext
	 */
	protected $context;

	public function __construct($name, array $context = array())
	{
		$this->name = $name;
		$this->context = new TemplateContext($context);
	}

	/**
	 * Get the name of the template.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the template's context.
	 *
	 * @return TemplateContext
	 */
	public function getContext()
	{
		return $this->context;
	}
}
