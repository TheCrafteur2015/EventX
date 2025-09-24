<?php

use System\ApacheAPI;

require_once "autoload.php";

if (!defined("APACHE_API")) {
	echo "Something seems to be wrong with your <b>ApacheAPI</b> installation :/";
	exit;
}

$api = new ApacheAPI();
//$api->enableDebug();

$api->run();

/*
$response = Response::expect([
		'timestamp'=>date("Y-m-d H:i:s"),
		'message'=>$e->getMessage(),
		'stacktrace'=>$e->getTraceAsString(),
		'username'=>$e->getUser(),
		'token'=>gettype($e->getToken()) === "array" ? implode(", ", $e->getToken()) : $e->getToken(),
	], $e->getHttpCode());
*/
