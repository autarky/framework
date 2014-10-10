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
		$dic = $this->app->getContainer();

		foreach ($extensions as $dependency => $extension) {
			if (is_string($dependency) && !$dic->isBound($dependency)) {
				continue;
			}

			$this->twig->addExtension(
				$dic->resolve(__NAMESPACE__."\Extension\\$extension")
			);
		}
	}

	public function loadUserExtensions(array $extensions)
	{
		$dic = $this->app->getContainer();

		foreach ($extensions as $extension) {
			if (is_string($extension)) {
				$extension = $dic->resolve($extension);
			}

			$this->twig->addExtension($extension);
		}
	}
}
