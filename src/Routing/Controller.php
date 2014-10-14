<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

use Autarky\Container\ContainerAwareInterface;

/**
 * Base web controller class for convenience and accessibility to newcomers.
 * 
 * Although this class is named "Controller", this does not mean that *only*
 * classes that extend this class can be mapped to a route in the framework. Any
 * plain PHP class with a method that returns a string or a Response object can
 * be used as a controller. This class is simply a convenience class, with a lot
 * of common utility methods already implemented, like dealing with the session,
 * rendering templates, returning various responses, and more.
 *
 * If you are unable to extend the controller class for any reason, you can just
 * use the trait instead:
 *
 * class MyController extends SomethingElse
 * {
 *     use \Autarky\Routing\ControllerTrait;
 * }
 */
abstract class Controller implements ContainerAwareInterface
{
	/**
	 * Import the trait.
	 */
	use ControllerTrait;
}
