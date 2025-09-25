<?php

namespace System\Http;

use JsonSerializable;

class PayloadData implements JsonSerializable {
	
	private mixed $value;
	
	private string $source;
	
	function __construct(mixed $value, string $source) {
		$this->value = $value;
		$this->source = $source;
	}
	
	public function append(string|int|float|bool $newValue): void {
		if (!is_array($this->value))
			$this->value = [$this->value];
		$this->value[] = $newValue;
	}
	
	public function getValue(): mixed {
		return $this->value;
	}
	
	public function getSource(): string {
		return $this->source;
	}
	
	public function setSource(string $source): void {
		$this->source = $source;
	}
	
	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): mixed {
		return $this->value;
	}
}
