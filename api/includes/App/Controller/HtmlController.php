<?php

namespace App\Controller;

use System\Controller;
use System\Http\Request;
use System\Http\Response\Response;
use System\Localization\i18n;

class HtmlController extends Controller {
	
	private static ?array $get = null;
	
	public function &var(): array {
		if (is_null(static::$get)) {
			static::$get = $_GET;
		}
		return static::$get;
	}
	
	public function __construct(Request $request) {
		parent::__construct($request);
	}
	
	public function getApiSandbox(): Response {
		$html = '
		<main>
			<aside id="main-nav">
				<h2>'.i18n::getString("api.sandbox.title").'</h2>
				<ul id="routes">';
		
		$routesData = $this->request->apacheAPI->router->getRoutes();
		
		if ($this->request->get("format") === "json")
			return $this->json($routesData);
		
		foreach ($routesData as $method=>$routes) {
			$html .= '<li>'.$method.'<ul>';
			foreach ($routes as $route=>$handler) {
				$content = $route;
				if ($method === "GET" && !str_contains($route, "{"))
					$content = "<a href='{$_SERVER['REQUEST_URI']}$route' target='_blank'>$route</a>";
				$html .= "<li>$content</li>";
			}
			$html .= '</ul></li>';
		}
		
		$html .= '</ul></aside></main>';
		
//		$html = '
//		<div id="container">
//	        <h1>API Sandbox</h1>
//	        <form id="api-form">
//	            <label for="endpoint">Endpoint:</label>
//	            <input type="text" id="endpoint" name="endpoint" value="http://127.0.0.1/REST/api.php">
//
//	            <label for="method">Method:</label>
//	            <select id="method" name="method">
//	                <option value="GET">GET</option>
//	                <option value="POST">POST</option>
//	                <option value="PUT">PUT</option>
//	                <option value="DELETE">DELETE</option>
//	            </select>
//
//	            <label for="body">Request Body (JSON):</label>
//	            <textarea id="body" name="body" placeholder=\'{"key": "value"}\'></textarea>
//
//	            <button type="submit">Send Request</button>
//	        </form>
//
//	        <h2>Response:</h2>
//	        <pre id="response"></pre>
//	    </div>';

		return $this->html($html);
	}
	
}
