<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

use Autarky\Container\ContainerInterface;

/**
 * Extension to provide richer partial views. Adds the partial() function to all
 * twig templates. See PartialExtension::getPartial() for method signature.
 */
class PartialExtension extends Twig_Extension
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getFunctions()
	{
		return [
			new Twig_SimpleFunction('partial', [$this, 'getPartial'], ['is_safe' => ['html']]),
		];
	}

	/**
	 * The implementation of the twig partial() function.
	 *
	 * @param  string $name   'Namespace\Class' or 'Class:method'. If method is
	 *                        left out, defaults to 'render'
	 * @param  array  $params
	 *
	 * @return string
	 */
	public function getPartial($name, array $params = array())
	{
		list($class, $method) = \Autarky\splitclm($name, 'handle');
		$obj = $this->container->resolve($class);
		return call_user_func_array([$obj, $method], $params);
	}

	public function getName()
	{
		return 'partial';
	}
}
