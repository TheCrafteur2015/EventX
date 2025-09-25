<?php

namespace System;

use System\Config\Constants;
use System\Config\OpenAPI;
use System\Config\Paths;
use System\Localization\i18n;

define("HTTP_RESPONSE_TEXT", [
	100=>"Continue",
	101=>"Switching Protocols",
	102=>"Processing",
	200=>"OK",
	201=>"Created",
	202=>"Accepted",
	203=>"Non-Authoritative Information",
	204=>"No Content",
	205=>"Reset Content",
	206=>"Partial Content",
	207=>"Multi-status",
	208=>"Already Reported",
	300=>"Multiple Choices",
	301=>"Moved Permanently",
	302=>"Found",
	303=>"See Other",
	304=>"Not Modified",
	305=>"Use Proxy",
	306=>"Switch Proxy",
	307=>"Temporary Redirect",
	400=>"Bad Request",
	401=>"Unauthorized",
	402=>"Payment Required",
	403=>"Forbidden",
	404=>"Not Found",
	405=>"Method Not Allowed",
	406=>"Not Acceptable",
	407=>"Proxy Authentication Required",
	408=>"Request Time-out",
	409=>"Conflict",
	410=>"Gone",
	411=>"Length Required",
	412=>"Precondition Failed",
	413=>"Request Entity Too Large",
	414=>"Request-URI Too Large",
	415=>"Unsupported Media Type",
	416=>"Requested range not satisfiable",
	417=>"Expectation Failed",
	418=>"I'm a teapot",
	422=>"Unprocessable Entity",
	423=>"Locked",
	424=>"Failed Dependency",
	425=>"Unordered Collection",
	426=>"Upgrade Required",
	428=>"Precondition Required",
	429=>"Too Many Requests",
	431=>"Request Header Fields Too Large",
	451=>"Unavailable For Legal Reasons",
	500=>"Internal Server Error",
	501=>"Not Implemented",
	502=>"Bad Gateway",
	503=>"Service Unavailable",
	504=>"Gateway Time-out",
	505=>"HTTP Version not supported",
	506=>"Variant Also Negotiates",
	507=>"Insufficient Storage",
	508=>"Loop Detected",
	511=>"Network Authentication Required",
]);

class Boot {
	
	public function __construct(string $root) {
		define("APACHE_API", true);
		session_start();
		Boot::loadConstants($root);
		Boot::loadDependencies();
		Boot::loadSystemConfig();
		spl_autoload_register(function(string $class) {
			$pathsClass = new Paths();
			$className = substr($class, strrpos($class, "\\") + 1);
			foreach ($pathsClass->paths as $path) {
				if (str_contains($path, "Config"))
					continue;
				$file = $path.DS.$className.".php";
				if (file_exists($file)) {
					require $file;
					break;
				}
			}
			OpenAPI::register($class);
		});
		
		Configuration::init($root);
		
		// TODO: temporary
		i18n::loadLocale("en-US");
		
		register_shutdown_function(function() {
			$error = error_get_last();
			if ($error && $error['type'] === E_ERROR) {
				echo "<pre>";
				echo "<b>Fatal error:</b> {$error['message']} in", PHP_EOL;
				echo "  <b>", $error['file'], "</b> on line <b>", $error['line'], "</b>";
				echo "</pre>";
			}
		});
		
//		$composer_autoload = Configuration::getRelativeDir("/vendor/autoload.php");
//		if ($composer_autoload)
//			require $composer_autoload;
//		ExceptionHandler::init();
//		EventHandler::init();
//		i18n::loadLocale(Cfg::preferredLanguage());
	}
	
	private static function loadConstants(string $dir): void {
		require realpath("$dir/includes/system/Config/Constants.php");
		$constants = new Constants($dir);
		if (!defined("APPPATH"))
			define("APPPATH", $constants->constants['APPPATH']);
		
		if (!defined("SYSTEMPATH"))
			define("SYSTEMPATH", $constants->constants['SYSTEMPATH']);
		
		if (!defined("DEPENDENCYPATH"))
			define("DEPENDENCYPATH", $constants->constants['DEPENDENCYPATH']);
		
		if (!defined("LOGPATH"))
			define("LOGPATH", $constants->constants['LOGPATH']);
		
		if (!defined("DS"))
			define("DS", $constants->constants['DS']);
		
		if (!defined("DOCUMENT_ROOT"))
			define("DOCUMENT_ROOT", $constants->constants['DOCUMENT_ROOT']);
		
		if (!defined("IMAGEPATH"))
			define("IMAGEPATH", $constants->constants['IMAGEPATH']);
		
		define("CONTENT_TYPE", "Content-Type");
		define("CONTENT_LENGTH", "Content-Length");
		define("ETAG", "ETag");
		define("X_API_CONTENT", "X-Api-Content");
		define("X_API_CONTENT_TYPE", "X-Api-Content-Type");
		define("ACCEPT_RANGES", "Accept-Ranges");
		define("LOCATION", "Location");
		define("ORIGIN", "Origin");
		define("X_REQUESTED_WITH", "X-Requested-With");
		define("AUTHORIZATION", "Authorization");
		define("CACHE_CONTROL", "Cache-Control");
		define("EXPIRES", "Expires");
		define("ACCESS_CONTROL_ALLOW_ORIGIN", "Access-Control-Allow-Origin");
		define("ACCESS_CONTROL_ALLOW_METHODS", "Access-Control-Allow-Methods");
		define("ACCESS_CONTROL_ALLOW_HEADERS", "Access-Control-Allow-Headers");
		
		define("POST", "POST");
		define("GET", "GET");
		define("REQUEST", "REQUEST");
	}
	
	private static function loadDependencies(): void {
		foreach (scandir(DEPENDENCYPATH) as $file) {
			if ($file === "." || $file === "..")
				continue;
			$file = DEPENDENCYPATH.DS.$file;
			if (is_file($file) && str_contains($file, ".php"))
				require $file;
		}
	}
	
	private static function loadSystemConfig(): void {
		foreach (scandir(SYSTEMPATH.DS."Config") as $file) {
			if ($file === "." || $file === ".." || $file === "Constants.php")
				continue;
			$file = SYSTEMPATH.DS."Config".DS.$file;
			if (is_file($file) && str_contains($file, ".php"))
				require $file;
		}
	}
	
}

function get_html_page(?string $body): string {
	$body = $body ?: "";
	$lang = i18n::getLocale();
	$root = Configuration::instance()->relative_uri;
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
