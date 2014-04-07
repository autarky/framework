<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Support;

/**
 * Interface for namespaced resource resolvers.
 *
 * A big part of Autarky is namespacing of resources like config files and
 * templates. This allows you to split your application up and spread out these
 * resources instead of hoarding them in one single config and templates
 * directory, allows for more modular development in general and lets package
 * authors add their own resources to the framework seamlessly.
 *
 * This interface does not currently define a method that resolves a resource
 * from a namespace, but this behaviour is still expected of the classes
 * implementing this interface.
 *
 * When a resource is resolved by name, they are by default fetched from the
 * global namespace. If a ':' is present in the name of the resource being
 * resolved, the bit before the ':' defines the namespace that should be fetched
 * from while the bit after is the name of the resource.
 */
interface NamespacedResourceResolverInterface
{
	/**
	 * Add a namespace to the resolver.
	 *
	 * @param string $namespace
	 * @param string $location
	 */
	public function addNamespace($namespace, $location);
}
