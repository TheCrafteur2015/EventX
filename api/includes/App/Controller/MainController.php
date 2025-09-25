<?php

namespace App\Controller;

use System\ApacheAPI;
use System\Controller;
use System\Http\Response\Response;

class MainController extends Controller {
	
	public function getRoutes(): Response {
		return $this->json(ApacheAPI::instance()->router->getRoutes());
	}
	
}
