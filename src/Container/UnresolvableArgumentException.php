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
		parent::__construct($this->makeMessage($param));
	}

	protected function makeMessage(ReflectionParameter $param)
	{
		$pos = $param->getPosition() + 1;

		$name = $param->getName();

		$func = $this->getFunctionName($param->getDeclaringFunction());

		return "Unresolvable argument: Argument #{$pos} (\${$name}) of {$func}";
	}

	protected function getFunctionName(ReflectionFunctionAbstract $reflFunc)
	{
		if ($reflFunc->isClosure()) {
			if ($class = $reflFunc->getClosureScopeClass()) {
				$location = $class->getName();
			} else {
				$location = $reflFunc->getFileName();
			}

			$startLine = $reflFunc->getStartLine();
			$endLine = $reflFunc->getEndLine();

			if ($startLine == $endLine) {
				$lines = "line $startLine";
			} else {
				$lines = "lines {$startLine}-{$endLine}";
			}

			return "closure in $location on $lines";
		}

		$func = '';

		if ($reflFunc instanceof ReflectionMethod) {
			$func .= $reflFunc->getDeclaringClass()->getName().'::';
		}

		return $func . $reflFunc->getName();
	}
}
