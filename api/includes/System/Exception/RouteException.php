<?php

namespace System\Exception;

use RuntimeException;

class RouteException extends RuntimeException {

	private int $httpCode;

	public function __construct(string $message, int $code) {
		parent::__construct($message);
		$this->httpCode = $code;
	}

	public function getHttpCode() : int {
		return $this->httpCode;
	}

}
