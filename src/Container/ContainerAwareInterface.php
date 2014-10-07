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

/**
 * Interface for container-aware classes.
 *
 * A container-aware class is a class that is aware of the framework's service
 * container, and can thereby both bind objects onto the container and resolve
 * items from it.
 *
 * Container-aware classes should be used sparingly and usually only at the top
 * level of your class hierarchy, as they tightly couple your class to this
 * framework's service container. Web controllers are a common use case for
 * container-aware classes, as controllers often need to resolve many types of
 * services - the session, router, templating engine and so on.
 *
 * Other parts of the framework can do an "instanceof ContainerAwareInterface"
 * to determine if the container should be set on the class or not, without
 * requiring it to be specified in the constructor and without having to
 * resolve to static/global function calls.
 */
interface ContainerAwareInterface
{
	/**
	 * Set the container instance.
	 *
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container);
}
