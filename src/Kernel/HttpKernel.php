<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Autarky\Errors\ErrorHandlerInterface;
use Autarky\Routing\RouterInterface;

class HttpKernel implements HttpKernelInterface, TerminableInterface
{
	protected $router;
	protected $errorHandler;
	protected $requests;
	protected $eventDispatcher;

	public function __construct(
		RouterInterface $router,
		ErrorHandlerInterface $errorHandler,
		RequestStack $requests,
		EventDispatcherInterface $eventDispatcher = null
	) {
		$this->router = $router;
		$this->errorHandler = $errorHandler;
		$this->requests = $requests;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		try {
			return $this->innerHandle($request, $type);
		} catch (Exception $exception) {
			if (!$catch) {
				$this->finishRequest($request, $type);
				throw $exception;
			}

			return $this->handleException($exception, $request, $type);
		}
	}

	protected function innerHandle(Request $request, $type)
	{
		$this->requests->push($request);

		if ($this->eventDispatcher !== null) {
			$event = new GetResponseEvent($this, $request, $type);
			$this->eventDispatcher->dispatch(KernelEvents::REQUEST, $event);
			$response = $event->getResponse() ?: $this->router->dispatch($request);
		} else {
			$response = $this->router->dispatch($request);
		}

		return $this->filterResponse($response, $request, $type);
	}

	protected function handleException(Exception $exception, Request $request, $type)
	{
		if ($this->eventDispatcher !== null) {
			$event = new GetResponseForExceptionEvent($this, $request, $type, $exception);
			$this->eventDispatcher->dispatch(KernelEvents::EXCEPTION, $event);
			$response = $event->getResponse() ?: $this->errorHandler->handle($exception);
		} else {
			$response = $this->errorHandler->handle($exception);
		}

		try {
			return $this->filterResponse($response, $request, $type);
		} catch (Exception $e) {
			return $response;
		}
	}

	protected function filterResponse(Response $response, Request $request, $type)
	{
		if ($this->eventDispatcher !== null) {
			$event = new FilterResponseEvent($this, $request, $type, $response);
			$this->eventDispatcher->dispatch(KernelEvents::RESPONSE, $event);
			$response = $event->getResponse();
		}

		$response->prepare($request);

		$this->finishRequest($request, $type);

		return $response;
	}

	protected function finishRequest(Request $request, $type)
	{
		if ($this->eventDispatcher !== null) {
			$event = new FinishRequestEvent($this, $request, $type);
			$this->eventDispatcher->dispatch(KernelEvents::FINISH_REQUEST, $event);
		}

		$this->requests->pop();
	}

	/**
	 * {@inheritdoc}
	 */
	public function terminate(Request $request, Response $response)
	{
		if ($this->eventDispatcher !== null) {
			$event = new PostResponseEvent($this, $request, $response);
			$this->eventDispatcher->dispatch(KernelEvents::TERMINATE, $event);
		}
	}
}
