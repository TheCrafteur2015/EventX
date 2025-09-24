<?php

namespace System\Http;

use System\ApacheAPI;
use System\Exception\RouteException;
use System\Http\Response\HtmlResponse;
use System\Http\Response\ImageResponse;
use System\Http\Response\JsonResponse;
use System\Http\Response\Response;
use System\Http\Response\StatusResponse;
use System\Http\Response\TextResponse;
use System\Http\Routing\Route;

class Request {
	
	public Payload $data;
	
	protected HeaderList $headers;
	
	protected string $requestMethod;
	protected string $requestUri;
	
	public readonly array $supportedCorsHeaders;
	
	protected array $formats;
	
	protected array $params;
	
	public readonly ApacheAPI $apacheAPI;
	
	private Route $route;
	
	protected function __construct(ApacheAPI $apacheAPI) {
		$this->apacheAPI = $apacheAPI;
		$this->headers   = HeaderList::getRequestHeaders();
		$this->data      = new Payload();
		$this->populateData();
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->requestUri    = @$_SERVER['PATH_INFO'] ?: "/";
		$this->params        = [];
	}
	
	public function loadRouteData(Route $route, array $params): void {
		$this->route = $route;
		$this->params = array_filter($params, "is_string", ARRAY_FILTER_USE_KEY);
	}
	
	public function getMethod(): string {
		return $this->requestMethod;
	}
	
	public function getUri(): string {
		return $this->requestUri;
	}
	
	public function getParams(): array {
		return $this->getMethod() === POST ? $this->data->getPostAsArray() :  $this->params;
	}
	
	public function getData(): array {
		return $this->data->getData();
	}
	
	public static function new(ApacheAPI $apacheAPI): self {
		return new self($apacheAPI);
	}
	
	public function isCors(): bool {
		return $this->header('Sec-Fetch-Mode') === "cors";
	}
	
	public function header(string $key): mixed {
		return $this->headers->get($key);
	}
	
	public function get(string $key, string $source = null): mixed {
		return $this->data->get($key, $source);
	}
	
	public function exists(string $key, string $source = null): bool {
		return $this->data->exists($key, $source);
	}
	
	public function getPostValues(): array {
		return $this->data->getPostAsArray();
	}
	
	/**
	 *
	 * @return string
	 */
	public function findFormat(): string {
		$format = $this->get("format") ?: $this->route->getDefaultFormat();
//		if (!array_contains($this->route->getFormats(), $this->formats)) TODO: check whatever to do with this
		if (!in_array($format, $this->route->getFormats()))
			throw new RouteException("Format not supported", 400);
		return $format;
	}
	
	/**
	 *
	 * @param mixed $content
	 * @param bool $forceConversion
	 * @return Response|null
	 */
	public function createResponse(mixed $content, bool $forceConversion = false): ?Response {
		$format = $this->findFormat();
		$response = match ($format) {
			"json"                  => new JsonResponse(),
			"html"                  => new HtmlResponse(),
			"text"                  => new TextResponse(),
			"image", "image-base64" => new ImageResponse(),
		};
		if ($forceConversion) {
			$content = match(true) {
				$format === "json" && !is_array($content)                   => json_decode($content, true),
				in_array($format, ["text", "html"]) && !is_string($content) => json_encode($content),
				default                                                     => $content
			};
		}
		$response->setPayload($content, $format === "image-base64");
		return $response;
	}
	
	/**
	 * @param  bool $status
	 * @param  string $message
	 * @return StatusResponse
	 */
	public function createStatusResponse(bool $status, string $message): StatusResponse {
		$response = new StatusResponse();
		$response->setPayload($message);
		$response->setStatus($status);
		return $response;
	}
	
	private function populateData(): void {
		$this->supportedCorsHeaders = [
			ORIGIN,
			X_REQUESTED_WITH,
			CONTENT_TYPE,
			CONTENT_LENGTH,
			ACCEPT_RANGES,
			AUTHORIZATION
		];
		$accepts = explode(",", $this->header("Accept"));
		$accepts = array_filter($accepts, fn($v) => preg_match("#^[\w*]+/[\w*+-]+(?!(;.*)?;q=\d.\d)$#m", $v));
		$accepts = array_map(function($v) {
			if ($v === "text/plain")
				return "text";
			return preg_replace([
				"#^text/(\w+)$#",
				"#^(image)/\w+$#",
				"#^application/(\w+)(\+\w+)?$#"
			], "$1", $v);
		}, $accepts);
		$accepts = array_filter($accepts, fn($v) => $v !== "*/*");
		$format = $this->get("format");
		if ($format && !is_array($format))
			$accepts[] = $format;
		array_push($accepts, "json", "text");
		$this->formats = array_unique($accepts);
	}
	
}
