<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Http;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class CookieMiddleware implements HttpKernelInterface
{
	protected $kernel;
	protected $cookieQueue;

	public function __construct(HttpKernelInterface $kernel, CookieQueue $cookieQueue)
	{
		$this->kernel = $kernel;
		$this->cookieQueue = $cookieQueue;
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$response = $this->kernel->handle($request, $type, $catch);

		if ($type === HttpKernelInterface::MASTER_REQUEST) {
			foreach ($this->cookieQueue->all() as $key => $value) {
				$response->headers->setCookie($key, $value);
			}
		}

		return $response;
	}
}
