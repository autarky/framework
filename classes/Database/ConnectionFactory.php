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

use InvalidArgumentException;
use PDO;
use PDOException;

class ConnectionFactory implements ConnectionFactoryInterface
{
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
	 * Create a new PDO instance.
	 *
	 * @param  array  $config
	 * @param  string $connection Name of the connection - used for exception
	 * messages
	 *
	 * @return PDO
	 *
	 * @throws InvalidArgumentException If connection is incorrectly configured
	 * @throws CannotConnectException If construction of PDO object fails
	 */
	public function makePdo(array $config, $connection = null)
	{
		// extract the username and password
		if (
			(isset($config['driver']) && $config['driver'] == 'sqlite') ||
			(isset($config['dsn']) && strpos($config['dsn'], 'sqlite:') === 0)
		) {
			$username = $password = '';
		} else {
			$this->validate($config, 'username', $connection, false);
			$username = $config['username'];
			unset($config['username']);

			$this->validate($config, 'password', $connection, true);
			$password = $config['password'];
			unset($config['password']);
		}

		// either DSN or driver must be present
		if (!isset($config['dsn'])) {
			$this->validate($config, 'driver', $connection);
		}
		if (!isset($config['driver'])) {
			$this->validate($config, 'dsn', $connection);
		}

		$options = array_key_exists('pdo_options', $config) ? $config['pdo_options'] : [];
		unset($config['pdo_options']);
		$options = array_replace($this->defaultPdoOptions, $options);

		$initCommands = isset($config['pdo_init_commands']) ? $config['pdo_init_commands'] : [];
		unset($config['pdo_init_commands']);

		if (isset($config['dsn'])) {
			$dsn = $config['dsn'];
		} else {
			$driver = $config['driver'];
			unset($config['driver']);
			if ($driver == 'sqlite') {
				$this->validate($config, 'path', $connection);
				$path = $config['path'];
				unset($config['path']);
				$dsn = $this->makeSqliteDsn($path);
			} else {
				$this->validate($config, 'host', $connection);
				$this->validate($config, 'dbname', $connection);
				$dsn = $this->makeDsn($values, $config);
			}
		}

		try {
			$pdo = new PDO($dsn, $username, $password, $options);
		} catch (PDOException $e) {
			$newException = new CannotConnectException($e->getMessage(), $e->getCode(), $e);
			$newException->errorInfo = $e->errorInfo;
			throw $newException;
		}

		foreach ($initCommands as $command) {
			$pdo->exec($command);
		}

		return $pdo;
	}

	protected function makeSqliteDsn($path)
	{
		return "sqlite:$path";
	}

	protected function makeDsn($driver, array $values)
	{
		$valuestrings = [];

		foreach ($values as $key => $value) {
			$valuestrings[] = $key.'='.$value;
		}

		return $driver.':'.implode(';', $valuestrings);
	}

	protected function validate(array &$config, $key, $connection, $allowEmpty = false)
	{
		if (!isset($config[$key]) || (!$allowEmpty && !$config[$key])) {
			throw new InvalidArgumentException("Missing $key for connection: $connection");
		}
	}
}
