<?php

namespace System\Database;

use mysqli;
use System\Configuration as Cfg;

class DatabaseConnection {

	private static ?DatabaseConnection $instance = null;

	private mixed $conn;

	private mixed $results;

	private bool $isOpened;

	private mixed $stmt;

	/**
	 * @param DBType $platform
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param int|null $port
	 */
	function __construct(private readonly DBTYPE $platform, string $host = "localhost", string $user = "", string $password = "", string $database = "", int $port = null) {
		switch ($this->platform) {
			case DBType::MYSQL:
				$this->conn = new mysqli($host, $user, $password, $database, $port ?? ini_get("mysqli.default_port"));
				if ($this->conn->connect_errno) {
					echo "Failed to connect to MySQL: {$this->conn->connect_error}";
					exit(1);
				}
				break;
			case DBType::POSTGRES:
				$this->conn = pg_connect("host=$host ".($port === null ? "" : "port=$port")." dbname=$database user=$user password=$password");
				break;
		}
		$this->isOpened = true;
	}

	public static function getInstance(): DatabaseConnection {
		if (self::$instance == null)
			self::$instance = new DatabaseConnection(DBType::MYSQL, Cfg::getConfig('db.host'),
																			Cfg::getConfig('db.username'),
																			Cfg::getConfig('db.password'),
																			Cfg::getConfig('db.database'));
		return self::$instance;
	}

	public function query(string $query): void {
		$this->results = null;
		switch ($this->platform) {
			case DBType::MYSQL:
				$this->results = $this->conn->query($query);
				break;
			case DBType::POSTGRES:
				$this->results = pg_query($this->conn, $query);
				break;
		}
	}

	/**
	 * @param string $query
	 * @return void
	 * @deprecated Unsafe and unmaintained, use prepare() instead.
	 */
	public function safeQuery(string $query): void {
		$this->query($this->escapeQuery($query));
	}

	/**
	 * @param string $query
	 * @return string
	 * @deprecated
	 */
	private function escapeQuery(string $query): string {
		$query = stripslashes($query);
		$query = str_replace("'", "\'", $query);
		return str_replace("-- ", "", $query);
	}

	public function prepare(string $query): bool {
		if ($query == null)
			return false;
		switch ($this->platform) {
			case DBType::MYSQL:
				$this->conn->next_result();
				$this->stmt = $this->conn->prepare($query);
				break;
			case DBType::POSTGRES:
				pg_prepare($this->conn, "", $query);
				break;
		}
		return true;
	}

	public function execute(string $types = null, mixed $params = null): null|string|array|bool {
		switch ($this->platform) {
			case DBType::MYSQL:
				if (!is_null($types) && !is_null($params)) {
					if (is_array($params))
						$this->stmt->bind_param($types, ...$params);
					else
						$this->stmt->bind_param($types, ...array_fill(0, strlen($types), $params));
				}
				$this->stmt->execute();
				$this->results = $this->stmt->get_result();
				return $this->results ? $this->results->fetch_all(MYSQLI_ASSOC) : false;
			case DBType::POSTGRES:
				return pg_fetch_all(pg_execute($this->conn, "", $params));
		}
		return null;
	}

	public function fetchAll(): ?array {
		if ($this->results == null)
			return null;
		return match ($this->platform) {
			DBType::MYSQL => $this->results->fetch_all(MYSQLI_ASSOC),
			DBType::POSTGRES => pg_fetch_all($this->results),
		};
	}
	
	
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getLastError(): string {
		return match ($this->platform) {
			DBType::MYSQL => $this->conn->error,
			DBType::POSTGRES => pg_last_error($this->conn),
		};
	}

	public function close(): void {
		if ($this->isOpened === true) {
			switch ($this->platform) {
				case DBType::MYSQL:
					$this->conn->close();
					break;
				case DBType::POSTGRES:
					pg_close($this->conn);
					break;
			}
			$this->isOpened = false;
		}
	}

	public function results(): mixed {
		return $this->results;
	}

	public function debug(): void {
		var_dump($this->conn, $this->platform, $this->results);
	}

	public function getLastInsertID(): null|int|string {
        return match ($this->platform) {
            DBType::MYSQL => $this->conn->insert_id,
            default => null,
        };
    }

	public function __destruct() {
		$this->close();
		self::$instance = null;
	}

}
