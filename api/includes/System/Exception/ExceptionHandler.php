<?php

namespace System\Exception;

use System\Configuration;
use System\Http\Response\ResponseHandler;
use Throwable;

class ExceptionHandler {

	public static function init(): void {
		set_error_handler(function(int $errno, string $err_str, string $err_file, int $err_line) {
			ResponseHandler::fatalError();
			$logger = Configuration::$_LOGGER;
			if ($logger)
				$logger->log("Error: $errno $err_str in $err_file on line $err_line");
			else
				var_dump($errno, $err_str, $err_file, $err_line);
		});
		set_exception_handler(function(Throwable $e) {
			ResponseHandler::fatalError();
			$logger = Configuration::$_LOGGER;
			if ($logger)
				$logger->log($e->getMessage()." => ".$e->getTraceAsString());
			else
				echo $e;
		});
	}

}
