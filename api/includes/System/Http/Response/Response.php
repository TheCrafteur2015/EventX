<?php

namespace System\Http\Response;

use http\Exception\RuntimeException;
use System\Http\HeaderList;

abstract class Response {
	
	protected mixed $value = null;
	
	protected int $httpCode = 200;
	
	private HeaderList $headers;
	
	private bool $outputStarted = false;
	
	public function __construct() {
		$this->headers = HeaderList::getResponseHeaders();
		$this->addHeader(CACHE_CONTROL, "no-store, no-cache, must-revalidate");
		$this->addHeader(EXPIRES, "Sat, 26 Jul 1997 05:00:00 GMT");
	}

	public final function addHeader(string $headerName, string $headerValue): void {
		if ($this->outputStarted)
			throw new RuntimeException("Cannot add headers after output started");
		$this->headers->set($headerName, $headerValue);
	}
	
	public abstract function setPayload(mixed $payload): void;
	
	public abstract function getPayload(): mixed;
	
	public abstract function append(mixed $payload): bool;
	
	public final function httpCode(int $httpCode): self {
		$this->httpCode = $httpCode;
		return $this;
	}
	
	protected final function sendHeaders(): void {
		$this->headers->send();
		$this->outputStarted = true;
	}
	
	public abstract function send(): int;
	
	/*
	 {
		try {
			$value = $this->value;
			if (is_array($value) || is_object($value))
				$value = json_encode($value);
			$httpCode = 200;
			if (empty($value))
				$httpCode = 204;
			http_response_code($httpCode);
			$this->headers[CONTENT_TYPE] = "text/plain";
			$this->headers[CONTENT_LENGTH] = strlen($value);
			$this->headers[ETAG] = '"'.md5($value).'"';
			$this->sendHeaders();
			echo $value;
			return true;
		} catch (Exception) {
			return false;
		}
	}
	 */
	
}

/*
class Response implements JsonSerializable {
	
	private mixed $content;

	private ?int $httpCode;
	
	private ?string $format;
	
	private ?string $image_ext;
	
	private ?string $err_msg;
	
	public readonly ?Route $route;
	
	protected HeaderList $headers;
	
	public readonly ApacheAPI $apacheAPI;
	
	public function __construct(ApacheAPI $apacheAPI) {
		$this->apacheAPI = $apacheAPI;
		$this->headers   = new HeaderList([]);
		$this->httpCode  = 200;
		$this->format    = "";
		$this->content   = null;
		$this->route     = null;
		$this->err_msg   = "";
		$this->image_ext = "";
	}
	
	public static function new(ApacheAPI $apacheAPI): self {
		$response = new self($apacheAPI);
		
		$request = $apacheAPI->request;
		if ($request->isCors()) {
			preg_match("#(?P<origin>https?://[a-zA-Z\d.]+)(:\d{1,5})?.*#", $request->header('Referer'), $matches);
			$matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
			$origin = $request->header('Origin') ?: $matches['origin'];
			$response->addHeader(ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
//			$response->addHeader(ACCESS_CONTROL_ALLOW_METHODS, strtoupper(implode(", ", $this->router->getMethods())));
			$response->addHeader(ACCESS_CONTROL_ALLOW_HEADERS, implode(", ", $request->supportedCorsHeaders));
		}
		$response->addHeader(CACHE_CONTROL, "no-store, no-cache, must-revalidate");
		$response->addHeader(EXPIRES, "Sat, 26 Jul 1997 05:00:00 GMT");
		
		return $response;
	}
	
	public function addHeader(string $headerName, string $headerValue): void {
		$this->headers->set($headerName, $headerValue);
	}
	
	public function httpCode(int $httpCode): self {
		$this->httpCode = $httpCode;
		return $this;
	}
	
	public function json(array $json): self {
		return $this->content($json, "json");
	}
	
	public function html(?string $html): self {
		return $this->content($html, "html");
	}
	
	public function text(string $text): self {
		return $this->content($text, "text");
	}
	
	public function xml(string $xml): self {
		return $this->content($xml, "xml");
	}
	
	public function image(string $image, string $extension): self {
		return $this->content($image, "image", $extension);
	}
	
	public function content(mixed $content, string $format, string $extension = null): self {
		$this->content = $content;
		$this->format = $format;
		$this->image_ext = $extension;
		/** @noinspection PhpSwitchStatementWitSingleBranchInspection *
		switch ($this->format) {
			case "image":
				$this->headers[ACCEPT-RANGES] = "bytes";
				break;
		}
		return $this;
	}
	
	public function append(mixed $content): self {
		if ($this->content === null)
			trigger_error("Cannot append to null!", E_USER_WARNING);
		else {
			if (is_array($this->content)) {
				if (is_array($content))
					$this->content = array_merge($this->content, $content);
				else
					$this->content[] = $content;
			} elseif (is_string($this->content)) {
				$this->content .= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
			}
		}
		return $this;
	}
	
	public function message(string $err_msg): self {
		$this->err_msg = $err_msg;
		return $this;
	}

	public function getContent(): mixed {
		$content = $this->content;
		return match ($this->format) {
			"html"=>$this->getHtmlPage($content),
			"text"=>$content,
			"xml"=>$this->getAsXML($content),
			"url"=>create_url(preg_replace("#^{$_SERVER['DOCUMENT_ROOT']}#", "", $content)),
			"json"=>json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),# ?? "[]",
			"image"=>get_image_content($content),
			"image-base64"=>"data:image/".$this->image_ext.";base64,".base64_encode(get_image_content($content)),
		};
	}
	
	private function getHtmlPage(?string $body): string {
		if (is_null($body))
			return "";
		$lang = i18n::getLocale();
		$root = Cfg::instance()->relative_uri;
		return '
		<!DOCTYPE html>
			<html lang="'.$lang.'">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<meta name="author" content="Gabriel Roche">
					<meta name="description" content="'.i18n::getString("html.head.description").'">
					<meta http-equiv="Content-Language" content="'.$lang.'">
					<title>'.i18n::getString("api.sandbox.head.title").'</title>
					<link rel="stylesheet" href="'.$root.'/styles/all.css" media="all" type="text/css">
					<link rel="icon" href="'.$root.'/images/infoicon.ico" media="all" type="image/ico">
					<script src="/resources/scripts/jquery.min.js"></script>
				</head>
				<body>
					'.$body.'
					<script src="'.$root.'/scripts/all.js"></script>
				</body>
			</html>';
	}
	
	private function getAsXML(array $data): string|bool {
		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><root />");
		$this->populateXML($xml, $data, $this->route->options['Xml-Element']);
		return $xml->asXML();
	}
	
	private function populateXML(SimpleXMLElement $xml, array $data, string $parent = null): void {
		switch (true) {
			case is_array_of_arrays($data) && $parent:
				array_walk($data, function ($value) use ($xml, $parent) {
//					var_dump($index, $value);
					$this->populateXML($xml->addChild($parent), $value);
				});
				break;
			case array_is_mixed($data):
			case array_is_list($data):
				$array = $xml->addChild("array");
				if ($parent !== null)
					$array->addAttribute("name", $parent);
				array_walk($data, function($value, $index) use ($array) {
					if (!is_array($value)) {
						$value = $value === false ? "false" : $value;
						$value = $value === true ? "true" : strval($value);
						$array->addChild("item", $value);
					} else
						$this->populateXML($array, $value, $index);
				});
				break;
			default:
				array_walk($data, function($value, $index) use ($xml) {
					if (!is_array($value)) {
						$value = $value === false ? "false" : $value;
						$value = $value === true ? "true" : strval($value);
						$xml->addChild($index, $value);
					} else {
						$this->populateXML($xml, $value, $index);
					}
				});
		}
	}
	
	public function getContentType(): ?string {
		return match ($this->format) {
			"html"=>"text/html",
			"json"=>"application/json",
			"xml"=>"application/xml", // FIXME: go to /images/azamoth?format=xml
//			"xml"=>"text/plain",
			"text", "image-base64", "url"=>"text/plain",
			"image"=>"image/".$this->image_ext,
		}."; charset=".match($this->format) {
			"image"=>"binary",
			default=>"utf-8"
		};
	}
	
	public function getHttpCode(): int {
		return $this->httpCode;
	}
	
	private function isRedirect(): bool {
		return isset($_GET['return_to']) && filter_var($_GET['return_to'], FILTER_VALIDATE_URL);
	}
	
	private function sendHeaders(): void {
		foreach ($this->headers as $name=>$value) {
			header("$name: $value");
		}
	}
	
	public function send(): bool {
		try {
			$content = $this->getContent();
			if (empty($content))
				$this->httpCode = 204;
			http_response_code($this->httpCode);
			switch (true) {
				case $this->isRedirect():
					$this->httpCode = 307;
					$this->headers[LOCATION] = urldecode($_GET['return_to']);
					break;
				case ($this->httpCode - 100) < 100 && ($this->httpCode - 100) >= 0:
					// 100
					break;
				case ($this->httpCode - 200) < 100 && ($this->httpCode - 200) >= 0:
					// 200
					break;
				case ($this->httpCode - 300) < 100 && ($this->httpCode - 300) >= 0:
					// 300
					break;
				case ($this->httpCode - 400) < 100 && ($this->httpCode - 400) >= 0:
					// 400
					break;
				case ($this->httpCode - 500) < 100 && ($this->httpCode - 500) >= 0:
					echo json_encode(['message'=>$this->err_msg]);
					exit;
			}
			$this->headers[CONTENT_TYPE] = $this->getContentType();
			$this->headers[CONTENT_LENGTH] = strlen($content);
			$this->headers[ETAG] = '"'.md5($content).'"';
			$this->sendHeaders();
			ApacheAPI::saveLogs($content);
			if ($this->isRedirect()) {
				$_SESSION[X_API_CONTENT] = $content;
				$_SESSION[X_API_CONTENT_TYPE] = $this->getContentType();
				return true;
			}
			echo $content;
			return true;
		} catch (Exception $e) {
			Cfg::$_LOGGER->log($e);
			return false;
		}
	}
	
	public static function expect(array $data, int $httpCode): self {
		return new self($data, null, "json", $httpCode);
	}
	
	/**
	 * @inheritDoc
	 *
	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
*/
