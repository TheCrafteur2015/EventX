<?php

namespace System\Http;

class Payload {
	
	/** @var PayloadData[] $data */
	private array $data = [];
	
	public function __construct() {
		$this->init();
	}
	
	private function init(): void {
		foreach ($_GET as $key=>$value) {
			$value = $this->mapValue($value);
			if (array_key_exists($key, $this->data)) {
				$this->data[$key]->append($value);
				continue;
			}
			if ($value !== "")
				$this->data[$key] = new PayloadData($value, GET);
		}
		foreach ($_POST as $key=>$value) {
			$value = $this->mapValue($value);
			if (array_key_exists($key, $this->data)) {
				if ($this->data[$key]->getSource() === GET)
					$this->data[$key]->setSource(REQUEST);
				$this->data[$key]->append($value);
				continue;
			}
			if ($value === "")
				$value = null;
			$this->data[$key] = new PayloadData($value, POST);
		}
	}
	
	private function mapValue(mixed $value): mixed {
		$lower  = strtolower($value);
		if ($lower === "null" || $lower === "undefined")
			return null;
		if ($lower === "true" || $lower === "false")
			return json_decode($value);
		return $value;
	}
	
	public function get(string $key, string $source = null): mixed {
		if (is_string($source) && !in_array($source, [GET, POST, REQUEST]))
			$source = REQUEST;
		if (array_key_exists($key, $this->data)) {
			if (is_string($source) && $this->data[$key]->getSource() !== $source)
				return false;
			$value = $this->data[$key]->getValue();
			return match(true) {
				is_numeric($value)           => floatval($value),
				is_bool(json_decode($value)) => boolval($value),
				default                      => $value,
			};
		}
		return false;
	}
	
	public function exists(string $key, string $source = null): bool {
		if (is_string($source) && !in_array($source, [GET, POST, REQUEST]))
			$source = REQUEST;
		if (array_key_exists($key, $this->data)) {
			if (is_string($source) && $this->data[$key]->getSource() !== $source)
				return false;
			if ($this->data[$key]->getSource() === $source)
				return true;
			return isset($this->data[$key]);
		}
		return false;
	}
	
	public function getData(): array {
		return $this->data;
	}
	
	public function getPostAsArray(): array {
		$array = array_filter($this->data, function($value) {
			return $value->getSource() === POST || $value->getSource() === REQUEST;
		});
		return json_decode(json_encode($array), true);
	}
	
}
