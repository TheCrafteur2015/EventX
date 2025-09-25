<?php

namespace System\Config;

class Constants {
	
	public readonly array $constants;
	
	public function __construct(string $root) {
		$this->constants = [
			'APPPATH'       =>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."App",
			'SYSTEMPATH'    =>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."system",
			'DEPENDENCYPATH'=>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."system".DIRECTORY_SEPARATOR."Dependencies",
			'LOGPATH'       =>$root.DIRECTORY_SEPARATOR."log",
			'DS'            =>DIRECTORY_SEPARATOR,
			'DOCUMENT_ROOT' =>$_SERVER['DOCUMENT_ROOT'],
			'IMAGEPATH'     =>$root.DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."images",
		];
	}
	
	
}
