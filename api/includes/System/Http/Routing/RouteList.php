<?php /** @noinspection PhpUnused */

namespace System\Http\Routing;

use Closure;
use System\Http\MethodType;

class RouteList {

	/** @var Route[] $routes */
	private array $routes;
	
	protected array $METHODS;
	
	public function __construct() {
		$this->routes  = [];
		$this->METHODS = array_merge(MAIN_METHODS, SECONDARY_METHODS);
	}
	
	/**
	 * @param array $methods
	 * @param string $uri
	 * @param Closure|callable|string|null $handler
	 * @param array $options
	 * @return void
	 */
	public function match(array $methods, string $uri, Closure|callable|string|null $handler, array $options = []): void {
		foreach ($methods as $method) {
			if (!in_array($method, $this->METHODS))
				trigger_error("Invalid method: $method", E_USER_ERROR);
			$this->addRoute($method, $uri, $handler, $options);
		}
	}
	
	public function get(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("GET", $uri, $handler, $options);
	}
	
	public function post(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("POST", $uri, $handler, $options);
	}
	
	public function put(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("PUT", $uri, $handler, $options);
	}
	
	public function head(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("HEAD", $uri, $handler, $options);
	}
	
	public function delete(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("DELETE", $uri, $handler, $options);
	}
	
	public function options(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("OPTIONS", $uri, $handler, $options);
	}
	
	public function trace(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("TRACE", $uri, $handler, $options);
	}
	
	public function connect(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("CONNECT", $uri, $handler, $options);
	}
	
	public function patch(string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$this->addRoute("PATCH", $uri, $handler, $options);
	}
	
	private function addRoute(string $method, string $uri, Closure|callable|string|null $handler, array $options = []): void {
		$handlerType = $this->getHandlerType($handler);
		$handler = $this->mapHandler($handler, $handlerType);
		$options = $this->mapOptions($options);
		$this->routes[] = new Route($method, $uri, $handlerType, $handler, $options);
	}
	
	private function getHandlerType(Closure|callable|string|null $handler): MethodType {
		$type = match (true) {
			is_string($handler) && (preg_match('#^(/(.+|\{\w+}))+/?$#', $handler) || filter_var($handler, FILTER_VALIDATE_URL)) => MethodType::REDIRECTION,
			is_string($handler) && preg_match('#^.+::\w+$#', $handler) => MethodType::CALLABLE,
			(is_array($handler) && is_callable($handler)) || (is_string($handler) && preg_match('#^\w+::\w+$#', $handler)) =>
			MethodType::ARRAY_CALLABLE,
			$handler instanceof Closure => MethodType::CLOSURE,
			$handler === null => MethodType::OPTIONS,
			default => MethodType::INVALID
		};
		if ($type === MethodType::INVALID)
			trigger_error("Invalid route handler", E_USER_ERROR);
		return $type;
	}
	
	private function mapHandler(Closure|callable|string|null $handler, MethodType $handlerType): mixed {
		return match(true) {
			$handlerType === MethodType::ARRAY_CALLABLE && is_string($handler) && preg_match('#^\w+::\w+$#', $handler) =>
			['App\Controller\\'.explode("::", $handler)[0], explode("::", $handler)[1]],
			default => $handler,
		};
	}
	
	private function mapOptions(array $options): array {
		if (isset($options['Formats'], $options['Default']))
			if (!in_array($options['Default'], $options['Formats']))
				$options['Formats'][] = $options['Default'];
		return [
			'Auth-Required'    =>@$options['Auth-Required']     ?: false,
			'Auth-Clearance'   =>@$options['Auth-Clearance']    ?: 1,
			'Redirect-If-Error'=>@$options['Redirect-If-Error'] ?: '/',
			'Default'          =>@$options['Default']           ?: 'json',
			'Formats'          =>@$options['Formats']           ?: ['json'],
			'Parameters'       =>@$options['Parameters']        ?: [],
			'Xml-Element'      =>@$options['Xml-Element']       ?: null,
		];
	}
	
	public function getRoutes(): array {
		return $this->routes;
	}
	
}
