<?php

namespace App\Controller;

use System\Configuration as Cfg;
use System\Controller;
use System\Http\Response\Response;

class OptionsController extends Controller {

	public function isLogged(bool $isInternal = false): Response|bool {
		$isLogged = isset($_SESSION[Cfg::getConfig("api.session")]);
		if ($isInternal)
			return $isLogged;
		return $this->json(["logged_in"=>$isLogged]);
	}

//	public function login(): ApiResponse {
//		$auth = firstNonNull(Autoloader::getAuths()['user']);
//		$mysqli = DatabaseConnection::getInstance();
//		$mysqli->prepare("SELECT * FROM user WHERE password = ? LIMIT 1;");
//		$auth = $mysqli->execute("s", [$auth]);
//		if (count($auth) === 1) {
//			$_SESSION[Autoloader::getConfig('api.session')]['username'] = $auth[0]['username'];
//			$_SESSION[Autoloader::getConfig('api.session')]['password'] = $auth[0]['password'];
//			return ApiResponse::createResponse(["success"=>true]);
//		} else
//			return ApiResponse::createResponse(["success"=>false]);
//	}

	public function logout(): Response {
		if ($this->isLogged(true)) {
			session_unset();
			session_destroy();
		}
		return $this->json(["success"=>true]);
	}

	public function signIn(): Response {
		return $this->empty();
	}

	function validateToken(string $token = null): array {
		if (is_null($token))
			return ["success"=>false, "clearance"=>1];
		(new DeleteController($this->request))->updateTokens();
		$this->db->prepare("SELECT * FROM authtoken WHERE value = ?;");
		$result = $this->db->execute("s", [$token]);
		return ["success"=>count($result) != 0, "clearance"=>$result['clearance']];
	}

}
