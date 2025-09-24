<?php

namespace App\Controller;

use Exception;
use mysqli_sql_exception;
use System\Config\HttpGet;
use System\Configuration;
use System\Controller;
use System\Http\Response\Response;
use System\Http\Routing\ControllerTag;

#[ControllerTag("cont", description: "test")]
class GetController extends Controller {
	
	/**
	 * This method returns all tags.
	 */
	#[HttpGet("tags/")]
	public function getAllTags(string $query = null): Response {
		if (is_null($query)) {
			$this->db->query("SELECT * FROM tag;");
			return $this->json($this->db->fetchAll());
		} else {
			$query = $this->sanitize($query);
			$this->db->prepare("SELECT * FROM tag WHERE LOWER(tag_name) LIKE ? OR
                        									LOWER(tag_desc) LIKE ? OR
                        									LOWER(display_name) LIKE ?;");
			return $this->json($this->db->execute("sss", "%$query%"));
		}
	}

	/**
	 * This method return a tag by its id.
	 */
	#[HttpGet(uri: "tags/")]
	public function getTagById(string $id): Response {
		$this->db->prepare("SELECT * FROM tag WHERE tag_name = ?;");
		return $this->json($this->db->execute("s", $id));
	}

	public function getAllUniverses(string $query = null): Response {
		if (is_null($query)) {
			$this->db->query("SELECT * FROM universe;");
			return $this->json($this->db->fetchAll());
		} else {
			$query = $this->sanitize($query);
			$this->db->prepare("
			SELECT *
			FROM   Universe
			WHERE  LOWER(u_name) LIKE ? OR LOWER(u_title) LIKE ? OR
			       LOWER(u_desc) LIKE ? OR u_type         LIKE ?;");
			return $this->json($this->db->execute("ssss", "%$query%"));
		}
	}

	/**
	* This method returns all civilians that matches the search query (any field).
	*/
	public function getAllCivilians(string $query = null): Response {
		$query = $this->sanitize($query);
		$this->db->prepare("
		SELECT *
		FROM   Civilian
		WHERE  LOWER(name)   LIKE ? OR LOWER(sources) LIKE ? OR
			   LOWER(images) LIKE ? OR LOWER(profile) LIKE ?;");
        return $this->parseCivilianArray($this->db->execute("ssss", "%$query%"));
    }

	/**
	* This method return a civilian by its id.
	*/
	public function getCivilianById(int $id): Response {
		$this->db->prepare("SELECT * FROM civilian WHERE civi_id = ?;");
        return $this->parseCivilianArray($this->db->execute("d", $id));
    }
	
	/**
	 * @param array $data
	 * @return Response
	 */
    private function parseCivilianArray(array $data): Response {
        if (!$data)
            return $this->empty();
        foreach ($data as $key=>$civilian) {
	        $data[$key]['sources'] = json_decode($civilian['sources'], true);
	        $data[$key]['images']  = json_decode($civilian['images'], true);
	        $data[$key]['profile'] = json_decode($civilian['profile'], true);
        }
        return $this->json($data);
    }

	/**
	 * This method returns the amount of images with the same template as name as the one passed in argument.
	 */
	public function getTemplateByName(string $name): Response {
		try {
			$this->db->prepare("SELECT * FROM image WHERE img_name LIKE ?;");
			$array = $this->db->execute("s", "$name%");
			$prLn = null;
			$max = 0;
			foreach ($array as $row) {
				$curLn = $row['img_name'];
				if (str_contains($curLn, "part"))
					$curLn = substr($curLn, 0, strpos($curLn, "part") - 1);
				$length = strlen($curLn);
				if ($prLn != null && substr($prLn, $length - 3, 3) !== substr($curLn, $length - 3, 3))
					$max++;
				elseif ($prLn == null)
					$max++;
				$prLn = $curLn;
			}
			return $this->json(["value"=>$max]);
		} catch (mysqli_sql_exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->json(["message"=>"Template doesn't exists yet!"]);
		} catch (Exception $all) {
			Configuration::$_LOGGER->log($all);
			return $this->json(["message"=>$all->getMessage()]);
		}
	}
	
}
