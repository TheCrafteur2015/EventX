<?php

namespace System;

use JsonSerializable;
use System\Config\ConnectionType;
use System\Config\Options;

class Configuration implements JsonSerializable {
	
	private static ?self $__instance = null;
	
	public static ?Logger $_LOGGER = null;
	
	public static ?Logger $_DEBUG = null;
	
	private Options $options;
	
	public readonly IArray $headers;
	
	private array $languages;
	
	public readonly ConnectionType $conn_type;
	
	public readonly string $request_path;
	
	public readonly string $request_method;
	
	public readonly bool $same_origin;
	
	public readonly string $server_ip;
	
	public readonly string $origin;
	
	public readonly string $base_dir;
	
	public readonly string $relative_uri;
	
	private array $configs;
	
	private array $paths;
	
	private function __construct(string $base_dir) {
		$this->headers = new IArray(getallheaders());
		$this->options = new Options();
		
		$this->request_path = $_SERVER['PATH_INFO'] ?? "/";
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		$this->server_ip = isConnected() ? gethostbynamel(gethostname())[1] : "127.0.0.1";
		$this->origin = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'];
		$this->base_dir = $base_dir;
		$this->relative_uri = $this->origin.dirname($_SERVER['SCRIPT_NAME']);
		
		$this->setLanguages();
		$this->setConnectionType();
		$this->setOriginState();
		$this->setDefaultConfigs();
	}
	
	private function setLanguages(): void {
		$languages = $this->headers->get("accept-language");
		$this->languages = array_filter(array_map(function($item) {
				if (str_contains($item, ";"))
					return substr($item, 0, strpos($item, ";"));
				return $item;
			}, explode(",", $languages)), fn($item) => $item !== "")+['forced'=>@$_GET['lang'], 'fallback'=>"en-US"];
	}
	
	private function setConnectionType(): void {
		$this->conn_type = match (true) {
			$this->headers->get("Sec-Fetch-Mode") === "navigate" => ConnectionType::WEB,
			$this->headers->get("Sec-Fetch-Mode") === "cors",
			$this->headers->get("Sec-Fetch-Mode") === "no-cors",
			strtolower($this->headers->get("X-Requested-With")) === "xmlhttprequest",
			strtolower($this->headers->get("X-KL-saas-Ajax-Request")) === "ajax_request" => ConnectionType::FETCH,
			default => ConnectionType::UNKNOWN,
		};
	}
	
	private function setOriginState(): void {
		$this->same_origin = match (true) {
			$this->headers->get("Sec-Fetch-Mode") === "cors",
			$this->headers->get("Sec-Fetch-Mode") === "no-cors",
			$this->headers->get("Sec-Fetch-Site") === "cross-site" => false,
			default => true,
		};
	}
	
	private function setDefaultConfigs(): void {
		$this->configs = [];
		$this->paths = [];
		foreach (glob($this->base_dir."/config/*.ini") as $file) {
			$configs = parse_ini_file($file, false, INI_SCANNER_TYPED) ?: [];
			foreach ($configs as $key=>$value) {
				if (isset($this->configs[$key])) {
					trigger_error("Config index '$key' is already defined, skipping value: $value");
				} else {
					$this->configs[$key] = $value;
				}
				if (str_starts_with($key, "path.")) {
					if (isset($this->paths[$key])) {
						trigger_error("Path index '$key' is already defined, skipping value: $value");
					} else {
						$this->paths[$key] = $value;
					}
				}
			}
		}
	}
	
	public static function getConfig(string $key): mixed {
		$instance = self::instance();
		if (!isset($instance->configs[$key])) {
			trigger_error("Config index '$key' does not exists.", E_USER_WARNING);
			return null;
		}
		return $instance->configs[$key];
	}
	
	public static function setConfig(string $key, mixed $value): mixed {
		$instance = self::instance();
		$old = self::getConfig($key);
		$instance->configs[$key] = $value;
		return $old;
	}
	
	public static function getDir(string $key): string {
		$instance = self::instance();
		if (!isset($instance->paths[$key]))
			trigger_error("Path index '$key' does not exists.", E_USER_WARNING);
		return $instance->base_dir.$instance->paths[$key];
	}
	
	public static function getRelativeDir(string $relative_path): string|false {
		$instance = self::instance();
		return realpath($instance->base_dir.DIRECTORY_SEPARATOR.$relative_path);
	}
	
	public static function preferredLanguage(): string {
		$instance = self::instance();
		$browser_lang = array_filter($instance->languages, "is_int", ARRAY_FILTER_USE_KEY);
		$first = reset($browser_lang);
		return $instance->languages['forced'] ?? ($first === false ? null : $first) ?? $instance->languages['fallback'];
	}
	
	public static function isBrowserLanguage(string $lang): bool {
		$instance = self::instance();
		$browser_lang = array_filter($instance->languages, "is_int", ARRAY_FILTER_USE_KEY);
		return in_array($lang, $browser_lang, true);
	}
	
	public static function instance(): self {
		if (self::$__instance === null)
			trigger_error("Uninitialized Configuration", E_USER_ERROR);
		return self::$__instance;
	}
	
	public static function init(string $base_dir): void {
		if (self::$__instance === null) {
			self::$__instance = new self($base_dir);
			self::$_LOGGER = new Logger("$base_dir/log/access.log");
			self::$_DEBUG = new Logger("$base_dir/log/debug.log");
		}
	}
	
	public function setDebug(bool $use_debug): void {
		$this->options->use_debug = $use_debug;
	}
	
	public function useDebug(): bool {
		return $this->options->use_debug;
	}
	
	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): array {
		return get_object_vars($this);
	}
}
