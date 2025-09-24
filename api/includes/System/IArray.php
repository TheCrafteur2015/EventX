<?php

namespace System;

use JsonSerializable;

class IArray implements JsonSerializable {
	
	private array $content;
	
	public function __construct(array $data = []) {
		$this->content = array_change_key_case($data);
	}
	
	public function append(string|int $key, mixed $value): void {
		$this->content[strtolower($key)] = $value;
	}
	
	public function get(string|int $key): mixed {
		if (is_string($key))
			$key = strtolower($key);
		return @$this->content[$key] ?: "";
	}
	
	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
