<?php

use JetBrains\PhpStorm\NoReturn;
use System\Configuration as Cfg;
use System\Localization\i18n;

function array_map_assoc(callable $callback, array $array): array|false {
	if (empty($array))
		return [];
	if (array_is_list($array))
		return false;
	return array_map($callback, array_keys($array), array_values($array));
}

function array_is_mixed(mixed $array): bool {
	if (!is_array($array))
		return false;
	$keys = array_keys($array);
	$numeric = false;
	$associative = false;
	foreach ($keys as $value) {
		if (is_numeric($value))
			$numeric = true;
		if (is_string($value))
			$associative = true;
		if ($numeric && $associative)
			return true;
	}
	return false;
}

function is_array_of_arrays(mixed $array): bool {
	if (!is_array($array))
		return false;
	return (bool) count(array_filter($array, "is_array"));
}

/**
 * This method returns true if the string is either null or strlen($value) == 0, otherwise false.
 * This prevents the string "0" from being counted as empty as it may be a value in some cases.
 */
function empty_string(?string $value): bool {
	return is_null($value) || strlen($value) === 0;
}

function array_contains(array $haystack, array $needle, bool $strict = false): bool {
	foreach ($needle as $obj)
		if (in_array($obj, $haystack, $strict))
			return true;
	return false;
}

function get_var_dump(mixed ...$mixed): string {
	ob_start();
	var_dump(...$mixed);
	return ob_get_clean();
}

function is_image(string $data): bool {
	$image = imagecreatefromstring($data);
	if (!$image)
		return false;
	else {
		imagedestroy($image);
		return true;
	}
}

function get_image_content(?string $data): string|false {
	if ($data === null)
		return "";
	if (file_exists($data))
		return file_get_contents($data);
	$image = imagecreatefromstring($data);
	if ($image !== false) {
		imagedestroy($image);
		return $data;
	}
	return false;
}

function get_image_as_base64(string $uri): string|false {
	if (file_exists($uri))
		return base64_encode(get_image_content($uri));
	return false;
}

function get_image_type(string $uri): ?string {
	if (file_exists($uri)) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $uri);
		finfo_close($finfo);
		return $type;
	}
	return null;
}

function convertUri(string $uri): string {
	$uri = str_replace("/", DIRECTORY_SEPARATOR, $uri);
	return str_replace("\\", DIRECTORY_SEPARATOR, $uri);
}

/** @noinspection JSUnresolvedReference */
function html_build_page(string $title = "", string $body = "", array $inlineScripts = []): string {
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
				<title>'.$title.'</title>
				<link rel="stylesheet" href="'.$root.'/styles/all.css" media="all" type="text/css">
				<link rel="icon" href="'.$root.'/images/infoicon.ico" media="all" type="image/ico">
				<script src="/resources/scripts/jquery.min.js"></script>
			</head>
			<body>
				'.$body.'
				<script src="'.$root.'/scripts/all.js"></script>
				<script>
				$(function() {'.implode(PHP_EOL, $inlineScripts).'});
				</script>
			</body>
		</html>';
}

function firstNonNull(array $array): mixed {
	foreach($array as $val) {
		if(!is_null($val))
			return $val;
	}
	return null;
}

function get_subclasses(string $parentClass): array {
	$subclasses = [];
	foreach (get_declared_classes() as $class) {
		if (is_subclass_of($class, $parentClass))
			$subclasses[] = $class;
	}
	return $subclasses;
}

const REGEX_FILE_ANY = 1;
const REGEX_FILE_UPPER = 2;
const REGEX_FILE_LOWER = 4;
const REGEX_FILE_IGNORE_HIDDEN = 8;
const REGEX_FILE_HIDDEN = 16;
const REGEX_FILE_USE_EXTENSION = 32;
const REGEX_PATH_IGNORE_HIDDEN = 64;

function get_regex_by_flags(int $flags, string $ext): string {
	$regex = "";
	if (($flags & REGEX_FILE_HIDDEN) & ~($flags & REGEX_FILE_IGNORE_HIDDEN))
		$regex .= "[.";
	if ($flags & REGEX_FILE_IGNORE_HIDDEN)
		$regex = "";
	if ($flags & (REGEX_FILE_LOWER & (REGEX_FILE_UPPER | REGEX_FILE_LOWER))) {
		if ($regex === "")
			$regex .= "[";
		$regex .= "a-z";
	}
	if ($flags & (REGEX_FILE_UPPER & (REGEX_FILE_UPPER | REGEX_FILE_LOWER))) {
		if ($regex === "")
			$regex .= "[";
		$regex .= "A-Z";
	}
	if ($regex === "[")
		$regex = "";
	if ($regex !== "")
		$regex .= "]";
	if ($flags & REGEX_FILE_ANY || $flags === 0)
		$regex = ".*";
	else {
		$regex .= ".*";
		if ($flags & REGEX_FILE_USE_EXTENSION)
			$regex .= ".$ext";
	}
	return "/^$regex$/";
}

function get_files_recursively(string $directory, int $flags = 0, string $ext = "", array $files = []): array {
	$regex = get_regex_by_flags($flags, $ext);
	foreach(scandir($directory) as $file) {
		$path = $directory.DIRECTORY_SEPARATOR.$file;
		if ($file === "." || $file === "..")
			continue;
		if (is_dir($path)) {
			if (!($flags & REGEX_PATH_IGNORE_HIDDEN && str_starts_with($file, ".")))
				$files = get_files_recursively($path, $flags, $ext, $files);
		} else {
			if (preg_match($regex, $file))
				$files[] = $path;
		}
	}
	return $files;
}

function get_folders_recursively(string $dir, array $dirs = []): array {
	if (!is_dir($dir))
		throw new InvalidArgumentException("The provided directory is invalid: $dir");
	$dirs[] = realpath($dir);
	$folders = scandir($dir);
	foreach ($folders as $folder) {
		if ($folder === "." || $folder === "..")
			continue;
		$folder = $dir.DIRECTORY_SEPARATOR.$folder;
		if (is_dir($folder))
			$dirs = get_folders_recursively($folder, $dirs);
	}
	return $dirs;
}

function truncate_text(string $text, int $length): string|false {
	if ($length < 0)
		return false;
	if ($length >= strlen($text))
		return $text;
	return substr($text, 0, $length)."...";
}

function d(mixed ...$values): void {
	echo "<pre>";
	var_dump(...$values);
	echo "</pre>";
}

#[NoReturn]
function dd(mixed ...$values): void {
	echo "<pre>";
	var_dump(...$values);
	echo "</pre>";
	exit;
}

function is_cli(): bool {
	if (in_array(PHP_SAPI, ['cli', 'phpdbg'], true))
		return true;
	return ! isset($_SERVER['REMOTE_ADDR']) && ! isset($_SERVER['REQUEST_METHOD']);
}

function equals(mixed $needle, mixed $value, bool $strict = false): ?bool {
	if (!isset($needle) && !isset($value))
		return null;
	if (!isset($needle))
		$needle = null;
	if (!isset($value))
		$value = null;
	if ($strict)
		return $needle === $value;
	return $needle == $value;
}
