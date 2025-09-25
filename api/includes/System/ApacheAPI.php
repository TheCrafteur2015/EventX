<?php

namespace System;

use Exception;
use JetBrains\PhpStorm\Deprecated;
use System\Configuration as Cfg;
use System\Exception\AuthenticationException;
use System\Exception\RouteException;
use System\Http\Request;
use System\Http\Response\Response;
use System\Http\Response\StatusResponse;
use System\Http\Routing\Router;
use System\Localization\i18n;
use function getallheaders;

/**
 * TODO: Redirection url
 * TODO: Error redirection
 * TODO: Authentication
 * TODO: General cleaning
 */
final class ApacheAPI {
	
	private static ApacheAPI $instance;
	
	public readonly Router $router;
	
	public readonly Request $request;
	
	/**
	 * Construct a new {@link ApacheAPI} instance.
	 */
	public function __construct() {
		self::$instance = $this;
		$this->router = new Router($this);
		$this->request = Request::new($this);
		ini_set('display_errors', "Off");
		ob_start();
	}
	
	public static function instance(): self {
		return self::$instance;
	}
	
	public function enableDebug(): void {
		ini_set('error_reporting', E_ALL);
		error_reporting(E_ALL);
		ini_set('display_errors', "On");
	}
	
	/**
	 * @throws RouteException
	 */
	public function run(): int {
		try {
			$response = $this->router->handle($this->request);
		} catch (Exception $e) {
			$response = new StatusResponse();
			$response->setPayload($e->getMessage());
			$response->setStatus(false);
		}
		return $response->send();
	}

	public final const ACTION_DEFAULT_ERROR = 1;
	public final const ACTION_ROOT_REDIRECT = 2;
	public final const ACTION_CUSTOM_ERROR = 4;
	public final const ACTION_CUSTOM_CALLBACK = 8;
	public final const ACTION_PROPAGATE_EXCEPTION = 16;

	/**
	 * @throws AuthenticationException
	 */
	#[Deprecated]
	public function init(int $actionOnError = 1, null|callable $callback = null, ?string $htmlError = null): ?Response {
//		if (($this->flags & self::AUTH_TOKEN) | ($this->flags & self::AUTH_USR_PWRD) && $method !== "OPTIONS") {
//			$code = $this->flags & self::AUTH_TOKEN ? 498 : 401;
//			if (!isset($_SESSION[Autoloader::getConfig("api.session")]) && $this->authToken === false)
//				throw new AuthenticationException(null, null,"No credentials provided!", 401);
//			if (!isset($_SESSION[Autoloader::getConfig("api.session")]) && !($this->authToken->isValid() && ($this->flags & self::AUTH_TOKEN)))
//				throw new AuthenticationException(firstNonNull(Autoloader::getAuths()['user']), Autoloader::getAuths()['token'],"No valid credentials provided!", $code);
//		}
		try {
			return $this->router->handle($this->request);
		} catch (RouteException $e) {
			switch ($actionOnError) {
				case self::ACTION_DEFAULT_ERROR:
					$this->handleRouteException($e);
					die(1);
				case self::ACTION_ROOT_REDIRECT:
					header("Location: ".self::getUrl());
					http_response_code(302);
					exit(0);
				case self::ACTION_CUSTOM_ERROR:
					echo $htmlError ?? "";
					die(1);
				case self::ACTION_CUSTOM_CALLBACK:
					echo $callback() ?? "";
					die(1);
				case self::ACTION_PROPAGATE_EXCEPTION:
					trigger_error($e->getMessage()."<br>".$e->getTraceAsString(), E_USER_ERROR);
				default:
					trigger_error("Unknown 'actionOnError' code: $actionOnError", E_USER_ERROR);
			}
		}
	}

	public static function getUrl(string $path = ""): string {
		return Cfg::instance()->relative_uri.Cfg::getConfig("api.entry").$path.(count($_GET)?"?".http_build_query($_GET):"");
	}

	public static function Get(): array {
		return array_filter($_GET, function($value, $key) {
			return !in_array($key, Cfg::getConfig("api.redirect.keep_get"));
		}, ARRAY_FILTER_USE_BOTH);
	}

	private function handleRouteException(RouteException $e): void {
		header("Content-Type: text/html; charset=utf-8");
		http_response_code($e->getHttpCode());
		$status = $e->getHttpCode()." - ".i18n::getString("exception.".$e->getHttpCode().".status");
		$message = i18n::getString("exception.".$e->getHttpCode().".message");
		if (getallheaders()['X-Requested-With'] === "XMLHttpRequest" || getallheaders()['X-KL-saas-Ajax-Request'] === "Ajax_Request") {
			echo json_encode(["http-code"=>$e->getHttpCode(), "http-status"=>$status, "http-message"=>$message]);
		} else {
			echo html_build_page($status, "
				<div>
					<h1>$status</h1>
					<h3>$message</h3>
				</div>", ['$("html").addClass("error-page");']);
		}
	}
	
	public static function saveLogs(string $body): void {
		# Ensure log files currently exists, and creates them if not
		$dir = Cfg::getDir("path.log");
		$files = ["latest_request.log", "latest_response.log", "latest_session.log"];
		foreach ($files as $file)
			if (!file_exists("$dir/$file"))
				touch("$dir/$file");
		
		# Request
		$content = $_SERVER['REQUEST_METHOD']." ".$_SERVER['REQUEST_URI']." ".$_SERVER['SERVER_PROTOCOL'].PHP_EOL;
		foreach (getallheaders() as $key => $value)
			$content .= "$key: $value".PHP_EOL;
		file_put_contents("$dir/latest_request.log", $content);
		
		# Response
		$httpCode = http_response_code();
		$content = $_SERVER['SERVER_PROTOCOL']." $httpCode ".HTTP_RESPONSE_TEXT[$httpCode].PHP_EOL;
		foreach (headers_list() as $value)
			$content .= $value.PHP_EOL;
		$content .= PHP_EOL.$body.PHP_EOL;
		file_put_contents("$dir/latest_response.log", $content);
		
		# Session
		$content = json_encode($_SESSION ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		file_put_contents("$dir/latest_session.log", $content);
	}
	
}
