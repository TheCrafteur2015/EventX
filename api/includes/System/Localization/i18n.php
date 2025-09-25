<?php

namespace System\Localization;

use System\Configuration as Cfg;

class i18n {

	private static array $fallbackLocale = [];
	private static array $currentLocale = [];

	private static string $fallbackLocaleName = "";
	private static string $currentLocaleName = "";

	public static function loadLocale(string $lang, string $fallback = "en-US"): void {
		if (static::$currentLocaleName !== $lang) {
			static::$currentLocale = self::parseLocale(self::findLocaleFile($lang));
			static::$currentLocaleName = $lang;
		}
		if (static::$fallbackLocaleName !== $fallback) {
			static::$fallbackLocale = self::parseLocale(self::findLocaleFile($fallback));
			static::$fallbackLocaleName = $fallback;
		}
	}

	public static function getString(string $key): string {
		return trim(empty(trim(static::$currentLocale[$key])) ? static::$fallbackLocale[$key] : static::$currentLocale[$key]);
	}

	public static function getLocale(): string {
		return static::$currentLocaleName;
	}

	public static function getFallbackLocale(): string {
		return static::$fallbackLocaleName;
	}
	
	private static function findLocaleFile(string $locale): string {
		$locale = str_replace("_", "-", $locale);
		$files = scandir(Cfg::getDir("path.i18n"));
		$files = array_values(array_filter($files, fn($item) => preg_match("#^.*$locale.*\.lang$#i", $item)));
		if (!count($files))
			trigger_error("Locale file not found for: $locale", E_USER_WARNING);
		return $files[0];
	}

	private static function parseLocale(string $locale): array {
		$array = explode("\n", file_get_contents(Cfg::getDir("path.i18n") . "/$locale"));
		return array_reduce($array, function($previous, $item) {
			$index = strpos($item, "=");
			$previous[substr($item, 0, $index)] = substr($item, $index + 1);
			return $previous;
		}, []);
	}

}
