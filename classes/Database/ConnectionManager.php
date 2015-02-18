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
		PDO::ATTR_CASE               => PDO::CASE_NATURAL,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES   => false,
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES  => false,
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
			throw new \InvalidArgumentException("Missing DSN for connection: $connection");
		}

		if (strpos($config['dsn'], 'sqlite') === 0) {
			$username = $password = '';
		} else {
			if (!isset($config['username']) || !$config['username']) {
				throw new \InvalidArgumentException("Missing username for connection: $connection");
			}
			$username = $config['username'];
			if (!isset($config['password'])) {
				throw new \InvalidArgumentException("Missing password for connection: $connection");
			}
			$password = $config['password'];
		}

		$options = array_key_exists('options', $config) ? $config['options'] : [];
		$options = array_replace($this->defaultPdoOptions, $options);

		try {
			return new PDO($config['dsn'], $username, $password, $options);
		} catch (\PDOException $e) {
			$newException = new CannotConnectException($e->getMessage(), $e->getCode(), $e);
			$newException->errorInfo = $e->errorInfo;
			throw $newException;
		}
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
