<?php

namespace App\Controller;

use System\Controller;

class PutController extends Controller {
	
	private static ?array $put = null;
	
	public function &var(): array {
		if (is_null(static::$put)) {
			static::$put = $_POST;
		}
		return static::$put;
	}
	
}
