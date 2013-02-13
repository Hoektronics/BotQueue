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

/**
* @defgroup Cache EasyCache System
* @brief EasyCache is a simple way to do caching.
*
* Please note: CacheBot is a singleton class designed to make it easy for you
* to switch between caching methods seamlessly.  Whenever possible, you should
* route your calls through that class, instead of using an EasyCache class
* directly.
* @{
*/

/**
* this class is a wrapper that makes it easy to work with EasyCache, regardless
* of where you are or what caching method you use..
*/
class CacheBot
{
	/**
	* this is our EasyCache object
	*/
	private static $bot = null;

	/**
	* we're private so you can instantiate us.
	*/
	private function __construct()
	{
	}
	
	/**
	* get our cache object!
	*
	* @return an EasyCache based object.
	*/
	public static function getBot()
	{
		//is it legit?
		if (self::$bot instanceOf EasyCache)
			return self::$bot;
		//nope, default to no caching.
		else
		{
			self::setBot(new NoCache());
			return self::getBot();
		}
	}

	/**
	* set our cache object.
	*
	* @param $bot a class extended form EasyCache that implements basic caching.
	*/
	public static function setBot($bot, $prefix = '')
	{
		if ($bot instanceOf EasyCache)
			self::$bot = $bot;
		else
			throw new Exception('Cachebots must be a derivitive of EasyCache');
	}

	/**
	* get data from teh cache... passes it off to the cache object.
	* here to make use of autoload.
	*
	* @param $key the key to get from the cache.
	*/
	public static function get($key)
	{
		return self::$bot->get($key);
	}
	
	/**
	* set data in teh cache... passes it off to the cache object.
	* here to make use of autoload.
	*
	* @param $key the key to use to save our data under
	* @param $data the variable to cache
	* @param $life the life of the cache in seconds.
	*/
	public static function set($key, $data, $life = 3600)
	{
		return self::$bot->set($key, $data, $life);
	}

	/**
	* delete specific data from teh cache... passes it off to the cache object.
	* here to make use of autoload.
	*/
	public static function delete($key)
	{
		return self::$bot->delete($key);
	}
	
	/**
	* delete all the data from teh cache... passes it off to the cache object.
	* here to make use of autoload.
	*/
	public static function flush()
	{
		return self::$bot->flush();
	}
}

/**
* A class that handles creating / updating / saving of data to be cached.
*/
abstract class EasyCache
{
	/**
	* this is used to track how many cache hits we do per page.
	*/
	public static $hits = 0;

	/**
	* if we have cache tracking on, this will contain all the keys hit.
	*/
	private static $keys = array();

	/**
	* create an EasyCache object.  this object will do the actual caching.
	*/
	public function __construct()
	{
	}
	
	/**
	* get data from the cache
	*
	* @param $key the key of the data in the cache to get.  can be a string or an array of keys to fetch.
	*
	* @return if the data is found, it will return the data as the appropriate
	* object.  if not found, it will return false.  if you passed in an array,
	* the return will be an array with the same keys, with either data or false
	* for values, as appropriate.
	*/
	abstract public function get($key);

	/**
	* set the data to the cache
	*
	* @param $key the key to save the data under
	* @param $data the data to save to cache.  this can be anything, the
	* cachebot will automatically run any variable through serialize and
	* unserialize to make it as seamless as possible.
	* @param $life the time till it expires in seconds
	*/
	abstract public function set($key, $data, $life = 3600);

	/**
	* delete data from the cache
	*
	* @param $key the key to delete from teh cache
	*/
	abstract public function delete($key);

	/**
	* completely wipe the cache and start over
	*/
	abstract public function flush();

	/**
	* mark a hit in the cache.  if 'bj_track_cache' is set in the config, it
	* will also track which keys are hit.
	*
	* @param $key the key that you hit
	* @param $type the type of cache hit:  get, set, delete
	*/
	public static function markCacheHit($key, $type = 'get')
	{
		//increment the total number
		self::$hits++;

		//add it to our array
		if (TRACK_CACHE_HITS)
		{
			self::$keys['keys'][] = $key;
			self::$keys['type'][] = $type;
		}
	}

	/**
	* use then when you've enabled cache tracking.  it will spit out a list of
	* keys that have been hit, and link them to the Main module where you can
	* view them, and/or delete them.
	*/
	public static function drawCacheStats()
	{
	  if (!empty(self::$keys)) {
  		echo "\nCache Activity.\n";
  		foreach (self::$keys['keys'] AS $index => $key)
  		{
  			if (is_array($key))
  				echo "Get Keyset:" . implode(", ", $keys) . "\n";
  			else
  				echo ucfirst(self::$keys['type'][$index]) . ": $key\n";
  		}
  		echo "\n";
	  }
	}
}

/**
* cache data directly to memory.
*
* this is by far the best option for caching.  it uses memcached server
* (www.danga.com/memcached/) which is basically a daemon that stores your
* cached data to memory in a very efficient and fast manner.  you will need
* the memcached php extensions.  this will make your site lightning fast.
*/
class EasyMemCache extends EasyCache
{
	public $mc;
	
	/**
	* create our object.  where and how long to store it.
	*
	* @param $servers a string or array of server addresses in form 'server.name:port'
	*/
	public function __construct($servers = 'localhost:112211')
	{
		//error checking.
		if (!class_exists('Memcache'))
			throw new Exception('Memcache is not installed in PHP.');

		//call mommy.
		parent::__construct();
		
		//create our cache object
		$this->mc = new Memcache;
		
		//did we get a string?
		if (is_string($servers))
			$servers = array($servers);
		
		//connect to servers.
		if (!empty($servers))
			foreach ($servers AS $server)
				if (preg_match('/^(.*):([0-9]*)$/', $server, $matches))
					$this->mc->addServer($matches[1], $matches[2]);
		else
			throw new Exception('Weird servers passed to EasyMemCache.');
	}

	public function get($key)
	{
		self::markCacheHit($key, 'get');
		
		//get an array of keys.
		if (is_array($key))
		{
			$ret = array();

			//only do something if its no empty
			if (count($key))
			{
				//init.
				foreach ($key AS $id)
					$ret[$id] = false;
				
				//get all our keys.
				$data = $this->mc->get($key);
				
				//save our data.
				foreach ($data AS $index => $val)
					$ret[$index] = $val;
			}

			return $ret;
		}
		//return our single value
		else
			return $this->mc->get($key);
	}

	public function set($key, $data, $life = 3600)
	{
		self::markCacheHit($key, 'set');
		
		return $this->mc->set($key, $data, MEMCACHE_COMPRESSED, $life);
	}

	public function delete($key)
	{
		self::markCacheHit($key, 'delete');
		
		return $this->mc->delete($key);
	}

	public function flush()
	{
		$this->mc->flush();
	}
}

/**
* this class caches data to files in the filesystem.  it is good if you do not
* have access to memcache (which is the ideal caching solution).  The downside
* to this class is that it must look up each key individually, and you also
* have to include your CacheLife on gets, because there is no way to store that
* for the set call. it's generally not a bad option.
*/
class EasyFileCache extends EasyCache
{
	private $path;
	
	/**
	* create our object.  where and how long to store it.
	*
	* @param $path the path to store the object.  the hash of the data name
	* will be appended directly after this.
	* @param $life the life of the cached data in seconds.
	*/
	public function __construct($path = "/tmp/EasyCache/")
	{
		parent::__construct();
		
		$this->path = $path;

		//make sure our cache directory exists.
		if (!is_dir($this->path))
			mkdir($this->path, 0777, true);
	}
	
	/**
	* @param $name a unique name you assign to the data to identify it.
	*/
	public function get($key)
	{
		//if its an array... then we want want to lookup all the caches
		if (is_array($key))
		{
			$ret = array();

			//if there are keys, look them up individually (this is the downfall of file cache)
			if (count($key))
				foreach ($key AS $single)
					$ret[$single] = $this->get($single);
		
			return $ret;
		}
		//nope, just a single key...
		else
		{
			//mark it here because arrays all go here.
			self::markCacheHit($key, 'get');
			
			//get the cache.
			if ($this->isCached($key))
			{
				$data = file_get_contents($this->getCachePath($key));
				return unserialize($data);
			}
			else
				return null;
		}
	}

	/**
	* saves our data to the cache.
	* 
	* @param $key the key to identify the data.
	* @param $data the data to save.
	* @param $life the time till it expires in seconds
	*
	* @return the number of bytes written
	*/
	public function set($key, $data, $life = 3600)
	{
		self::markCacheHit($key, 'set');
		
		file_put_contents($this->getCachePath($key), serialize($data));
	}

	/**
	* deletes our cached data.
	*
	* @param $key the key identifying your data.
	*/
	public function delete($key)
	{
		self::markCacheHit($key, 'delete');
		
		$file = $this->getCachePath($key);

		if (file_exists($file))
			unlink($file);
	}

	public function flush()
	{
		//delete everything on the path.
		$cmd = "find $this->path -type f | xargs rm";
		Util::shellExec($cmd);
	}
	
	/**
	* determines if the data is still cached or not.  checks for nonexistant as
	* well as old/stale data.  if its old, it also cleans up.
	*
	* @param $key the key you assigned to this data.
	* @return bool on using cache or not
	*/
	protected function isCached($key)
	{
		$file = $this->getCachePath($key);

		//does it exist?
		if (file_exists($file))
		{
			//okay, now is it legit? (file cache lasts for 1 hour)
			$minAge = time() - 3600;
			if (filemtime($file) >= $minAge)
				return true;
			//nope, stale... kill it
			else
				$this->delete($key);
		}

		return false;
	}
	
	/**
	* get the full path to the cached page file.
	*
	* @param $key the key you assigned to the cache file
	*/
	protected function getCachePath($key)
	{
		return $this->path . sha1($key);
	}
}

/**
* this class caches data to a database!  if you cant access a file system or
* you want to do everything via db, then this is for you.  i dont use it very
* much, as i recommend memcache, but you could probably get pretty good
* performance out of a memory db.  keep in mind this will probably put alot of
* load on your db server.
*/
class EasyDBCache extends EasyCache
{
	private $table;
	
	/**
	* create our object.  where and how long to store it.
	*
	* @param $table - the name of the table in the database to store the cache.
	*/
	public function __construct($table = "easy_cache")
	{
		parent::__construct();
		
		$this->table = $table;
	}

	protected function createTable()
	{
		db()->execute("
			CREATE TABLE $this->table
			(
				`id` varchar(255) default '' not null,
				`data` text default '' not null,
				`expire_date` datetime not null,
				KEY(id)
			) ENGINE=InnoDB 
		");
	}
	
	/**
	* @param $name a unique name you assign to the data to identify it.
	*/
	public function get($key)
	{
		self::markCacheHit($key, 'get');
		
		//if its an array, then get all the keys
		if (is_array($key))
		{
			//init our array.
			$rval = array();
			
			//make sure we got some.
			if (count($key))
			{
				//init it incase we dont find it.
				foreach ($key AS $id)
					$rval[$id] = false;	
			
				//do our query
        $sql = "SELECT ec.*, ec.expire_date <= NOW() AS expired
					FROM $this->table ec
					WHERE ec.id IN ('?')";
        $statement = db()->prepare($sql);
        $statement->bind_param("s", implode("', '", $key));
				db()->execute($statement);

				//get them all
				while ($ar = $statement->get_result()->fetch_assoc())
				{
					//what one?
					$id = $ar['id'];
					
					//if we're expired... delete it.
					if ((int)$ar['expired'])
						$this->delete($id);
					//nope, we go it.. save it.
					else
						$rval[$id] = unserialize($ar['data']);
				}
			}

			//send the array of cached stuff back
			return $rval;
		}

		$ar = db()->getRow("
			SELECT ec.*, ec.expire_date <= NOW() AS expired
			FROM $this->table ec
			WHERE id = '$key'
		");

		if (is_array($ar))
		{
			//if we're expired... delete it.
			if ((int)$ar['expired'])
				$this->delete($key);
			else
				return unserialize($ar['data']);
		}

		//if we didnt return before, we will now =)
		return null;
	}

	/**
	* saves our data to the cache.
	* 
	* @param $key the key to identify the data.
	* @param $data the data to save.
	*
	* @return the number of bytes written
	*/
	public function set($key, $data, $life = 3600)
	{
		self::markCacheHit($key, 'set');
		
		//first delete our old data.
		$this->delete($key);

		//format our data.
		$data = db()->safe(serialize($data));

		//insert it!
		db()->execute("
			INSERT INTO $this->table
				(id, data, expire_date)
			VALUES
				('$key', '$data', DATE_ADD(NOW(), INTERVAL " . $life . " SECOND))
		");
	} 

	/**
	* deletes our cached data.
	*
	* @param $key the key identifying your data.
	*/
	public function delete($key)
	{
		self::markCacheHit($key, 'delete');
		
		db()->execute("
			DELETE FROM $this->table
			WHERE id = '$key'
		");
	}

	public function flush()
	{
		db()->execute("
			DELETE FROM $this->table
		");
	}
}

/**
* this class doesnt cache anywhere.  its here if you want to disable caching
* for testing purposes or whatnot.  keep in mind your site may not function
* correctly (or very fast...)
*/
class NoCache extends EasyCache
{
	public function get($key)
	{
		self::markCacheHit($key, 'get');
		return null;
	}
	
	public function set($key, $data, $life = 3600)
	{
		self::markCacheHit($key, 'set');
		return true;
	}
	
	public function delete($key)
	{
		self::markCacheHit($key, 'delete');
		return true;
	}

	public function flush()
	{
		return true;
	}
}

/*@}*/
?>