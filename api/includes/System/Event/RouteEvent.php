<?php

namespace System\Event;

use JetBrains\PhpStorm\Immutable;
use System\Http\Response\Response;
use System\Http\Routing\Route;

#[Immutable]
class RouteEvent extends Event {
	
	/** @var string */
	public const ON_ROUTE = "route";
	
	/** @var string */
	public const ON_ROUTE_PREPEND = "routePrepend";
	
	/** @var string */
	public const ON_ROUTE_APPEND = "routeAppend";
	
	public readonly ?Route $route;
	
	public readonly ?Response $response;
	
	public function __construct(string $type, Route $route = null, Response $response = null) {
		parent::__construct($type);
		$this->route = $route;
		$this->response = $response;
	}
	
}
