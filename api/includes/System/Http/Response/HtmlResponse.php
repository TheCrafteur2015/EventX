<?php

namespace System\Http\Response;

use function System\get_html_page;

class HtmlResponse extends TextResponse {
	
	public function __construct() {
		parent::__construct("html");
	}
	
	public function send(): int {
		$content = get_html_page($this->value);
		$this->addHeader(CONTENT_LENGTH, strlen($content));
		$this->addHeader(ETAG, '"'.md5($content).'"');
		$this->sendHeaders();
		echo $content;
		return 0;
	}
	
}
