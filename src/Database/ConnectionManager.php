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

use PDO;

use Autarky\Config\ConfigInterface;

/**
 * Manager for multiple database connections in the form of PDO instances and
 * configuration data.
 */
class ConnectionManager
{
	/**
	 * @var \Autarky\Config\ConfigInterface
	 */
	protected $config;

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
	 * The default PDO options.
	 *
	 * @var array
	 */
	protected $defaultPdoOptions = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_CASE               => PDO::CASE_NATURAL,
		PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES  => false,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];

	/**
	 * @param ConfigInterface $config
	 * @param string|null     $defaultConnection If null, "database.connection" is retrieved from $config
	 */
	public function __construct(ConfigInterface $config, $defaultConnection = null)
	{
		$this->config = $config;
		$this->defaultConnection = $defaultConnection ?: $config->get('database.connection');
	}

	/**
	 * Get a PDO instance.
	 *
	 * @param  string|null $connection Null fetches the default connection.
	 *
	 * @return PDO
	 *
	 * @throws \InvalidArgumentException if connection is not configured
	 */
	public function getPdo($connection = null)
	{
		if ($connection === null) {
			$connection = $this->defaultConnection;
		}

		if (array_key_exists($connection, $this->instances)) {
			return $this->instances[$connection];
		}

		return $this->instances[$connection] = $this->makePdo($connection);
	}

	protected function makePdo($connection)
	{
		$config = $this->getConnectionConfig($connection);

		if (!isset($config['dsn']) || !$config['dsn']) {
			throw new \InvalidArgumentException("Connection $connection missing data: dsn");
		}

		if (strpos($config['dsn'], 'sqlite') === 0) {
			$username = $password = '';
		} else {
			foreach (['username', 'password'] as $key) {
				if (!isset($config[$key])) {
					throw new \InvalidArgumentException("Connection $connection missing data: $key");
				}
			}
			$username = $config['username'];
			$password = $config['password'];
		}

		$configOptions = array_key_exists('options', $config) ? $config['options'] : [];

		return new PDO($config['dsn'], $username, $password,
			$configOptions + $this->defaultPdoOptions);
	}

	/**
	 * Get the configuration array for a specific connection.
	 *
	 * @param  strgin $connection The name of the connection.
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

			throw new \InvalidArgumentException("Connection $connection not defined");
		}

		return $config;
	}
}
