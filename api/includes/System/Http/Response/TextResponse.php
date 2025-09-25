<?php

namespace System\Http\Response;

use RuntimeException;

class TextResponse extends Response {
	
	public function __construct(string $type = "plain") {
		parent::__construct();
		$this->addHeader(CONTENT_TYPE, "text/$type");
	}
	
	/**
	 * @param mixed $payload
	 * @return void
	 */
	public function setPayload(mixed $payload): void {
		if (!is_string($payload))
			throw new RuntimeException("Payload must be a string");
		$this->value = $payload;
	}
	
	public function getPayload(): string {
		return $this->value;
	}
	
	public function append(mixed $payload): bool {
		if (is_string($payload)) {
			$this->value .= $payload;
			return true;
		}
		return false;
	}
	
	public function send(): int {
		$this->addHeader(CONTENT_LENGTH, strlen($this->value));
		$this->addHeader(ETAG, '"'.md5($this->value).'"');
		$this->sendHeaders();
		echo $this->value;
		return 0;
	}
}
