<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Support;

class Diagnostic
{
	protected $errors = 0;
	protected $verbose = false;

	public function __construct($verbose = false)
	{
		$this->verbose = $verbose;
	}

	public function checkPaths(array $paths)
	{
		foreach ($paths as $key => $value) {
			if (!$value) {
				continue;
			}

			if (!is_dir($value)) {
				echo "ERROR: path.$key: $value is not a directory\n";
				continue;
			}

			if ($key == 'vendor' || $key == 'base') {
				$this->checkDir("path.$key", $value, true, false, false);
			} else if ($key == 'storage') {
				$this->checkDir("path.$key", $value, true, true, true);
			} else {
				$this->checkDir("path.$key", $value, true, false, true);
			}
		}

		if ($this->errors === 0) {
			echo "No errors!\n";
			return 0;
		} else {
			return 1;
		}
	}

	protected function checkDir($prefix, $dir, $readable = true, $writeable = false, $recurse = true)
	{
		if ($recurse) {
			foreach (glob("$dir/*") as $path) {
				if ($path == $dir) {
					continue;
				}

				if (is_dir($path)) {
					$this->checkDir($prefix, $path, $readable, $writeable, $recurse);
					continue;
				}

				if ($readable && !is_readable($path)) {
					$this->errors++;
					echo "ERROR: $prefix: $path is not readable\n";
				} else if ($this->verbose) {
					echo "OK: $prefix: $path is readable\n";
				}

				if ($writeable && !is_writeable($path)) {
					$this->errors++;
					echo "ERROR: $prefix: $path is not writeable\n";
				} else if ($this->verbose) {
					echo "OK: $prefix: $path is writeable\n";
				}
			}
		}

		if ($readable && !is_readable($dir)) {
			$this->errors++;
			echo "ERROR: $prefix: $dir is not readable\n";
		} else if ($this->verbose) {
			echo "OK: $prefix: $path is readable\n";
		}

		if ($writeable && !is_writeable($dir)) {
			$this->errors++;
			echo "ERROR: $prefix: $dir is not writeable\n";
		} else if ($this->verbose) {
			echo "OK: $prefix: $path is writeable\n";
		}
	}

	public static function check(array $paths, $verbose = false)
	{
		(new static($verbose))->checkPaths($paths);
	}
}
