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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\Event;

use Autarky\Container\ContainerAwareTrait;

/**
 * Trait for controller functionality. Any class that implements this trait
 * should also implement the interface Autarky\Container\ContainerAwareInterface
 * - the interface's methods are implemented by the trait, but you still need to
 * implement the interface on the class.
 */
trait ControllerTrait
{
	/**
	 * 
	 */
	use ContainerAwareTrait;

	/**
	 * Render a template.
	 *
	 * @param  string $name Name of the view.
	 * @param  array  $data Data to pass to the view.
	 *
	 * @return string
	 */
	protected function render($name, array $data = array())
	{
		return $this->container->resolve('Autarky\Templating\TemplatingEngine')
			->render($name, $data);
	}

	/**
	 * Render a template.
	 *
	 * @param  string $name Name of the view.
	 * @param  array  $data Data to pass to the view.
	 *
	 * @return string
	 *
	 * @deprecated Deprecated in favour of render()
	 */
	protected function view($name, array $data = array())
	{
		return $this->render($name, $data);
	}

	/**
	 * Generate the URL to a route.
	 *
	 * @param  string $name   Name of the route.
	 * @param  array  $params Route parameters.
	 *
	 * @return string
	 */
	protected function url($name, array $params = array())
	{
		return $this->container->resolve('Autarky\Routing\UrlGenerator')
			->getRouteUrl($name, $params);
	}

	/**
	 * Get the session manager.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected function getSession()
	{
		return $this->container->resolve('Symfony\Component\HttpFoundation\Session\Session');
	}

	/**
	 * Flash something to the session.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 *
	 * @return void
	 */
	protected function flash($key, $value)
	{
		$this->getSession()
			->getFlashBag()
			->set($key, $value);
	}

	/**
	 * Flash an array of messages to the session.
	 *
	 * @param  array  $messages
	 *
	 * @return void
	 */
	protected function flashMessages($messages)
	{
		$flashBag = $this->getSession()
			->getFlashBag();

		foreach ((array) $messages as $message) {
			$flashBag->add('_messages', $message);
		}
	}

	/**
	 * Flash input to session.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request $request Optional
	 *
	 * @return void
	 */
	protected function flashInput(Request $request = null)
	{
		if ($request === null) {
			$request = $this->container
				->resolve('Symfony\Component\HttpFoundation\RequestStack')
				->getCurrentRequest();
		}

		$this->flash('_old_input', $request->request->all());
	}

	/**
	 * Get old input flashed to the session.
	 *
	 * @return array
	 */
	protected function getOldInput()
	{
		return $this->getSession()
			->getFlashBag()
			->peek('_old_input', []);
	}

	/**
	 * Get the event dispatcher instance.
	 *
	 * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected function getEventDispatcher()
	{
		return $this->container->resolve('Symfony\Component\EventDispatcher\EventDispatcherInterface');
	}

	/**
	 * Dispatch an event.
	 *
	 * @param  string $name
	 * @param  Event  $event
	 *
	 * @return mixed
	 */
	protected function dispatchEvent($name, Event $event)
	{
		return $this->getEventDispatcher()
			->dispatch($name, $event);
	}

	/**
	 * Log a message.
	 *
	 * @param  string $level   https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php
	 * @param  string $message
	 * @param  array  $context
	 *
	 * @return void
	 */
	protected function log($level, $message, array $context = array())
	{
		$this->getLogger()
			->log($level, $message, $context);
	}

	/**
	 * Get the logger instance.
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function getLogger()
	{
		return $this->container->resolve('Psr\Log\LoggerInterface');
	}

	/**
	 * Create a response.
	 *
	 * @param  string  $content
	 * @param  integer $statusCode
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function response($content, $statusCode = 200)
	{
		return new Response($content, $statusCode);
	}

	/**
	 * Create a redirect response.
	 *
	 * @param  string $name   Name of the route to redirect to
	 * @param  array  $params Route parameters
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	protected function redirect($name, array $params = array())
	{
		return new RedirectResponse($this->url($name, $params));
	}

	/**
	 * Create a JSON response.
	 *
	 * @param  array   $data
	 * @param  integer $statusCode
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	protected function json(array $data, $statusCode = 200)
	{
		return new JsonResponse($data, $statusCode);
	}
}
