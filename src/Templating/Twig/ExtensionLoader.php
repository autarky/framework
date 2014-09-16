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

use Twig_Environment;

use Autarky\Kernel\Application;

/**
 * Class responsible for adding Twig extensions, both user-defined ones and
 * those that are part of the core framework.
 */
class ExtensionLoader
{
	/**
	 * @var Twig_Environment
	 */
	protected $twig;

	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Twig_Environment $twig, Application $app)
	{
		$this->twig = $twig;
		$this->app = $app;
	}

	public function loadCoreExtensions(array $extensions)
	{
		$providers = $this->app->getConfig()->get('app.providers', []);

		foreach ($extensions as $dependency => $extension) {
			if (is_string($dependency) && !in_array($dependency, $providers)) continue;

			$extension = $this->app->getContainer()
				->resolve(__NAMESPACE__.'\\Extension\\'.$extension);
			$this->twig->addExtension($extension);
		}
	}

	public function loadUserExtensions(array $extensions)
	{
		foreach ($extensions as $extension) {
			if (is_string($extension)) {
				$extension = $this->app->getContainer()->resolve($extension);
			}

			$this->twig->addExtension($extension);
		}
	}
}
