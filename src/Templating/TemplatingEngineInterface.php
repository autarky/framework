<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating;

use Autarky\Support\NamespacedResourceResolverInterface;

/**
 * Interface for templating engines.
 *
 * Templating engines in Autarky must support namespaces.
 *
 * @see  \Autarky\Support\NamespacedResourceResolverInterface
 */
interface TemplatingEngineInterface extends NamespacedResourceResolverInterface
{
	/**
	 * Render a template.
	 *
	 * @param  string $name   Name of the template.
	 * @param  array  $context
	 *
	 * @return string
	 */
	public function render($name, array $context = array());

	/**
	 * Register a context handler.
	 *
	 * The context handler is invoked when the template is loaded, allowing you
	 * to automatically add data to a template's context when it is loaded,
	 * without having to explicitly add it in the class that invokes render().
	 * Context data that is passed via render() overrides any context handler
	 * data. More than one context handler may be added to the same template.
	 *
	 * @param  string $template
	 * @param  mixed  $handler
	 *
	 * @return void
	 */
	public function registerContextHandler($template, $handler);

	/**
	 * Add a global variable that is available in all templates.
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 *
	 * @return void
	 */
	public function addGlobalVariable($name, $value);
}
