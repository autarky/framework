<?php
namespace Autarky\Files;

class LockingWriteOperation
{
	protected $path;
	protected $blocking;

	public function __construct($path, $blocking = false)
	{
		$this->path = $path;
		$this->blocking = $blocking;
	}

	public function write($contents)
	{
		$file = fopen($this->path, 'c');

		$flockFlags = $this->blocking ? LOCK_EX : LOCK_EX | LOCK_NB;

		if (!flock($file, $flockFlags)) {
			fclose($file);
			throw new IOException("Could not aquire file lock for file: $this->path");
		}
		
		ftruncate($file, 0);
		fwrite($file, $contents);
		fflush($file);
		flock($file, LOCK_UN | LOCK_NB);
		fclose($file);
	}
}
