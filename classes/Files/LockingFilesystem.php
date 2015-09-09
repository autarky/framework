<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Files;

/**
 * Class that performs a locking file read/write operation.
 *
 * The object will add a file lock before writing and release it right after the
 * contents have been written. This can be used to prevent corruption of data
 * when multiple requests try to write to the same file at the same time.
 */
class LockingFilesystem
{
	/**
	 * Read from a file.
	 *
	 * @param  string $path Path to the file.
	 * @param  bool   $blocking Wait for other locks to expire rather than
	 * throwing an error when a lock cannot be aquired.
	 *
	 * @return string
	 *
	 * @throws IOException
	 */
	public function read($path, $blocking = false)
	{
		$size = filesize($path);

		if ($size === 0) {
			return '';
		}

		$flockFlags = $blocking ? LOCK_SH : LOCK_SH | LOCK_NB;

		$file = fopen($path, 'r');

		if (!flock($file, $flockFlags)) {
			fclose($file);
			throw new IOException("Could not aquire file lock for file: $path");
		}

		$contents = fread($file, $size);
		flock($file, LOCK_UN | LOCK_NB);
		fclose($file);

		return $contents;
	}

	/**
	 * Write to the file.
	 *
	 * @param  string $path Path to the file.
	 * @param  string $contents Contents to write to the file.
	 * @param  bool   $blocking Wait for other locks to expire rather than
	 * throwing an error when a lock cannot be aquired.
	 *
	 * @return void
	 *
	 * @throws IOException
	 */
	public function write($path, $contents, $blocking = false)
	{
		$flockFlags = $blocking ? LOCK_EX : LOCK_EX | LOCK_NB;

		$file = fopen($path, 'c');

		if (!flock($file, $flockFlags)) {
			fclose($file);
			throw new IOException("Could not aquire file lock for file: $path");
		}

		ftruncate($file, 0);
		fwrite($file, $contents);
		fflush($file);
		flock($file, LOCK_UN | LOCK_NB);
		fclose($file);
	}
}
