<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Providers;

class ProviderException extends \Exception
{
	/**
	 * The error messages.
	 *
	 * @var string[]
	 */
	protected $errors;

	/**
	 * Constructor.
	 *
	 * @param string   $message
	 * @param string[] $errors
	 */
	public function __construct($message, array $errors)
	{
		parent::__construct($message);
		$this->errors = $errors;
	}

	/**
	 * Get the exception's error messages.
	 *
	 * @return string[]
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
