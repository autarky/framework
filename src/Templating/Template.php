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
	protected $name;
	protected $context;

	public function __construct($name, array $context = array())
	{
		$this->name = $name;
		$this->context = new TemplateContext($context);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getContext()
	{
		return $this->context;
	}
}
