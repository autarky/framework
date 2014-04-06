<?php
namespace Autarky\Templating\Twig;

use Twig_Environment;
use Autarky\Kernel\Application;

class ExtensionsLoader
{
	public function __construct(Twig_Environment $twig, Application $app)
	{
		$this->twig = $twig;
		$this->app = $app;
	}

	public function loadCoreExtensions(array $extensions)
	{
		foreach ($extensions as $extension) {
			$extension = $this->app->getContainer()->resolve(__NAMESPACE__.'\\'.$extension);
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
