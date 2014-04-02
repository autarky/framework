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

interface TemplatingEngineInterface extends NamespacedResourceResolverInterface
{
	public function render($view, array $params = array());
}
