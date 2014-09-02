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

use Autarky\Kernel\Application;

class TemplateManager
{
	/**
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	/**
	 * @var \Autarky\Templating\TemplatingEngineInterface
	 */
	protected $engine;

	/**
	 * @var array
	 */
	protected $contextHandlers = [];

	public function __construct(Application $app, TemplatingEngineInterface $engine)
	{
		$this->app = $app;
		$this->engine = $engine;
	}

	public function render($name, array $context = array())
	{
		$template = $this->getTemplate($name, $context);

		$this->callContextHandlers($template);

		return $this->engine->render($template);
	}

	protected function getTemplate($name, array $context)
	{
		return new Template($name, $context);
	}

	protected function callContextHandlers($template)
	{
		if (!array_key_exists($template->getName(), $this->contextHandlers)) {
			return;
		}

		foreach ($this->contextHandlers[$template->getName()] as $handler) {
			$this->callContextHandler($handler, $template);
		}
	}

	protected function callContextHandler($handler, $template)
	{
		if ($handler instanceof \Closure) {
			return $handler($template);
		}

		list($class, $method) = \Autarky\splitclm($handler, 'getContext');
		$obj = $this->app->resolve($class);

		return $obj->$method($template);
	}

	public function registerContextHandler($template, $handler)
	{
		$this->contextHandlers[$template][] = $handler;
	}
}
