<?php

namespace System;

use Throwable;

class Logger {
	
	private string $filename;
	
	public function __construct(string $path) {
		if (!file_exists($path)) {
			touch($path);
			chmod($path, 0777);
		}
		$this->filename = realpath($path);
		if (!$this->filename)
			trigger_error("An error occurred while creating log files", E_USER_WARNING);
	}
	
	public function log(mixed $message): void {
		$date = date("d/m/Y - H:i", time());
		if ($message instanceof Throwable) {
			$message = $message->getMessage()." - ".$message->getTraceAsString();
		} else if (is_array($message) || is_object($message)) {
			if (json_encode($message) !== false)
				$message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			else
				$message = get_var_dump($message);
		}
		file_put_contents($this->filename, "[$date] - $message".PHP_EOL, FILE_APPEND);
	}
	
}
