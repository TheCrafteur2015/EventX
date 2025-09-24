<?php

namespace System\Http\Routing;

use JsonSerializable;
use System\Http\MethodType;

class RouteHandler implements JsonSerializable {

	private MethodType $type;

	private mixed $value;

	public function __construct(MethodType $type, mixed $value) {
		// TODO: add check for handler type
		$this->type = $type;
		$this->value = $value;
	}

	public function getValue(): mixed {
		return $this->value;
	}

	public function getType(): MethodType {
		return $this->type;
	}
	
	public function getClassName(): string|null {
		return match ($this->type) {
			MethodType::ARRAY_CALLABLE => $this->value[0],
			MethodType::CALLABLE       => 'App\Controller\\'.explode("::", $this->value)[0],
			default                    => null
		};
		// TODO: Add the other enum values
	}
	
	public function getAction(): string|null {
		return match ($this->type) {
			MethodType::ARRAY_CALLABLE => $this->value[1],
			MethodType::CALLABLE       => explode("::", $this->value)[1],
			MethodType::REDIRECTION    => $this->value,
			default                    => null
		};
		// TODO: Add the other enum values
	}

	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
