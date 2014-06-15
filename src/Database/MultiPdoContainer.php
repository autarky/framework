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

class MultiPdoContainer
{
	protected $config;

	protected $defaultConnection;

	protected $instances;

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
		$dsn = $this->config->get("database.connections.{$connection}.dsn");
		$username = $this->config->get("database.connections.{$connection}.username");
		$password = $this->config->get("database.connections.{$connection}.password");

		$options = $this->config->get("database.connections.{$connection}.options", [])
			+ $this->defaultPdoOptions;

		return new PDO($dsn, $username, $password, $options);
	}
}
