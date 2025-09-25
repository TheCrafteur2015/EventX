<?php

use System\Configuration;

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once "autoload.php";

header("Content-Type: text/plain; charset=utf-8");

var_dump(Configuration::instance());
