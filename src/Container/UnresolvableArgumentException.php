<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container;

use ReflectionParameter;
use ReflectionMethod;
use ReflectionFunctionAbstract;

class UnresolvableArgumentException extends ContainerException
{
	public function __construct(ReflectionParameter $param)
	{
		$pos = $param->getPosition() + 1;

		$name = $param->getName();

		$func = $this->getFunctionName($param->getDeclaringFunction());

		$message = "Unresolvable argument: Argument #{$pos} (\${$name}) of {$func}";

		parent::__construct($message);
	}

	protected function getFunctionName(ReflectionFunctionAbstract $reflFunc)
	{
		$func = '';

		if ($reflFunc instanceof ReflectionMethod) {
			$func .= $reflFunc->getDeclaringClass()->getName().'::';
		}

		return $func . $reflFunc->getName();
	}
}
