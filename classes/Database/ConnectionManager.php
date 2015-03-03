<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Database;

use Autarky\Config\ConfigInterface;
use PDO;

/**
 * Manager for multiple database connections in the form of PDO instances and
 * configuration data.
 */
class ConnectionManager
{
	/**
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * @var ConnectionFactory
	 */
	protected $factory;

	/**
	 * The default connection to use
	 *
	 * @var string
	 */
	protected $defaultConnection;

	/**
	 * PDO instances.
	 *
	 * @var \PDO[]
	 */
	protected $instances = [];

	/**
	 * Constructor.
	 *
	 * @param ConfigInterface $config
	 * @param ConnectionFactoryInterface $config
	 * @param string|null $defaultConnection If null, "database.connection" is
	 * retrieved from $config
	 */
	public function __construct(
		ConfigInterface $config,
		ConnectionFactoryInterface $factory,
		$defaultConnection = null
	) {
		$this->config = $config;
		$this->factory = $factory;
		$this->defaultConnection = $defaultConnection ?: $config->get('database.connection');
	}

	/**
	 * Get a PDO instance.
	 *
	 * @param  string|null $connection Null fetches the default connection.
	 *
	 * @return \PDO
	 */
	public function getPdo($connection = null)
	{
		if ($connection === null) {
			$connection = $this->defaultConnection;
		}

		if (isset($this->instances[$connection])) {
			return $this->instances[$connection];
		}

		$config = $this->getConnectionConfig($connection);

		return $this->instances[$connection] = $this->factory->makePdo($config, $connection);
	}

	/**
	 * Get the configuration array for a specific connection.
	 *
	 * @param  string $connection The name of the connection.
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException If connection is not defined
	 */
	public function getConnectionConfig($connection = null)
	{
		if ($connection === null) {
			$connection = $this->defaultConnection;
		}

		$config = $this->config->get("database.connections.$connection");

		if (!$config) {
			if (!is_string($connection)) {
				$connection = gettype($connection);
			}

			throw new \InvalidArgumentException("No config found for connection: $connection");
		}

		return $config;
	}
}
