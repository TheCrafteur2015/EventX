<?php

namespace System\Http\Response;

use RuntimeException;

class ImageResponse extends Response {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function setPayload(mixed $payload, bool $asBase64 = false): void {
		if (!is_string($payload))
			throw new RuntimeException("Payload must be a string");
		$ext = get_image_type($payload);
		if ($asBase64) {
			$payload = "data:image/$ext;base64,".get_image_as_base64($payload);
			$this->addHeader(CONTENT_TYPE, "text/plain");
		} else {
			$payload = get_image_content($payload);
			$this->addHeader(CONTENT_TYPE, "$ext; charset=binary");
			$this->addHeader(ACCEPT_RANGES, "bytes");
		}
		$this->addHeader(CONTENT_LENGTH, strlen($payload));
		$this->value = $payload;
	}
	
	public function getPayload(): string {
		return $this->value;
	}
	
	public function append(mixed $payload): bool {
		return false;
	}
	
	public function send(): int {
		if (is_null($this->value))
			return 1;
		$this->sendHeaders();
		echo $this->value;
		return 0;
	}
}
