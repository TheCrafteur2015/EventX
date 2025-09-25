<?php

namespace App\Controller;

use System\Controller;
use System\Http\Response\Response;

class UserController extends Controller {
	
	public function getAll(): Response {
//		$this->db->query("SELECT * From users");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	public function getById(int $id): Response {
//		$this->db->query("SELECT * From users WHERE id_user = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	public function search(string $text): Response {
//		$this->db->prepare("SELECT * From users WHERE mail LIKE ?");
//		return $this->json($this->db->execute("s", [$text]));
		return $this->json(["Sorry there's no database up here"]);
	}
	public function getProfile(int $id): Response {
//		$this->db->query("SELECT * From profile WHERE id_user = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	public function setProfile(int $id): Response {
//		$data = $this->request->getPostValues();
//		$this->db->prepare("INSERT INTO profile (firstname, lastname, profile_description, id_user, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE);
//		return $this->json($this->db->execute("sssis", [...$data, $id]));
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function setProfilePicture(int $id): Response {
	
	}
	
}
