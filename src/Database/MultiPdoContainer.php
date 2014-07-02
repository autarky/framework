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

use Pdo;

use Autarky\Config\ConfigInterface;

/**
 * Container that manages multiple PDO instances.
 */
class MultiPdoContainer
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
	protected $instances;

	/**
	 * The default PDO options.
	 *
	 * @var array
	 */
	protected $defaultPdoOptions = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
		PDO::ATTR_CASE               => PDO::CASE_NATURAL,
		PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES  => false,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];

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

		if (isset($this->instances[$connection])) {
			return $this->instances[$connection];
		}

		return $this->instances[$connection] = $this->makePdo($connection);
	}

	protected function makePdo($connection)
	{
		$config = $this->config->get("database.connections.$connection");

		if (!$config) {
			throw new \InvalidArgumentException("Connection $connection not defined");
		}

		return new PDO($config['dsn'], $config['username'], $config['password'],
			($config['options'] ?: []) + $this->defaultPdoOptions);
	}
}
