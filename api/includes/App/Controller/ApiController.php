<?php

namespace App\Controller;

use System\ApacheAPI;
use System\Controller;
use System\Http\MethodType;
use System\Http\Response\Response;
use System\Http\Routing\Route;

class ApiController extends Controller {
	
	public function swaggerV1(): Response {
		$router = ApacheAPI::instance()->router;
		$description = [
			"openapi"=>"3.1.0",
			"info"=>[
				"title"=>"Basic single file API",
				"license"=>[
					"name"=>"MIT",
					"identifier"=>"MIT"
				],
				"version"=>"1.0.0"
			],
			"servers"=>[
				[
					"url"=>"http://localhost/REST/api.php",
					"description"=>"API server"
				]
			],
			"paths"=>[],
			"components"=>[
				"schemas"=>[
					"Product"=>[
						"title"=>"Product",
						"description"=>"A Product.",
						"type"=>"object"
					]
				]
			],
			"securitySchemes"=>[
				"bearerAuth"=>[
					"type"=>"http",
					"description"=>"Basic Auth",
					"scheme"=>"bearer"
				]
			],
			"security"=>[
				[
					"bearerAuth"=>[]
				]
			],
			"tags"=>$router->getTags()
		];
		foreach ($router->getRoutes() as $method=>$routes) {
			foreach ($routes as $uri=>/** @var Route $route */$route) {
				$handler = $route->handler;
				$description['paths'][$uri] = [
					strtolower($method)=>[
						"tags"=>[],
						"summary"=>"",
						"description"=>"",
						"operationId"=>$handler->getType() === MethodType::ARRAY_CALLABLE ? $handler->getValue()[1] : "",
						"parameters"=>$this->getRouteParameters($uri),
					]
				];
			}
		}
		dd($router->getTags());
		return $this->json($description);
	}
	
	private function getRouteParameters(string $uri): array {
		$array = [];
		if (!preg_match_all("/{((?P<name>\w+)(:(?P<type>text|integer|float)(?P<optional>\?)?)?)}/", $uri, $matches))
			return $array;
		for ($i = 0; $i < count($matches["name"]); $i++) {
			$array[] = [
				"name"=>$matches["name"][$i],
				"in"=>"path",
				"description"=>"",
				"required"=>$matches["optional"][$i] !== "?",
				"schema"=>[
					"type"=>$matches["type"][$i]
				]
			];
		}
		return $array;
	}
	
	/*
	"/products/[product_id]"=>[
					"get"=>[
						"tags"=>[
							"products"
						],
						"summary"=>"Get a product",
						"operationId"=>"getProducts",
						"parameters"=>[],
						"responses"=>[
							"200"=>[
								"description"=>"successful operation",
								"headers"=>[
									"X-Rate-Limit"=>[
										"description"=>"calls per hour allowed by the user",
										"schema"=>[
											"type"=>"integer",
											"format"=>"int32"
										]
									]
								],
								"content"=>[
									"application/json"=>[
										"schema"=>[
											"\$ref"=>"#/components/schemas/Product"
										]
									]
								]
							],
							"401"=>[
								"description"=>"oops"
							]
						]
					]
				],
				"/products"=>[
					"post"=>[
						"tags"=>[
							"products"
						],
						"summary"=>"Add products",
						"description"=>"Add a product.",
						"operationId"=>"getProducts",
						"requestBody"=>[
							"description"=>"New product",
							"required"=>true,
							"content"=>[
								"application/json"=>[
									"schema"=>[
										"type"=>"array",
										"items"=>[
											"\$ref"=>"#/components/schemas/Product"
										]
									]
								]
							]
						],
						"responses"=>[
							"200"=>[
								"description"=>"successful operation",
								"content"=>[
									"application/json"=>[
										"schema"=>[
											"\$ref"=>"#/components/schemas/Product"
										]
									]
								]
							]
						]
					]
				]
	*/
}
