<?php
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
