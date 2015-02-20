<?php
namespace Autarky\Tests;

class SpyPDO extends \PDO
{
	protected $dsn;
	protected $username;
	protected $password;
	protected $options;
	protected $execLog = [];

	public function __construct($dsn, $username, $password, array $options = array())
	{
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;
	}

	public function getDsn()
	{
		return $this->dsn;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getConstructorArgs()
	{
		return [$this->dsn, $this->username, $this->password, $this->options];
	}

	public function exec()
	{
		$this->execLog[] = func_get_args();
	}

	public function getExecLog()
	{
		return $this->execLog;
	}

	public static function create($dsn, $username, $password, array $options = array())
	{
		return new static($dsn, $username, $password, $options);
	}
}
