<?php

namespace App\Controller;

use Exception;
use System\Configuration;
use System\Controller;
use System\Http\Response\Response;

class NaughtylistController extends Controller {
	
	protected function _getNaughtyList(string $query = null): Response {
		if (is_null($query)) {
			$this->db->query("SELECT * FROM naughtylist;");
			return $this->json($this->db->fetchAll());
		}
		return $this->empty();
	}
	
	/**
	 * @throws Exception
	 */
	protected function _insertNaughtyList(): Response {
		$this->checkParams("name", "size", "brand", "date", "link");
		$this->notNullParams("name", "brand", "link");
		$this->escapeParams();
		try {
			$this->db->prepare("INSERT INTO naughtylist VALUES (DEFAULT, ?, ?, ?, ?, DEFAULT, ?, NULL);");
			$this->db->execute("sssss", [$_POST['name'], $_POST['size'], $_POST['brand'], $_POST['date'], $_POST['link']]);
			return $this->successful("Product '{$_POST['name']}' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @param  ?string $query
	 * @return Response
	 */
	protected function getNaughtyList(): Response {
		$this->db->query("SELECT * FROM get_nl_products WHERE soft_deleted = FALSE;");
		return $this->json($this->db->fetchAll());
	}
	
	protected function getAllNaughtyList(): Response {
		$this->db->query("SELECT * FROM get_nl_products;");
		return $this->json($this->db->fetchAll());
	}
	
	protected function getScheduledNaughtyList(): Response {
		$this->db->query("SELECT * FROM get_nl_products WHERE scheduled_date != '0000-00-00' AND scheduled_date IS NOT NULL;");
		return $this->json($this->db->fetchAll());
	}
	
	protected function getNaughtyListHistory(): Response {
		$this->db->query("SELECT * FROM nl_history;");
		return $this->json($this->db->fetchAll());
	}
	
	protected function getPurchasedNaughtyList(): Response {
		$this->db->query("SELECT * FROM nl_purchased nlpu JOIN nl_product nlpr ON nlpu.npu_product_id = nlpr.np_id;");
		return $this->json($this->db->fetchAll());
	}
	
	protected function deleteNaughtyListProduct(int $index): Response {
		$this->db->query("SELECT * FROM get_nl_products WHERE id = $index;");
		$result = $this->db->fetchAll()[0];
		$this->db->prepare("INSERT INTO nl_history VALUES (DEFAULT, ?, ?, DEFAULT);");
		$this->db->execute("ss", [json_encode($result), "delete"]);
		$this->db->prepare("DELETE FROM nl_product WHERE np_id = ?;");
		return $this->json($this->db->execute("i", $index));
	}
	
	/**
	 * @throws Exception
	 */
	protected function insertNaughtyList(string $name, string $brand, string $brand_link, string $link, string $category, string $scheduled_date = null, string $preview_link = null, string $options = null, string $size = null, float $price = null): Response {
		$this->checkParams("name", "size", "options", "price", "brand", "brand_link", "scheduled_date", "link", "preview_link");
		$this->notNullParams("name", "brand", "brand_link", "link");
		$options = $options ?: null;
		$this->escapeParams();
		try {
			$this->db->prepare("CALL get_nl_brand_id(?, ?);");
			$nb_id = $this->db->execute("ss", [$brand, $brand_link])[0]['nb_id'];
			$this->db->prepare("INSERT INTO nl_product VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?, DEFAULT, ?, ?, DEFAULT);");
			$this->db->execute("ssssddsss", [$name, $size, $options, $category, $price, $nb_id, $scheduled_date, $link, $preview_link]);
			$nl_id = $this->db->getLastInsertID();
			$this->db->query("SELECT * FROM nl_product WHERE np_id = $nl_id;");
			$product = $this->db->fetchAll()[0];
			$this->db->prepare("INSERT INTO nl_history VALUES (DEFAULT, ?, ?, DEFAULT);");
			$this->db->execute("ss", [json_encode($product), "insert"]);
			return $this->successful("Naughty loot '$name' inserted successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @throws Exception
	 */
	protected function updateNaughtyList(string $name, string $brand, string $brand_link, string $link, string $category, int $id, string $scheduled_date = null, string $preview_link = null, string $options = null, string $size = null, float $price = null): Response {
		$this->checkParams("name", "size", "options", "price", "brand", "brand_link", "scheduled_date", "link", "preview_link");
		$this->notNullParams("name", "brand", "brand_link", "link");
		$options = $options ?: null;
		$this->escapeParams();
		try {
			$this->db->prepare("CALL get_nl_brand_id(?, ?);");
			$brand_id = $this->db->execute("ss", [$brand, $brand_link])[0]['nb_id'];
			$this->db->prepare("UPDATE nl_product SET np_name = ?, np_size = ?, np_options = ?, np_category = ?, np_price = ?, np_brand_id = ?, np_scheduled_date = ?, np_link = ?, np_preview_link = ? WHERE np_id = ?;");
			$this->db->execute("ssssddsssd", [$name, $size, $options, $category, $price, $brand_id, $scheduled_date, $link, $preview_link, $id]);
			return $this->successful("Naughty loot '$name' updated successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
	/**
	 * @throws Exception
	 */
	protected function purchaseNaughtyList(int $id, float $expected_price, float $real_price, string $additional_cost, string $date): Response {
		$this->checkParams("expected_price", "real_price", "additional_cost", "date");
		$this->notNullParams("expected_price", "real_price");
		$this->escapeParams();
		try {
			$this->db->prepare("INSERT INTO nl_purchased VALUES (DEFAULT, ?, ?, ?, ?, ?);");
			$this->db->execute("iddds", [$id, $expected_price, $real_price, $additional_cost, $date]);
			$this->db->prepare("UPDATE nl_product SET np_soft_deleted = ? WHERE np_id = ?;");
			$this->db->execute("ii", [1, $id]);
			return $this->successful("Naughty product ID $id purchased successfully!");
		} catch (Exception $e) {
			Configuration::$_LOGGER->log($e);
			return $this->unsuccessful($e->getMessage());
		}
	}
	
}
