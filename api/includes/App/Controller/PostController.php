<?php

namespace App\Controller;

use Exception;
use System\Configuration;
use System\Controller;
use System\Http\Response\Response;

class PostController extends Controller {
	
	/**
	 * This method upload a file with a given inputName to the temp dir.
	 */
	public function uploadTempFile(string $input_name): Response {
		if (isset($_FILES[$input_name])) {
			$file = $_FILES[$input_name];
			
			$tempFilePath = $file['tmp_name'];
			$originalFileName = $file['name'];
			
			$newPath = TMP_PATH . $originalFileName;
			
			if (move_uploaded_file($tempFilePath, DOCUMENT_ROOT . $newPath))
				return $this->html($newPath);
		}
		return $this->empty();
	}
	
	/**
	 * @throws Exception
	 */
	public function insertTest(): Response {
		$this->checkParams("name", "title", "desc", "type");
		$this->notNullParams("name", "type");
		
		return $this->successful("Test applied successfully!");
	}

	/**
	 * @throws Exception
	 */
	public function insertUniverse(): Response {
		$this->checkParams("name", "title", "desc", "type");
		$this->notNullParams("name", "type");
		extract($this->request->getPostValues());
		
		$this->db->prepare("INSERT INTO universe VALUES (?, ?, ?, ?);");
		try {
			$this->db->execute("ssss", [$name, $title, $desc, $type]);
			return $this->successful("Universe '$name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @throws Exception
	 */
	public function insertUniverseElement(): Response {
		$this->checkParams("u_name", "name", "desc", "links");
		$this->fallbackValue("links", "[]");
		$this->notNullParams("u_name", "name", "links");
		extract($this->request->getPostValues());
		
		$this->db->prepare("SELECT u_name FROM universe WHERE u_name = ?;");
		$universe = $this->db->execute("s", [$u_name]);
		try {
			if (count($universe) != 1)
				throw new Exception("This universe doesn't exists: $u_name");
			$this->db->prepare("INSERT INTO universe_elements VALUES (?, ?, ?, ?);");
			$this->db->execute("ssss", [$u_name, $name, $desc, $links]);
			return $this->successful("Universe element '$name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	public function insertDemon(): Response {
		$this->checkParams("name", "desc", "type");
		$this->notNullParams("name", "type");
		extract($this->request->getPostValues());
		
		$this->db->prepare("INSERT INTO demon VALUES (?, ?, ?);");
		try {
			$this->db->execute("sss", [$name, $desc, $type]);
			return $this->successful("Demon '$name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @return Response
	 * @throws Exception
	 */
	public function insertTag(): Response {
		$this->checkParams("tag_name", "tag_desc", "display_name");
		$this->notNullParams("tag_name");
		extract($this->request->getPostValues());
		if ($display_name === null)
			$display_name = $tag_name;
		
		$this->db->prepare("INSERT INTO tag VALUES (?, ?, ?);");
		try {
			$this->db->execute("sss", [$tag_name, $tag_desc, $display_name]);
			return $this->successful("Tag '$tag_name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @return Response
	 * @throws Exception
	 */
	public function insertWord(): Response {
		$this->checkParams("lang", "word", "translation");
		$this->notNullParams("lang", "word", "translation");
		extract($this->request->getPostValues());
		
		$this->db->prepare("INSERT INTO language VALUES (DEFAULT, ?, ?, ?);");
		try {
			$this->db->execute("sss", [$lang, $word, $translation]);
			return $this->successful("Word '$word' of language '$lang' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @return Response
	 * @throws Exception
	 */
	public function insertCivilian(): Response {
		$this->checkParams("civilian");
		$this->notNullParams("civilian");
		extract($this->request->getPostValues());
		
		$this->db->prepare("INSERT INTO civilian VALUES (DEFAULT, ?, ?, ?, ?);");
		try {
			$civilian = json_decode($civilian, true);
			$name = $civilian['name'];
			$sources = json_encode($civilian['sources']);
			$images = json_encode($civilian['images']);
			$this->db->execute("ssss", [$name, $sources, $images, json_encode($civilian['profile'])]);
			return $this->successful("Civilian '$name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}

	// $res = array("profile"=>[], "sex_profile"=>[]);
	// $excpts = ["table", "civilian_name", "source", "images"];
	// $nsfwValues = ["dick_length", "dick_diameter", "dick_shape", "dick_type", "dick_color", "knot_diameter", "balls_size", "cum_volume", "breast_size"];
	// foreach ($_REQUEST as $key=>$value) {
	// 	if (in_array($key, $excpts))
	// 		continue;
	// 	if (in_array($key, $nsfwValues))
	// 		$res['sex_profile'][$key] = escape($value);
	// 	else
	// 		$res['profile'][$key] = escape($value);
	// }
	// $name = escape(empty($_REQUEST['civilian_name']) ? NULL : $_REQUEST['civilian_name']);
	// $src = empty($_REQUEST['source']) ? NULL : $_REQUEST['source'];
	// $profile = escape(json_encode($res['profile']));
	// $sexProfile = escape(json_encode($res['sex_profile']));
	// $images = $_REQUEST['images'] === "[]" ? NULL : escape($_REQUEST['images']);
	//
	// adjustAutoincrement("profiles");
	// $mysqli->prepare("INSERT INTO profiles VALUES (DEFAULT, ?, ?, ?, ?, ?);");
	//
	// $types = "sssss";
	// $values = [$name, $src, $profile, $sexProfile, $images];

}
