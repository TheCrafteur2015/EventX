<?php

namespace System\Config;

class Constants {
	
	public readonly array $constants;
	
	public function __construct(string $root) {
		$this->constants = [
			'APPPATH'       =>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."App",
			'SYSTEMPATH'    =>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."system",
			'DEPENDENCYPATH'=>$root.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."Dependencies",
			'LOGPATH'       =>$root.DIRECTORY_SEPARATOR."log",
			'DS'            =>DIRECTORY_SEPARATOR,
			'DOCUMENT_ROOT' =>$_SERVER['DOCUMENT_ROOT'],
			'IMG_PATH'      =>"/resources/pictures/",
			'DOC_PATH'      =>"/resources/documents/",
			'TMP_PATH'      =>"/tmp/",
		];
	}
	
	
}
