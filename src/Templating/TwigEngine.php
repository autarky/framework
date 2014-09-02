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

use Twig_Environment;

use Autarky\Templating\Twig\FileLoader;
use Autarky\Templating\Twig\ExtensionsLoader;

/**
 * Templating engine utilizing Twig.
 */
class TwigEngine implements TemplatingEngineInterface
{
	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	public function __construct(Twig_Environment $env)
	{
		$this->twig = $env;
	}

	/**
	 * Get the Twig environment.
	 *
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		return $this->twig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(Template $template)
	{
		return $this->twig->loadTemplate($template->getName())
			->render($template->getContext()->toArray());
	}

	/**
	 * {@inheritdoc}
	 */
	public function addGlobalVariable($name, $value)
	{
		$this->twig->addGlobal($name, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($namespace, $location)
	{
		$this->twig->getLoader()
			->addPath($location, $namespace);
	}
}
