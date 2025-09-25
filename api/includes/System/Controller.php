<?php

namespace System;

use BadMethodCallException;
use Exception;
use JetBrains\PhpStorm\Deprecated;
use System\Database\DatabaseConnection;
use System\Http\Request;
use System\Http\Response\EmptyResponse;
use System\Http\Response\Response;
use System\Http\Response\StatusResponse;

abstract class Controller {
	
	protected Request $request;
	protected DatabaseConnection $db;
	
	private array $data;
	
	public function __construct(Request $request) {
		$this->request = $request;
		$this->data    = [];
		$this->db      = DatabaseConnection::getInstance();
	}
	
	public final function __call($method, $arguments) {
		foreach ($this->request->getParams() as $key=>$value) {
			$this->{$key} = $this->mapValues($value);
		}
		
		if (method_exists($this, $method)) {
			return $this->$method(...$arguments);
		}
		
		// Gérer le cas où la méthode n'existe pas
		throw new BadMethodCallException("La méthode $method n'existe pas.");
	}
	
	private function mapValues(mixed $value): mixed {
		$decoded = json_decode($value, true);
		if ($decoded === null && is_string($value))
			return $value;
		return $decoded;
	}
	
	public final function __get(string $name) {
		return @$this->data[$name] ?: null;
	}
	
	public final function __set(string $name, mixed $value): void {
		if (__CLASS__ !== Controller::class)
			return;
		$this->data[$name] = $value;
	}
	
	/**
	 * @throws Exception
	 */
	#[Internal]
	protected final function checkParams(string ...$params): void {
		foreach ($params as $key) {
			$value = $this->request->exists($key, "POST");
			if (!$value && !array_key_exists($key, $_FILES))
				throw new Exception("Missing file or parameter: $key");
		}
	}

	/**
	 * @throws Exception
	 */
	#[Internal]
	protected final function notNullParams(string ...$params): void {
		foreach ($params as $key) {
			$value = $this->request->get($key, POST);
			if ($value === null || $value === "")
				throw new Exception("This parameter may not be null: $key");
		}
	}

	/**
	 * This method defines a fallback value if the param value is empty, must be called before notNullParams().
	 */
	#[Internal]
	protected final function fallbackValue(string $key, mixed $fallback): void {
		if (empty_string($this->data[$key]))
			$this->data[$key] = $fallback;
	}

	/**
	 * This method escape the POST params.
	 */
	#[Internal]
	#[Deprecated]
	protected final function escapeParams(): void {
		foreach ($this->data as $key=>$value) {
			if (empty_string($value))
				$this->data[$key] = null;
		}
	}
	
	/**
	 * This method convert a query string to a json-ready one.
	 */
	#[Internal]
	protected final function sanitize(?string $query): string {
		if (is_null($query))
			return "";
		return htmlspecialchars_decode(urldecode(strtolower($query)));
	}
	
	/**
	 *
	 * json(["successful"=>true, "message"=>$message])
	 *
	 * @param  string $message
	 * @return StatusResponse
	 */
	#[Internal]
	protected final function successful(string $message): StatusResponse {
		return $this->request->createStatusResponse(true, $message);
	}
	
	/**
	 *
	 * json(["successful"=>false, "message"=>$message])->httpCode(424)
	 *
	 * @param  string $message
	 * @return StatusResponse
	 * @throws Exception
	 */
	#[Internal]
	protected final function unsuccessful(string $message): StatusResponse {
		return $this->request->createStatusResponse(false, $message)->httpCode(424);
	}
	
	/**
	 * @param string $content
	 * @return Response
	 */
	protected final function html(string $content): Response {
		return $this->request->createResponse($content);
	}
	
	/**
	 * @param  array $data
	 * @return Response
	 */
	protected final function json(array $data): Response {
		return $this->request->createResponse($data);
	}
	
	protected final function image(string $uri): Response {
		return $this->request->createResponse($uri);
	}
	
	protected final function empty(): EmptyResponse {
		return new EmptyResponse();
	}

}
