<?php

namespace System\Config;

use ReflectionClass;
use ReflectionException;
use System\Http\Routing\ControllerTag;

class OpenAPI {
	
	private static array $OPENAPI_DATA = [];
	
	public static function register(string $class): void {
		if (str_starts_with($class, "App\\Controller")) {
			try {
				$className = substr($class, strrpos($class, "\\") + 1);
				$data = [];
				$reflection = new ReflectionClass($class);
				foreach ($reflection->getAttributes(ControllerTag::class) as $attr) {
					$data['name'] = ControllerTag::getName($attr);
				}
				foreach ($reflection->getMethods() as $method) {
//					d($method->getName());
					foreach ($method->getAttributes() as $attr) {
//						d($attr);
					}
//					foreach ($method->getAttributes(HttpGet::class) as $attr) {
//						$args = array_values($attr->getArguments());
//						d($args);
//					}
				}
				self::$OPENAPI_DATA[$className] = $data;
			} catch (ReflectionException) {
			
			}
		}
	}
	
	public static function show(): void {
		d(self::$OPENAPI_DATA);
	}
	
}
