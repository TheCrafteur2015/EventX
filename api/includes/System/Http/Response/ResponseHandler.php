<?php

namespace System\Http\Response;

class ResponseHandler {
	
	public static int $code = 200;
	
	public static array $headers = [];
	
	public static function fatalError(): void {
		http_response_code(500);
	}
	
}
