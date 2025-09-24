<?php

namespace System\Config;

class Paths {
	
	public readonly array $paths;
	
	public function __construct() {
		$this->paths = array_merge(get_folders_recursively(SYSTEMPATH), get_folders_recursively(APPPATH));
	}
	
}
