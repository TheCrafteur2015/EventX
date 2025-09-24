<?php

namespace App\Controller;

use Exception;
use Imagick;
use ImagickException;
use System\ApacheAPI;
use System\Configuration;
use System\Controller;
use System\Http\Response\Response;

class ImageController extends Controller {
	
	/**
	 * Checks if an image file hasn't already been uploaded.
	 *
	 * @throws ImagickException
	 * @throws Exception
	 */
	protected function checkImageAlreadyUploaded(array $file): string {
		$this->db->prepare("SELECT COUNT(*) FROM image WHERE img_src LIKE ?;");
		$resultName = $this->db->execute("s", ["%" . $file['name']]);
		$image = new Imagick($file['tmp_name']);
		$hash = $image->getImageSignature();
		$this->db->prepare("SELECT COUNT(*) FROM image WHERE img_hash = ?;");
		$resultHash = $this->db->execute("s", [$hash]);
		$result = $resultName[0]['COUNT(*)'] + $resultHash[0]['COUNT(*)'];
		if ($result != 0)
			throw new Exception("Image '{$file['name']}' already imported!");
		return $hash;
	}
	
	/**
	 * Checks if image name is not already used.
	 * @throws Exception
	 * @deprecated
	 */
	protected function checkImageName(string $name): void {
		$this->db->prepare("SELECT COUNT(*) FROM image WHERE img_name = ?;");
		$resultCount = $this->db->execute("s", [$name]);
		if ($resultCount[0]['COUNT(*)'] != 0)
			throw new Exception("Image name '$name' already used!");
	}
	
	private function addTag(int|string $id, string $tag): string|bool {
		try {
			$this->db->prepare("INSERT INTO img_tags VALUES (?, ?);");
			$this->db->execute("ds", [$id, $tag]);
			return true;
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $tag;
		}
	}
	
	/**
	 * @throws Exception
	 */
	public function insertImage(string $img_name, ?string $img_desc, ?string $repo, string $img_rating, string $tags, bool $do_f_list): Response {
		
		$repo = $repo ?? "";
		$this->checkParams("img_name", "img_path", "img_desc", "repo", "img_rating", "tags", "do_f_list");
		$this->fallbackValue("tags", "[]");
		$this->notNullParams("img_name", "img_path", "tags");
		
		$file = $_FILES['img_path'];
		$fileName = $file['name'];
		$filePath = DOCUMENT_ROOT.IMG_PATH.$repo.$file['name'];
		try {
//			$hash = $this->checkImageAlreadyUploaded($file);
			$hash = (new Imagick($file['tmp_name']))->getImageSignature();
			// $this->checkImageName($img_name);
			if (!move_uploaded_file($file['tmp_name'], $filePath))
				throw new Exception("Failed to import file '{$file['name']}'!");
			$this->db->prepare("INSERT INTO Image VALUES (DEFAULT, ?, ?, ?, ?, ?, ?);");
			$params = [$img_name, $img_desc, IMG_PATH.$repo.$fileName, $img_rating, $hash, $do_f_list];
			$this->db->execute("sssssb", $params);
			
			$tags = json_decode($tags, true);
			$id = $this->db->getLastInsertID();
			
			$errorTags = [];
			foreach ($tags as $tag)
				$errorTags[] = $this->addTag($id, $tag);
			$errorTags = array_filter($errorTags, fn($item) => $item !== true);
			
			$tempStr = "";
			if (count($errorTags) > 0)
				$tempStr = "The following tags (" . implode(", ", $errorTags) . ") could not be imported!";
			
			$smallFileName = truncate_text($fileName, 10);
			$smallName = truncate_text($img_name, 10);
			return $this->successful("Image '$smallFileName' named '$smallName' inserted successfully!\n$tempStr");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * This method returns an array of included tags from an array.
	 * @param array $tags
	 * @param array $exclusionChars
	 * @return array
	 */
	function getIncludedTags(array $tags, array $exclusionChars = ["!", "-"]): array {
		$tags = array_filter($tags, function($tag) use ($exclusionChars) {
			foreach ($exclusionChars as $exclusionChar)
				if (str_starts_with($tag, $exclusionChar))
					return false;
			return true;
		}, ARRAY_FILTER_USE_KEY);
		return $this->fixArrayIndexes(array_keys($tags));
	}
	
	/**
	 * This method returns an array of excluded tags from an array.
	 * @param array $tags
	 * @param array $exclusionChars
	 * @return array
	 */
	function getExcludedTags(array $tags, array $exclusionChars = ["!", "-"]): array {
		$tags = array_filter($tags, function($tag) use ($exclusionChars) {
			foreach ($exclusionChars as $exclusionChar)
				if (str_starts_with($tag, $exclusionChar))
					return true;
			return false;
		}, ARRAY_FILTER_USE_KEY);
		return $this->fixArrayIndexes(array_map(function($tag) {
			return substr($tag, 1);
		}, array_keys($tags)));
	}
	
	/**
	 * This method returns a reconstructed numeric array with fixed indexes.
	 * @param array $array
	 * @return array
	 */
	protected function fixArrayIndexes(array $array): array {
		$newArray = [];
		foreach ($array as $value)
			$newArray[] = $value;
		return $newArray;
	}
	
	private function mapImageResults(array $array): array {
		return array_map(function(array $subarray): array {
			$subarray['need_f_list'] = $subarray['need_f_list'] != 0;
			$subarray['tags'] = explode(",", $subarray['tags']);
			return $subarray;
		}, $array);
	}
	
	/**
	 * This method returns images with their respectives tags with a query and tags.
	 */
	public function getAllImages(string $query = null): Response {
		return $this->json($this->searchImages($this->sanitize($query)));
	}
	
	/**
	 * This method returns images by a specific, single-line query, comprised of text and tags.
	 *
	 * @param string $query
	 * @return Response
	 */
	public function extractAllImages(string $query): Response {
		return $this->json($this->searchImages($this->sanitize($query), true));
	}
	
	/**
	 * This method return an image by its id.
	 */
	public function getImageById(int $id, bool $isInternal = false): Response|array {
		$this->db->prepare("
		SELECT		i.*, GROUP_CONCAT(t.tag_name) AS tags
		FROM		image i LEFT JOIN img_tags it ON i.n_img = it.n_img
							LEFT JOIN tag t ON t.tag_name = it.tag_name
		WHERE		i.n_img = ?
		GROUP BY	i.n_img;");
		$results = $this->mapImageResults($this->db->execute("d", $id));
		if ($isInternal)
			return $results[0] ?? [];
		return $this->json($results[0] ?? []);
	}
	
	/**
	 * This method return an image by its id.
	 * @throws Exception
	 */
	public function showImageById(int $id): Response {
		$result = $this->getImageById($id, true);
		if (empty($result))
			return $this->empty();
		ob_end_clean();
		$uri = DOCUMENT_ROOT.$result['img_src'];
		if (isset($params['width']) && is_numeric($params['width'])) {
			$img = new Imagick($uri);
			$img->resizeImage(intval($params['width']), intval($params['width']), 0, 1, true);
			http_response_code(200);
			header("Content-type: image/".$img->getImageFormat());
			header("Accept-Ranges: bytes");
			echo $img->getImageBlob();
			exit;
		}
		return $this->image($uri);
	}
	
	/** @noinspection SqlAggregates */
	private function buildImageQuery(string|array $query): array {
		$sql = "
		SELECT   i.*, GROUP_CONCAT(t.tag_name) AS tags
		FROM     image i LEFT JOIN img_tags it ON i.n_img = it.n_img
		                 LEFT JOIN tag t ON t.tag_name = it.tag_name
		GROUP BY i.n_img
		HAVING 1";
		$types = "";
		$params = [];
		if (is_array($query) && !empty($query)) {
			foreach ($query as $string) {
				if (preg_match("/[!-]?[a-zA-Z\d]+/", $string)) {
					$sql .= " AND";
					if ($string[0] === "-" || $string[0] === "!") {
						$string = substr($string, 1);
						$sql .= " NOT";
					}
					$sql .= " (LOWER(i.img_name) LIKE ? OR
							   LOWER(img_desc) LIKE ? OR
							   LOWER(img_src)  LIKE ? OR
							   LOWER(img_rating) LIKE ? OR
							   LOWER(img_hash) LIKE ? OR
							   FIND_IN_SET(?, tags) > 0)";
					$types .= "ssssss";
					array_push($params, "%$string%", "%$string%", "%$string%", "%$string%", "%$string%", $string);
				}
			}
		} else if (is_string($query) && !empty($query)) {
			$sql .= " AND (LOWER(i.img_name) LIKE ? OR LOWER(img_desc) LIKE ? OR
					LOWER(img_src) LIKE ? OR LOWER(img_hash) LIKE ? OR LOWER(img_rating) LIKE ?)";
			$types .= "sssss";
			array_push($params, "%$query%", "%$query%", "%$query%", "%$query%", "%$query%");
		}
		if (is_string($query)) {
			$useTags = $this->getIncludedTags(ApacheAPI::Get());
			$useNotTags = $this->getExcludedTags(ApacheAPI::Get());
			foreach ($useTags as $tag) {
				$sql .= " AND FIND_IN_SET(?, tags) > 0";
				$types .= "s";
				$params[] = $tag;
			}
			foreach ($useNotTags as $tag) {
				$sql .= " AND FIND_IN_SET(?, tags) = 0";
				$types .= "s";
				$params[] = $tag;
			}
		}
		return ["sql"=>"$sql;", "types"=>$types ?: null, "params"=>$params ?: null];
	}
	
	public function searchImages(string $query, bool $useQueryAsArray = false): array {
		$requestData = $this->buildImageQuery($useQueryAsArray ? explode(" ", $query) : $query);
		$this->db->prepare($requestData['sql']);
		return $this->mapImageResults($this->db->execute($requestData['types'], $requestData['params']));
	}
	
}
