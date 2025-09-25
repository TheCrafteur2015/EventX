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
//		$path = $this->uploadFile("profile_picture");
//		$this->db->prepare("UPDATE profile SET profile_picture = ? WHERE id_user = ?");
//		$this->db->execute("si", [$path, $id]);
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function registerToEvent(): Response {
//		extract($this->request->getPostValues());
//		$this->db->prepare("INSERT INTO registration VALUES (?, ?)");
//		$this->db->execute("ii", [$id_user, $id_event]);
//		return $this->successful("true");
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function unregisterToEvent(): Response {
//		extract($this->request->getPostValues());
//		$this->db->prepare("DELETE FROM registration WHERE id_user = ? AND id_event = ?");
//		$this->db->execute("ii", [$id_user, $id_event]);
//		return $this->successful("true");
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function wishlistEvent(): Response {
//		extract($this->request->getPostValues());
//		$this->db->prepare("INSERT INTO wishlist VALUES (?, ?)");
//		$this->db->execute("ii", [$id_user, $id_event]);
//		return $this->successful("true");
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function unwishlistEvent(): Response {
//		extract($this->request->getPostValues());
//		$this->db->prepare("DELETE FROM wishlist WHERE id_user = ? AND id_event = ?");
//		$this->db->execute("ii", [$id_user, $id_event]);
//		return $this->successful("true");
		return $this->json(["Sorry there's no database up here"]);
	}
	
}
