<?php

namespace System\Http\Routing;

use JsonSerializable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use System\Http\MethodType;

class Route implements JsonSerializable {
	
	public readonly string|array $methods;
	
	public readonly string $route;
	
	public readonly RouteHandler $handler;
	
	public readonly array $options;
	
	private array $tag = [];
	
	/**
	 * @throws ReflectionException
	 */
	public function __construct(string|array $methods, string $route, MethodType $handlerType, mixed $handler, array $options = []) {
		$this->methods = is_string($methods) ? strtoupper($methods) : array_map('strtoupper', $methods);
		$this->route = $route;
		$this->handler = new RouteHandler($handlerType, $handler);
		$this->options = $options;
//		$this->setTag();
	}
	
	/**
	 * TODO: Fix this method
	 * @throws ReflectionException
	 */
	private function setTag(): void {
		switch ($this->handler->getType()) {
			case MethodType::CALLABLE:
			case MethodType::ARRAY_CALLABLE:
				$class = new ReflectionClass($this->handler->getClassName());
				$attr = $class->getAttributes("ControllerTag")[0];
				if ($attr instanceof ReflectionAttribute) {
					$this->tag = [
						"name"        => $attr->getArguments()['name'],
						"description" => $attr->getArguments()['description'] ?? "",
					];
				}
				break;
			case MethodType::OPTIONS:
				break;
			case MethodType::CLOSURE:
				$this->tag = [
					"name"        => "Anonymous function",
					"description" => "Tag for routes that leads to an anonymous function",
				];
				break;
			case MethodType::REDIRECTION:
				$this->tag = [
					"name"        => "Redirection",
					"description" => "Redirection URIs",
				];
				break;
			case MethodType::INVALID:
				$this->tag = [
					"name"        => "Invalid",
					"description" => "There must be an error!",
				];
				break;
		}
	}
	
	public function getTag(): array {
		return $this->tag;
	}
	
	public function getFormats(): array {
		return @$this->options['Formats'] ?: [];
	}
	
	public function getDefaultFormat(): string {
		return @$this->options['Default'] ?: "json";
	}
	
	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
