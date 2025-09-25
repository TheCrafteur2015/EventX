<?php

namespace App\Controller;

use Exception;
use System\Controller;
use System\Http\Response\Response;

class RoleController extends Controller {
	
	public function getAll(): Response {
//		$this->db->query("SELECT * FROM role");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
	/**
	 * @throws Exception
	 */
	public function getById(int $id): Response {
		if ($id < 0)
			return $this->unsuccessful("Invalid ID");
//		$this->db->query("SELECT * FROM role WHERE id_role = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
	/**
	 * @throws Exception
	 */
	public function getUsersByRole(int $id): Response {
		if ($id < 0)
			return $this->unsuccessful("Invalid ID");
//		$this->db->query("SELECT * FROM users WHERE id_role = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
}
