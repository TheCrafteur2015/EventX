<?php

namespace System\Http\Response;

class EmptyResponse extends Response {
	
	public function __construct() {
		parent::__construct();
		$this->httpCode = 204;
		$this->value = null;
	}
	
	public function setPayload(mixed $payload): void {}
	
	public function getPayload(): mixed {
		return null;
	}
	
	public function append(mixed $payload): bool {
		return false;
	}
	
	public function send(): int {
		$this->addHeader(CONTENT_LENGTH, 0);
		$this->sendHeaders();
		return 0;
	}
}
