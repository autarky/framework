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

use Autarky\ConfiguratorInterface;
use Autarky\Config\ConfigInterface;

/**
 * This configurator reads the app/config/routes file and mounts it onto the
 * root path of your application.
 */
class DefaultRouteConfigurator implements ConfiguratorInterface
{
	/**
	 * The config instance.
	 *
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * The router instance.
	 *
	 * @var RouterInterface
	 */
	protected $router;

	/**
	 * Constructor.
	 *
	 * @param RouterInterface $router
	 * @param ConfigInterface $config
	 */
	public function __construct(
		RouterInterface $router,
		ConfigInterface $config
	) {
		$this->router = $router;
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configure()
	{
		$this->router->mount($this->config->get('routes'), '/');
	}
}
