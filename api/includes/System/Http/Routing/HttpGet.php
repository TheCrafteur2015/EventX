<?php

namespace System\Config;

use Attribute;
use System\Http\Routing\HttpMethod;

#[Attribute(Attribute::TARGET_METHOD)]
#[HttpMethod]
class HttpGet {
	
	public function __construct(public readonly string $uri) {}
	
}
