<?php

use System\Boot;

if (PHP_VERSION_ID < 80112)
	die("PHP version too old: ".PHP_VERSION);

require __DIR__."/includes/system/Boot.php";

$boot = new Boot(__DIR__);
