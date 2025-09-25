<?php

namespace System\Exception;

use Exception;

class AuthenticationException extends Exception {

	private ?string $user;

	private null|string|array $token;

	private int $httpCode;

	public function __construct(?string $user, null|string|array $token, string $message = "", int $code = 401) {
		parent::__construct($message);
		$this->user = $user;
		$this->token = $token;
		$this->httpCode = $code;
	}

	public function getUser(): ?string {
		return $this->user;
	}

	public function getToken(): null|string|array {
		if (gettype($this->token) === "array")
			return array_filter($this->token, fn($token): bool => !is_null($token));
		return $this->token;
	}

	public function getHttpCode(): int {
		return $this->httpCode;
	}

}
