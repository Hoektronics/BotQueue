<?
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

/*
 * @return DatabaseSocket
 */
function db($key = null)
{
	return Database::getSocket($key);
}

class Database
{
	private static $sockets = array();

	private function __construct() {}

  /*
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
	private $link;

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

		//Call the function to connect to the MySQL server
		$this->reconnect();
		
		if ($this->link->error())
			trigger_error(error());
	}
	
	//This method actually calls the mysql_connect function to connect to the server and select the database
	//The link property of this object is set to the identifier returned by mysql_connect

	public function reconnect()
	{
		$this->link = new mysqli($this->host . ":" . $this->port, $this->user, $this->pass, RR_DB_NAME);
		if ($this->link->connect_errno) {
		  die("Failed to connect: " . $this->link->connect_error);
		}
	}

	public function error()
	{
		return $this->link->error();
	}
	
	//This method prepares a statement to be executed
	//Accepts as input an sql string and arguments
  //Returns a statement to be executed
	public function prepare($sql)
	{
    //TODO figure out how to track queries
//		if (TRACK_SQL_QUERIES)
//			self::$queries[] = $sql;

    $statement = $this->link->prepare($sql);
    return $statement;
	}

  public function execute($statement)
  {
    if (!$statement->execute()) {
      echo "Execute failed: (" . $statement->errno . ") " . $statement->error;
    }
    return $statement;
  }
	
//	public function insert($sql)
//	{
//		if (TRACK_SQL_QUERIES)
//			self::$inserts[] = $sql;
//
//		mysql_query($sql, $this->link);
//
//		return mysql_insert_id($this->link);
//	}
	
//	public function execute($sql)
//	{
//		if (TRACK_SQL_QUERIES)
//			self::$executes[] = $sql;
//
//		mysql_query($sql, $this->link);
//
//		return mysql_affected_rows($this->link);
//	}
	
	public function ping()
	{
    if(!$this->link->ping())
      $this->reconnect();
	}
	
	public function getArray($sql, $key = null, $life = null)
	{
		//check the cache first?
		if ($key !== null && $life !== null)
		{
			$data = CacheBot::get($key, $data, $life);
			if (is_array($data))
				return $data;
		}
		
		//okay, load it from db.
		//$rs now contains a resource that can be used to extract the results of the query
		$rs = $this->query($sql);

		//error?
		if (mysql_error())
			trigger_error(mysql_error() . ": $sql");

		//snag it - populate the $data array with the results of the mysql output
		//$data is a numerically indexed array where each row corresponds to one row of MySQL output
		//Each value (row) in data contains an associative array for a given row of MySQL output, for example: array('id' => '1', 'user_id' => '5', ...)
		//So now $data looks like this:
		//  $data[0] = array('id' => '1', 'user_id' => '5', ...)
		//  $data[1] = array('id' => '2', 'user_id' => '6', ...)
		//  ...

    //TODO figure out why $rs is a boolean
		while ($row = mysql_fetch_assoc($rs))
			$data[] = $row;
			
		//save it to cache?
		if ($key !== null && $life !== null)
			CacheBot::set($key, $data, $life);
		
		//return the $data array, which contains the results of the mysql query
		return $data;
	}
	
	public function getRow($sql, $key = null, $life = null)
	{
		//check the cache first?
		if ($key !== null && $life !== null)
		{
			$data = CacheBot::get($key, $data, $life);
			if (is_array($data))
				return $data;
		}
		
		$this->ping();
		
		//okay, load it from db.
		$data = mysql_fetch_assoc($this->query($sql));

		//error?
		if (mysql_error())
			trigger_error(mysql_error() . ": $sql");

		//save it to cache?
		if ($key !== null && $life !== null)
			CacheBot::set($key, $data, $life);

		return $data;
	}
	
	public function getValue($sql, $key = null, $life = null)
	{
		$row = $this->getRow($sql, $key, $life);

		//error?
		if (mysql_error())
			trigger_error(mysql_error() . ": $sql");
		
		if (is_array($row) && count($row))
			return array_shift($row);
		
		return null;
	}
	
	public function getLink()
	{
		return $this->link;
	}
	
	public static function drawDbStats()
	{
		echo "\nDB Activity.\n";

		if (!empty(self::$queries))
		{
			echo "Queries:\n";
			foreach (self::$queries AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		}
		else
			echo "No queries.\n";

		if (!empty(self::$inserts))
		{
			echo "Inserts:\n";
			foreach (self::$inserts AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		}
		else
			echo "No inserts.\n";
			
		if (!empty(self::$exections))
		{
			echo "Updates/Deletes:\n";
			foreach (self::$executions AS $query)
				echo str_replace("\t", "", trim($query)) . "\n\n";
		}
		else
			echo "No updates/deletes.\n";


		echo "\n";
	}
}

?>
