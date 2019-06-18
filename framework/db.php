<?php
/*
	This file is part of BotQueue.

	BotQueue is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	BotQueue is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

/**
 * @param $key string
 * @return DatabaseSocket
 */
function db($key = null)
{
	return Database::getSocket($key);
}

class Database
{
	private static $sockets = array();

	private function __construct()
	{
	}

	/**
	 * @param $key string
	 * @return DatabaseSocket
	 */
	public static function getSocket($key = null)
	{
		if ($key === null)
			$key = 'main';

		//RR_DB_USER, RR_DB_PASS, RR_DB_HOST and RR_DB_PORT are global variables in extensions/config.php
		if (self::$sockets[$key] === null)
			self::$sockets[$key] = new DatabaseSocket(RR_DB_USER, RR_DB_PASS, RR_DB_HOST, RR_DB_PORT);

		return self::$sockets[$key];
	}
}

class DatabaseSocket
{
	/** @var PDO $db */
	private $db;

	private $user;
	private $pass;
	private $host;
	private $port;

	private static $executes = array();
	private static $queries = array();
	private static $inserts = array();

	public function __construct($user, $pass = null, $host = 'localhost', $port = 3306)
	{
		$this->user = $user;
		$this->pass = $pass;
		$this->host = $host;
		$this->port = $port;
		$this->database = RR_DB_NAME;

		//Call the function to connect to the MySQL server
		$this->reconnect();
	}

	//This method actually calls the mysql_connect function to connect to the server and select the database
	//The link property of this object is set to the identifier returned by mysql_connect

	public function reconnect()
	{
		try {
			ob_start();
			$connect = "mysql:host=$this->host;dbname=".$this->database.";charset=utf8";
			$this->db = new PDO($connect, $this->user, $this->pass);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			ob_end_clean();
		} catch (PDOException $e) {
			throw new Exception("Failed to connect to database!", 0, $e);
		}
	}

	public function selectDb($database_name) {
		if($this->database !== $database_name) {
			$this->database = $database_name;
			$this->reconnect();
		}
	}

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }

	public function prepare($sql) {
		return $this->db->prepare($sql);
	}

	public function insert($sql, $data = array())
	{
		if (TRACK_SQL_QUERIES)
			self::$inserts[] = array($sql, $data);

		// prepared statements rock
		$stmt = $this->prepare($sql);
		$stmt->execute($data);

		return $this->db->lastInsertId();
	}

	public function execute($sql, $data = array())
	{
		if (TRACK_SQL_QUERIES)
			self::$executes[] = array($sql, $data);

		// prepared statements rock
		$stmt = $this->prepare($sql);
		$stmt->execute($data);

		return $stmt->rowCount();
	}

	public function ping()
	{
//		$pingSql = "SELECT 1";
//
//		try {
//			$this->query($pingSql);
//		} catch (PDOException $e) {
//			$this->reconnect();
//		}
	}

	public function query($sql, $data = array())
	{
		if (TRACK_SQL_QUERIES)
			self::$queries[] = $sql;

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($data);

			return $stmt;
		} catch(PDOException $e) {
			trigger_error("MySQL Error (". $e->getCode() ."): ". $e->errorInfo . " for SQL Code: ". $sql);
		}

		// We should never get here because of trigger_error
		return null;
	}

	public function getArray($sql, $params = array())
	{
		//okay, load it from db.
		//$rs now contains a resource that can be used to extract the results of the query
		$stmt = $this->query($sql, $params);

		//snag it - populate the $data array with the results of the mysql output
		//$data is a numerically indexed array where each row corresponds to one row of MySQL output
		//Each value (row) in data contains an associative array for a given row of MySQL output, for example: array('id' => '1', 'user_id' => '5', ...)
		//So now $data looks like this:
		//  $data[0] = array('id' => '1', 'user_id' => '5', ...)
		//  $data[1] = array('id' => '2', 'user_id' => '6', ...)
		//  ...
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			$data[] = $row;

		//return the $data array, which contains the results of the mysql query
		return $data;
	}

	public function getRow($sql, $params = array())
	{

		$this->ping();

		//okay, load it from db.
		$stmt = $this->query($sql, $params);
		/** @var $data array */
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		//error?
		//if (mysql_error())
		//	trigger_error(mysql_error() . ": $sql");

		return $data;
	}

	public function getValue($sql, $params = array())
	{
		$row = $this->getRow($sql, $params);

		if (is_array($row) && count($row))
			return array_shift($row);

		return null;
	}

	public static function drawDbStats()
	{
		echo "\nDB Activity.\n";

		if (!empty(self::$queries)) {
			echo "Queries:\n";
			foreach (self::$queries AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		} else
			echo "No queries.\n";

		if (!empty(self::$inserts)) {
			echo "Inserts:\n";
			foreach (self::$inserts AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		} else
			echo "No inserts.\n";

		if (!empty(self::$executes)) {
			echo "Updates/Deletes:\n";
			foreach (self::$executes AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		} else
			echo "No updates/deletes.\n";


		echo "\n";
	}
}