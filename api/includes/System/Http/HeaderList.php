<?php

namespace System\Http;

use ArrayAccess;

class HeaderList implements ArrayAccess {
	
	protected array $headers = [];
	
	private function __construct() {}
	
	public static function getRequestHeaders(): self {
		$headers = new self();
		$headers->loadRequestHeaders();
		return $headers;
	}
	
	public static function getResponseHeaders(): self {
		return new self();
	}
	
	public function send(): void {
		foreach ($this->headers as $key=>$header) {
			header("$key: $header");
		}
	}
	
	public function get(string $key): mixed {
		return $this->headers[$key] ?: null;
	}
	
	public function set(string $key, mixed $value): void {
		$this->headers[$key] = $value;
	}
	
	private function loadRequestHeaders(): void {
		$headers = array_filter($_SERVER, fn($k) => str_starts_with($k, "HTTP"), ARRAY_FILTER_USE_KEY);
		$keys = array_map(fn($v) => str_replace(["Http_", "_"], "", mb_convert_case($v, MB_CASE_TITLE, "UTF-8")), array_keys($headers));
		
		$keys = array_map(fn($v) => preg_replace("#(?<=[^^])[A-Z]#", "-$0", $v), $keys);
		
		$this->headers = array_combine($keys, array_values($headers));
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetExists(mixed $offset): bool {
		if (!is_string($offset))
			return false;
		return array_key_exists($offset, $this->headers);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetGet(mixed $offset): mixed {
		if (!is_string($offset))
			return null;
		return @$this->headers[$offset] ?: null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		if (is_string($offset))
			$this->set($offset, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetUnset(mixed $offset): void {
		if (is_string($offset))
			unset($this->headers[$offset]);
	}
}














