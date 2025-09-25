<?php

namespace System\Http;

use JsonSerializable;

enum MethodType: string implements JsonSerializable {
	case CALLABLE = "callable";
	case ARRAY_CALLABLE = "array";
	case CLOSURE = "\Closure";
	case REDIRECTION = "string";
	case OPTIONS = "null";
	case INVALID = "";

	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
