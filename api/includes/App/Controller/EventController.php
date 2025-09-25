<?php

namespace App\Controller;

use System\Controller;
use System\Http\Response\Response;

class EventController extends Controller {
	
	public function getAll(): Response {
//		$this->db->query("SELECT * FROM `event`");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function getById(int $id): Response {
//		$this->db->query("SELECT * FROM `event` WHERE `id_event` = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function search(string $text): Response {
//		$this->db->prepare("SELECT * FROM `event` WHERE `event_name` LIKE '%?%' OR
//                                                      `event_description` LIKE '%?%'");
//		return $this->json($this->db->execute("ss", [$text, $text]);
		return $this->json(["Sorry there's no database up here"]);
	}
	
	public function createdByUser(int $id): Response {
//		$this->db->query("SELECT * FROM `event` WHERE `id_user` = $id");
//		return $this->json($this->db->fetchAll());
		return $this->json(["Sorry there's no database up here"]);
	}
	
}
