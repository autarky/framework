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
	public function __construct(ReflectionParameter $param, ReflectionFunctionAbstract $func = null)
	{
		parent::__construct($this->makeMessage($param, $func));
	}

	protected function makeMessage(ReflectionParameter $param, ReflectionFunctionAbstract $func = null)
	{
		$pos = $param->getPosition() + 1;

		$name = $param->getName();

		$func = $this->getFunctionName($func ?: $param->getDeclaringFunction());

		return "Unresolvable argument: Argument #{$pos} (\${$name}) of {$func}";
	}

	protected function getFunctionName(ReflectionFunctionAbstract $func)
	{
		if ($func->isClosure()) {
			return 'closure in '.$this->getClosureLocation($func);
		}

		if ($func instanceof ReflectionMethod) {
			return $func->getDeclaringClass()->getName() . '::' . $func->getName();
		}

		return $func->getName().' in '.$func->getFileName();
	}

	protected function getClosureLocation(ReflectionFunctionAbstract $func)
	{
		if ($class = $func->getClosureScopeClass()) {
			$location = $class->getName();
		} else {
			$location = $func->getFileName();
		}

		$startLine = $func->getStartLine();
		$endLine = $func->getEndLine();

		if ($startLine == $endLine) {
			$location .= " on line $startLine";
		} else {
			$location .= " on lines {$startLine}-{$endLine}";
		}

		return $location;
	}
}
