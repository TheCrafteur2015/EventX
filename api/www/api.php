<?php

use System\ApacheAPI;

require_once "autoload.php";

if (!defined("APACHE_API")) {
	echo "Something seems to be wrong with your <b>ApacheAPI</b> installation :/";
	exit;
}

$api = new ApacheAPI();
$api->run();
