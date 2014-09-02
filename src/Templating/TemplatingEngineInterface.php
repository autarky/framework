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
	 * @param  Template $template
	 *
	 * @return string
	 */
	public function render(Template $template);

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
