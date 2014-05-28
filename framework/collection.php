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

class Collection
{
	private $query;
	private $bind_data;
	private $query_total;
	private $obj_types;
	private $obj_values;
	private $obj_data;
	private $map;
	private $total;

	public function __construct($query, $bind_data = array())
	{
		$this->query = preg_replace("/\\;/", '', $query);
		$this->bind_data = $bind_data;
		$this->query_total = "SELECT count(*) FROM ({$this->query}) AS subq";
		$this->total = db()->getValue($this->query_total, $bind_data);
		//set the object types for this object, e.g. array('InventoryLogEntry' => 'id')
		$this->obj_types = array();
		$this->obj_values = array();
		$this->obj_data = array();
	}

	public function bindType($key, $value)
	{
		$this->obj_types[$key] = $value;
	}

	public function bindValue($key, $value = null)
	{
		if ($value == null)
			$value = $key;
		$this->obj_values[$key] = $value;
	}

	public function bindData($key, $data)
	{
		$this->obj_data[$key] = $data;
	}

	private function setMap()
	{
		if (!$this->map) {
			$this->map = db()->getArray($this->query, $this->bind_data);
		}
	}

	//This function makes sure the user doesn't ask for a page which doesn't exist (too big)
	public function putWithinBounds($page, $per_page = 15)
	{
		//Cast $page to an integer
		$page = (int)$page;
		//$page has to be at least 1
		$page = max($page, 1);

		//max number of pages = [# of rows in MySQL results array] / $pagesize
		$maxPage = (int)ceil($this->total / $per_page);
		//$page has to be at least zero?
		$page = max(0, $page);
		//$page is either the page specified by the user or the biggest page number
		$page = min($page, $maxPage);

		return $page;
	}

	public function implodeMap($type)
	{
		if (is_array($this->map)) {
			return implode(', ', $this->getMap($type));
		}

		return '';
	}

	public function getMap($type = null)
	{
		$this->setMap();
		return $this->map;
	}

	public function getPage($page, $per_page = 15)
	{
		$start = ($page - 1) * $per_page;
		$start = $start < 0 ? 0 : $start;

		return $this->getRange($start, $per_page);
	}

	public function getRange($start, $length)
	{
		// might need to make this smarter
		$this->query = preg_replace("/LIMIT.*/i", "", $this->query);

		$limit = "LIMIT {$start}, {$length}";
		$this->query .= " {$limit}";

		$this->setMap();

		if (is_array($this->map)) {
			//call the buildObjectArray function to build an array of objects (e.g. items) of the specified length
			//function array_slice() extracts a segment of the $map array
			return $this->buildObjectArray();
		} else {
			//returns a slice of the array of objects (e.g. items) retrieved from MySQL
			return array();
		}
	}

	//Counts the number of rows in the map property (MySQL results array)
	public function count()
	{
		return $this->total;
	}

	public function getAll()
	{
		$this->setMap();

		return $this->buildObjectArray();
	}

	//builds an array of objects from the MySQL database, e.g. an array of items
	private function buildObjectArray($map = null)
	{
		if ($map === null) {
			$map = $this->map;
		}

		if (!is_array($map)) {
			return array();
		}

		//very simple load...  should be made more advanced.
		$data = array();

		//Recall: the format of the $map array is as follows:
		//  $map[0] = array('id' => '1', 'user_id' => '5', ...)
		//  $map[1] = array('id' => '2', 'user_id' => '6', ...)
		//  ...

		foreach ($map AS $key => $row) {
			//An example of the collection->obj_types property is array("id" => "Item")
			foreach ($this->obj_types AS $id => $type) {
				//$data is an array of objects
				//for example: This creates a new object (of the type Item class) for each row in the MySQL results
				//example: $data[0]['Item'] = new Item($row('id'))
				if (is_subclass_of($type, "StorageInterface")) {
					$data[$key]["StorageInterface"] = Storage::get($row[$id]);
				} else {
					$data[$key][$type] = new $type($row[$id]);
				}
			}

			foreach ($this->obj_values AS $id => $name) {
				$data[$key][$name] = $row[$id];
			}

			foreach ($this->obj_data AS $id => $value) {
				$data[$key][$id] = $value;
			}
		}

		//returns the $data array: an array of objects, e.g. items
		return $data;
	}

}