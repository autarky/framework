<?php
namespace Autarky\Container;

use Autarky\Kernel\ServiceProvider;

class ContainerProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->setContainer(new Container);
	}
}
