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
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Extension that adds session-related functionality.
 *
 * Adds the 'flash' global variable containing flash messages.
 */
class SessionExtension extends Twig_Extension
{
	protected $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function getGlobals()
	{
		return [
			'flash' => $this->session->getFlashBag()->peek('_messages', []),
		];
	}

	public function getName()
	{
		return 'session';
	}
}
