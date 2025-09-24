<?php

header("Content-Type: text/plain; charset=utf-8");

$context = stream_context_create([
	'http'=>[
		'header'=>[
			"X-Requested-With: XMLHttpRequest"
		]
	]
]);

echo file_get_contents("http://127.0.0.1/REST/test.php", false, $context);

//var_dump($http_response_header);
