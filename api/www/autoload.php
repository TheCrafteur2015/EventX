<?php

use System\Boot;

if (PHP_VERSION_ID < 80112)
	die("PHP version too old: ".PHP_VERSION);

require "../includes/system/Boot.php";

$boot = new Boot(dirname(__DIR__));
