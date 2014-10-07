<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Exception;

/**
 * Exception that is thrown when one tries to resolve a class that is defined as
 * internal.
 */
class ResolvingInternalException extends ResolvingException
{
}
