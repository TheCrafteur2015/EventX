<?php

namespace System\Config;

use Exception;
use stdClass;

class Options extends stdClass {
	
	public bool $use_debug = false;
	
	/**
	 * @throws Exception
	 */
	public function __set(string $name, mixed $value): void {
		throw new Exception("Cannot add properties to an immutable object.");
	}
	
}
