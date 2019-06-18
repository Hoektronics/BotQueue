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

class Model
{
	/**
	 * @b Private.  The name of the database table its linked to.
	 * @private
	 */
	public $tableName;

	/**
	 * @b public.  Boolean if we use object caching or not. you should understand the object caching system before you use this.
	 */
	public $useObjectCaching = true;

	/**
	 * how long we cache the object data in seconds. defaults to an hour
	 */
	public static $objectCacheLife = 3600;

	/**
	 * The id that references the table.
	 */
	public $id = 0;

	/**
	 * @b Private. Array of fields that are dirty.
	 */
	private $dirtyFields = array();

	/**
	 * @b Private. Internal data array
	 */
	private $data = array();

	/**
	 * @b Private. Hydration state (ie: have we been loaded with real data?)
	 */
	private $hydrated = false;

	/**
	 * Creates a new BaseObject.
	 *
	 * @param $data mixed if $data is an integer id, in which case it will load the data from the database.
	 * If $data is an array, it will load the data from the array to the equivalent properties of the object.
	 *
	 * @param $tableName string the name of the table to reference.
	 */
	public function __construct($data, $tableName)
	{
		//set our table name...
		$this->tableName = $tableName;

		//omg noob... dont forget to load!
		$this->load($data);
	}

	/**
	 * This function translates the object to a string.
	 * @return string the string value for the funtction
	 **/
	public function __toString()
	{
		return $this->getName();
	}


    /**
     * This function helps us know if this model has an attribute defined
     * @param $name string is the name of the field
     * @return bool if that field exists on this object
     */
    public function has($name)
    {
        return array_key_exists($name, $this->data);
	}

	/**
	 * This function is for getting at the various data internal to the object
	 * @param $name string is the name of the field
	 * @return string the value from the data array or the id
	 */
	public function get($name)
	{
		return $this->data[$name];
	}

	/**
	 * Function to set data values for the object
	 * @param $name string is the name of the field
	 * @param $value string is the stored value
	 */
	public function set($name, $value)
	{
        //its not dirty if its the same.
	    if(array_key_exists($name, $this->data) && $this->data[$name] === $value) {
	        return;
        }

        $this->dirtyFields[$name] = 1;
        $this->data[$name] = $value;
	}

	/**
	 * This function handles loading the data into the object.
	 *
	 * @todo this will change in v2.2 to load the data from cache or look it up from the db.
	 *
	 * @param $data mixed either an id of an object or an array of data that
	 * represents that object.
	 */
	public function load($data)
	{
		//did we get an array of data to set?
		if (is_array($data))
			/* @var $data array */
			$this->hydrate($data);
		//nope, maybe its an id for the database...
		else if ($data)
			$this->loadData($data);
	}

	/**
	 * load our objects data.
	 *
	 * @depreciated this will be changed around in v2.2
	 * @todo remove and put comments / tags into load.  also make a loadfromdb function
	 */
	protected function loadData($id)
	{
		$id = (int)$id;

		if ($id > 0) {
			//set our id first.
			$this->id = $id;

			//try our cache.. if it works, then we're good.
			if (!$this->loadCacheData()) {
				$this->lookupData();

				if ($this->useObjectCaching)
					$this->setCache();
			}
		}
	}

	/**
	 * get our data from the db.
	 *
	 * @todo update this whole data loading process to be much smoother
	 */
	protected function lookupData()
	{
		//nope, load it normally.
		$data = $this->getData(true);
		if (count($data) && is_array($data))

			if (!empty($data))
				$this->hydrate($data);
	}

	/**
	 * load data from cache
	 */
	protected function loadCacheData()
	{
		//is it enabled?
		if ($this->useObjectCaching) {
			//get our cache data... is it there?
			$data = $this->getCache();
			if ($data) {
				//load it, and we're good.
				$this->hydrate($data);
				return true;
			}
		}

		return false;
	}

	/**
	 * This function handles saving the object.
	 *
	 * @return true on success, false on failure.
	 */
	public function save()
	{
		//we should do any cleanup if possible
		if ($this->isDirty())
			$this->clean();

		//save it to wherever
		$data = $this->saveData();

		//bust our cache.
		if ($this->useObjectCaching)
			$this->deleteCache();

		//of course we're hydrated!
		$this->hydrated = true;

		return $data;
	}

	/**
	 * This function handles any validation/cleaning of our data.
	 */
	public function clean()
	{
		foreach ($this->dirtyFields AS $field)
			$this->cleanField($field);
	}

	/**
	 * Clean a field's data.  called from clean()
	 *
	 * @param $field string the name of the field to clean.
	 */
	public function cleanField($field)
	{
		//by default... we don't clean anything
	}

	/**
	 * Tells us if the object is dirty or not.  Used to determine if we clean and/or if we need to save.
	 */
	public function isDirty()
	{
		return (bool)count($this->dirtyFields);
	}

	/**
	 * This function handles deleting our object.
	 *
	 * @return bool true on success, false on failure.
	 */
	public function delete()
	{
		//delete our cache.
		if ($this->useObjectCaching)
			$this->deleteCache();

		return $this->deleteDb();
	}

	/**
	 * This function gets an associative array of the object's members most
	 * commonly this will be from a db, but you never know.
	 *
	 * @param $useDb bool Should we check the database?
	 * @return array an associative array of the objects data.
	 */
	public function getData($useDb = true)
	{
		$data = array();

		if ($useDb) {
			$row = $this->getDbData();

			//format it properly.
			$data['id'] = $row['id'];
			unset($row['id']);
			$data['data'] = $row;
		} else {
			//format it nicely.
			$data['id'] = $this->id;
			$data['data'] = $this->data;
		}

		return $data;
	}

	/**
	 * This function sets all the data for the object.
	 *
	 * @param $data array an associative array of data.  The keys must match up with
	 * the object properties.
	 * @param $ignore bool the fields to ignore and not set
	 * @return boolean If data was set correctly
	 */
	//equivalent object members to that (if they exist)
	public function setData($data, $ignore = null)
	{
		//make sure we have an array here =)
		if (is_array($data)) {
			if (!is_array($ignore))
				$ignore = array($ignore);

			//okay loop thur our data...
			foreach ($data AS $key => $val) {
				//if we ignore it... continue
				if (in_array($key, $ignore))
					continue;

				//make sure this key exists for us....
				if ($key === 'id')
					$this->id = (int)$val;
				else
					$this->data[$key] = $val;
			}

			return true;
		} else
			return false;

	}

	/**
	 * This function handles saving the data to wherever.
	 *
	 * @return true on success, false on failure
	 */
	protected function saveData()
	{
		$this->saveDb();
	}

	/**
	 * This function gets all the member information from a database.
	 *
	 * @return array an associative array of data or false on failure.
	 */
	private function getDbData()
	{
		//make sure we have an id....
		if ($this->id) {
			$result = db()->getRow("SELECT * FROM $this->tableName WHERE id = ?", array($this->id));

			if (is_array($result))
				return $result;
		}

		return false;
	}

	/**
	 * This function saves the object back to the database.  It is a bit
	 * trickier.  It will smartly insert or update depending on if there is an
	 * id or not.  It also only saves the properties of the object that are
	 * named the same as the table fields, all automatically.
	 */
	private function saveDb()
	{
		//format our sql statements w/ the valid fields
		$fields = array();
		$sqlFields = "";

		//loop thru all our dirty fields.
		foreach ($this->dirtyFields AS $key => $foo) {
			//get our value.
			if (isset($this->data[$key]) && $key != 'id') {
				$val = $this->data[$key];

				//add it if we have it...
				$fields[] = $val;
				if($sqlFields != "")
					$sqlFields .= ", ";
				$sqlFields .= $key . "=?";
			}
		}
		$sqlFields .= " ";

		//update if we have an id....
		if (count($fields) && $sqlFields != "") {

			//update it?
			if ($this->id) {
				$sql = "UPDATE $this->tableName SET\n";
				$sql .= $sqlFields;
				$sql .= "WHERE id = '$this->id'\n";
				$sql .= "LIMIT 1";
				db()->execute($sql, $fields);
			} //otherwise insert it...
			else {
				$sql = "INSERT INTO $this->tableName SET " . $sqlFields;
				$this->id = db()->insert($sql, $fields);
			}
		}
	}

	/**
	 * This function deletes the object from the database.
	 *
	 * @return true on success, false on failure
	 */
	private function deleteDb()
	{
		//do we have an id?
		if ($this->id) {
			db()->execute("DELETE FROM $this->tableName WHERE id = ?", array($this->id));

			return true;
		}
		return false;
	}

	/**
	 * this function creates our key to use with caching.  no need to override
	 *
	 * @param $id int
	 * @return string a key used with CacheBot to cache the object.
	 */
	public function getCacheKey($id = null)
	{
		if ($id === null)
			$id = $this->id;

		return "object:" . get_class($this) . ":" . $id . ".data";
	}

	/**
	 * this is the function that gets the data we need saved to cache.  by
	 * default it saves our data, and will save the comments or tags objects if
	 * needed. its recommended to extend this to add data that you'd like cached
	 * by the object
	 *
	 * @param $deep bool
	 * @return array an array of data to cache
	 */
	protected function getDataToCache($deep = true)
	{
		$data = array();

		//obviously we want our data.
		$data['id'] = $this->id;
		$data['data'] = $this->data;

		return $data;
	}

	/**
	 * this function sets the data in the object from the data we retrieved from
	 * the cache.  it takes the data from the array and puts it in the object.
	 * you'll want to override this one if you added custome data in
	 * getDataToCache() and load it into the object.
	 *
	 * @param $data mixed the data we got from the cache
	 */
	public function hydrate($data)
	{
		//right format?
		if (is_array($data) && is_array($data['data']) && isset($data['id'])) {
			//okay, we're good.
			$this->hydrated = true;

			//get our id back
			$this->id = $data['id'];

			//obviously we want to load our data.
			$this->data = $data['data'];
		}
	}

	/**
	 * This function tells us if we've been hydrated or not.  IE: have we been loaded up with real data.
	 * useful for telling if we were able to find a model when loading by id, etc.
	 *
	 * @return boolean based on object state
	 */
	public function isHydrated()
	{
		return $this->hydrated;
	}

	/**
	 * this function gets our data from the cache. no need to override
	 */
	public function getCache()
	{
		return CacheBot::get($this->getCacheKey(), self::$objectCacheLife);
	}

	/**
	 * this function saves our data to the cache. no need to override
	 */
	public function setCache()
	{
		CacheBot::set($this->getCacheKey(), $this->getDataToCache(), self::$objectCacheLife);
	}

	/**
	 * this function deletes our data from the cache. no need to override
	 */
	public function deleteCache()
	{
		CacheBot::delete($this->getCacheKey());
	}

	public function getLink($text = null)
	{
		if ($text === null)
			$text = $this->getName();

		if (FORCE_SSL)
			$protocol = 'https://';
		else
			$protocol = 'http://';

		return '<a href="' . $protocol . SITE_HOSTNAME . $this->getUrl() . '">' . $text . '</a>';
	}

	public function getUrl()
	{
		return '';
	}

	public function getName()
	{
		return '';
	}

	public function copy()
	{
		$class = get_class($this);

		//actually copy it.
		/* @var $obj Model */
		$obj = new $class();
		foreach ($this->data AS $key => $value)
			$obj->set($key, $value);
		$obj->save();

		return $obj;
	}
}