<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

use Autarky\Routing\UrlGenerator;

/**
 * Extension for url generating functionality in templates.
 *
 * Parts shamelessly stolen from Symfony 2.
 */
class UrlGenerationExtension extends Twig_Extension
{
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * Constructor.
	 *
	 * @param UrlGenerator $urlGenerator
	 */
	public function __construct(UrlGenerator $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('url', [$this, 'getUrl'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
			new Twig_SimpleFunction('asset', [$this, 'getAsset']),
		);
	}

	/**
	 * Implementation of the `url(route)` twig function.
	 *
	 * @param  string  $name       Route name
	 * @param  array   $parameters Route parameters
	 * @param  boolean $relative   Whether to generate an absolute or relative URL.
	 *
	 * @return string
	 */
	public function getUrl($name, $parameters = array(), $relative = false)
	{
		return $this->urlGenerator->getRouteUrl($name, $parameters, $relative);
	}

	/**
	 * Implementation of the `asset(path)` twig function.
	 *
	 * @param  string  $path     Path to the asset, relative to the webroot or asset root.
	 * @param  boolean $relative Whether to generate an absolute or relative URL.
	 *
	 * @return [type]            [description]
	 */
	public function getAsset($path, $relative = false)
	{
		return $this->urlGenerator->getAssetUrl($path, $relative);
	}

	/**
	 * Determines at compile time whether the generated URL will be safe and thus
	 * saving the unneeded automatic escaping for performance reasons.
	 *
	 * The URL generation process percent encodes non-alphanumeric characters. So there is no risk
	 * that malicious/invalid characters are part of the URL. The only character within an URL that
	 * must be escaped in html is the ampersand ("&") which separates query params. So we cannot mark
	 * the URL generation as always safe, but only when we are sure there won't be multiple query
	 * params. This is the case when there are none or only one constant parameter given.
	 * E.g. we know beforehand this will be safe:
	 * - path('route')
	 * - path('route', {'param': 'value'})
	 * But the following may not:
	 * - path('route', var)
	 * - path('route', {'param': ['val1', 'val2'] }) // a sub-array
	 * - path('route', {'param1': 'value1', 'param2': 'value2'})
	 * If param1 and param2 reference placeholder in the route, it would still be safe. But we don't know.
	 *
	 * @param \Twig_Node $argsNode The arguments of the path/url function
	 *
	 * @return array An array with the contexts the URL is safe
	 *
	 * @author Fabien Potencier <fabien@symfony.com>
	 */
	public function isUrlGenerationSafe(\Twig_Node $argsNode)
	{
		// support named arguments
		$paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
			$argsNode->hasNode(1) ? $argsNode->getNode(1) : null
		);

		if (null === $paramsNode || $paramsNode instanceof \Twig_Node_Expression_Array && count($paramsNode) <= 2 &&
			(!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof \Twig_Node_Expression_Constant)
		) {
			return array('html');
		}

		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'routing';
	}
}
