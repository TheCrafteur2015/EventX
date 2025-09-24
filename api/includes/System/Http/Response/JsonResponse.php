<?php

namespace System\Http\Response;

use RuntimeException;

class JsonResponse extends Response {
	
	private int $flags;
	
	public function __construct() {
		parent::__construct();
		$this->flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
		$this->addHeader(CONTENT_TYPE, "application/json");
	}
	
	/**
	 * @param mixed $payload
	 * @return void
	 */
	public function setPayload(mixed $payload): void {
		if (!is_array($payload))
			throw new RuntimeException("Payload must be an array");
		$this->value = $payload;
	}
	
	public function getPayload(): array {
		return $this->value;
	}
	
	public function append(mixed $payload): bool {
		if (is_array($payload)) {
			$this->value = array_merge($this->value, $payload);
			return true;
		}
		return false;
	}
	
	public function send(): int {
		$content = json_encode($this->value, $this->flags);
		$this->addHeader(CONTENT_LENGTH, strlen($content));
		$this->addHeader(ETAG, '"'.md5($content).'"');
		$this->sendHeaders();
		echo $content;
		return 0;
	}
}
