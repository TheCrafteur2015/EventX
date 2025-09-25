<?php

namespace System\Http\Routing;

use Closure;
use ReflectionException;
use ReflectionMethod;
use System\ApacheAPI;
use System\Exception\RouteException;
use System\Http\MethodType;
use System\Http\Request;
use System\Http\Response\Response;

const MAIN_METHODS = ["GET", "POST", "PUT", "DELETE", "OPTIONS"];
const SECONDARY_METHODS = ["HEAD", "TRACE", "CONNECT", "PATCH"];

class Router {

	protected array $methods = [];

	protected array $routes = [];
	
	protected ApacheAPI $apacheAPI;
	
	protected array $tags = [];

	public function __construct(ApacheAPI $apacheAPI) {
		$this->apacheAPI = $apacheAPI;
		$routes = new RouteList();
		require APPPATH.DS."Config".DS."Routes.php";
		foreach ($routes->getRoutes() as $route)
			$this->addRoute($route);
	}
	
	public function addRoute(Route $route): void {
		$methods = $route->methods;
		if (is_string($methods)) {
			if (!in_array($methods, array_merge(MAIN_METHODS, SECONDARY_METHODS, ["*"]))) {
				trigger_error("Method not allowed: $methods");
				return;
			}
			if ($methods === "*") {
				foreach (MAIN_METHODS as $m)
					$this->routes[$m][$route->route] = $route;
			} else {
				$this->methods[] = $methods;
				$this->routes[$methods][$route->route] = $route;
			}
		} else {
			foreach ($methods as $method) {
				if (!in_array($method, array_merge(MAIN_METHODS, SECONDARY_METHODS, ["*"]))) {
					trigger_error("Method not allowed: $method");
					continue;
				}
				if ($method === "*") {
					foreach (MAIN_METHODS as $m)
						$this->routes[$m][$route->route] = $route;
				} else {
					$this->methods[] = $method;
					$this->routes[$method][$route->route] = $route;
				}
			}
		}
//		$this->tags[] = $route->getTag();
//		$this->tags = array_unique($this->tags);
		$this->methods = array_unique($this->methods);
	}
	
	public function getTags(): array {
		return $this->tags;
	}
	
	protected function getMethodType(string|callable|Closure $handler): MethodType {
		return match (true) {
			is_string($handler) => MethodType::REDIRECTION,
			is_array($handler) && is_callable($handler) => MethodType::ARRAY_CALLABLE,
			$handler instanceof Closure => MethodType::CLOSURE,
			default => MethodType::INVALID
		};
	}
	
	/**
	 * @throws RouteException
	 * @throws ReflectionException
	 */
	public function handle(Request $request): Response|null {
		foreach ($this->routes[$request->getMethod()] as $path=>$route) {
			if (preg_match('#^' . $this->patternToRegex($path) . '/?$#', $request->getUri(), $matches)) {
				$request->loadRouteData($route, $matches);
				/** @var RouteHandler $handler */
				$handler = $route->handler;
//				$options = $route->options;
				
				$response = null;
				switch($handler->getType()) {
					case MethodType::ARRAY_CALLABLE:
						$action = $handler->getAction();
						$controller = new ($handler->getClassName())($request);
						
						$reflection = new ReflectionMethod($controller, $action);

						$fullParams = $request->getParams();
						$filteredParams = array_intersect_key($fullParams, array_flip(array_map(fn($e) => $e->getName(), $reflection->getParameters())));
//						$filteredParams = $request->getParams();
						
						$response = $controller->$action(...$filteredParams);
						break;
					case MethodType::CALLABLE:
						$action = $handler->getAction();
						$controller = new ($handler->getClassName())($request);
						
						$reflection = new ReflectionMethod($controller, $action);
						
						$fullParams = $request->getParams();
						$filteredParams = array_intersect_key($fullParams, array_flip(array_map(fn($e) => $e->getName(), $reflection->getParameters())));
//						$filteredParams = $request->getParams();
						
						$response = $controller->$action(...$filteredParams);
						break;
					case MethodType::CLOSURE:
						$response = call_user_func($handler->getValue(), $request);
						break;
					case MethodType::OPTIONS:
						// 4
						break;
					case MethodType::REDIRECTION:
						http_response_code(301);
						header("Location: {$handler->getAction()}");
						break;
					case MethodType::INVALID:
						throw new RouteException('To be implemented', 400);
				}
				return $response;
				
//				dd($format, $handler);
//				if ($options['Auth-Required'] && (!$apiToken || !$apiToken->isValid()) && !isset($_SESSION[Autoloader::getConfig("api.session")])) {
//					$tokens = array_filter(Autoloader::getAuths()['token'], fn(?string $token): bool => !is_null($token));
//					$username = firstNonNull(Autoloader::getAuths()['user']);
//					throw new AuthenticationException($username, $tokens, match(true) {
//						$apiToken => "The provided credentials are invalid!",
//						default => "This route requires an authentication!"
//					});
//				}
//				$params = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
//				$params = array_merge($params, $_GET, $_POST);
//				if (isset($_GET['format']) && !in_array($_GET['format'], $options['Formats']))
//					throw new RouteException("Unsupported MIME type", 415);
//
//				EventHandler::trigger(new RouteEvent(RouteEvent::ON_ROUTE, $route));
//				$request = ApacheAPI::instance()->request;
//				$response = new Response(null, $route, @$_GET['format'] ?? $options['Default']);
//				EventHandler::trigger(new RouteEvent(RouteEvent::ON_ROUTE_PREPEND, $route, $response));
//				switch ($handler->getType()) {
//					case MethodType::CLOSURE:
//						if (!($handler->getValue() instanceof Closure))
//							throw new RouteException("Unsupported Closure type", 409);
//						$response = $handler->getValue()($request, $response, $params);
//						break;
//					case MethodType::REDIRECTION:
//						header("Location: ".ApacheAPI::getUrl($handler->getValue()));
//						break;
//					case MethodType::ARRAY_CALLABLE:
//						$controllerName = $handler->getValue()[0];
//						$action = $handler->getValue()[1];
//						$controller = new $controllerName($request);
//						$response = $controller->$action($response, $params);
//						break;
//					case MethodType::OPTIONS:
//						if (!isset($_GET['route']) || empty($_GET['route'])) {
//							$response->html("")->httpCode(400);
//							break;
//						}
//						foreach ($this->routes[$method] as $request_uri=>$dummy) {
//							if (preg_match('#^' . $this->patternToRegex($request_uri) . '/?$#', $_GET['route'], $match)) {
//								$opts = $dummy->getOptions();
//								$opts['Methods'] = $dummy->getMethods();
//								$opts['Base-Uri'] = $request_uri;
//								$response->json($opts);
//								break 2;
//							}
//						}
//						throw new RouteException("Erreur 404: Page non trouvée", 404);
//				}
//				if (!($response instanceof Response))
//					$response = new Response($response, $route);
//				EventHandler::trigger(new RouteEvent(RouteEvent::ON_ROUTE_APPEND, $route, $response));
//				return $response;
			}
		}
		throw new RouteException("Erreur 404: Page non trouvée", 404);
	}

	private function patternToRegex(string $pattern): array|string|null {
		return preg_replace_callback('/{(\w+)(:(?P<type>text|integer|float))?}/', function ($matches) {
			if (isset($matches['type'])) {
				switch ($matches['type']) {
					case "integer":
						return '(?P<' . $matches[1] . '>\d+)';
					case "float":
						return '(?P<' . $matches[1] . '>\d+\.\d+)';
				}
			}
			return '(?P<' . $matches[1] . '>[^/]+)';
		}, $pattern);
	}

	public function getMethods(): array {
		return $this->methods;
	}
	
	/**
	 * @return array
	 */
	public function getRoutes(): array {
		return $this->routes;
	}

}
