<?php
namespace Autarky\Container\Factory;

use Autarky\Container\ContainerInterface;

interface FactoryInterface
{
	public function invoke(ContainerInterface $container, array $params = array());
}
