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
	private $obj_types;
	private $map;

	private $total;
	private $page;
	private $start;
	private $end;
	private $perPage;
	private $startPage;
	private $endPage;

	public function __construct($query, $bind_data = array())
	{
		$this->query = preg_replace("/\\;/", '', $query);
		$this->bind_data = $bind_data;
		$query_total = "SELECT count(*) FROM ({$this->query}) AS subq";
		$this->total = db()->getValue($query_total, $this->bind_data);

		// Set the object types for this object, e.g. array('InventoryLogEntry' => 'id')
		$this->obj_types = array()
		;
		// Assume the page starts out at 1, and there is only one page
		$this->page = 1;
		$this->startPage = 1;
		$this->endPage = 1;
		// If there's any entries at all, they start at 1
		if($this->total == 0)
			$this->start = 0;
		else
			$this->start = 1;
		$this->end = $this->total;
		$this->perPage = $this->total;
	}

	public static function none()
	{
		// Dummy SQL statement returns no rows.
		return new Collection("select 1 from dual where false");
	}

	public function bindType($key, $value)
	{
		$this->obj_types[$key] = $value;
	}

	private function setMap()
	{
		if (!$this->map) {
			$this->map = db()->getArray($this->query, $this->bind_data);
		}
	}

	// This function makes sure the user doesn't ask for a page which doesn't exist (too big or too small)
	private function putWithinBounds($page, $per_page)
	{
		// Max number of pages = [# of rows in MySQL results array] / [page size]
		$maxPage = (int)ceil($this->total / $per_page);
		// Except it has to be at least 1
		$maxPage = max($maxPage, 1);
		// $page has to be at least 1
		$page = max($page, 1);
		// $page has to be no larger than the max page
		$page = min($page, $maxPage);

		return $page;
	}

	public function getPage($page, $per_page = 15)
	{
		// No negative numbers
		$this->perPage = abs($per_page);
		$page = $this->putWithinBounds($page, $this->perPage);

		// The start for the query is 0 based
		$start = ($page - 1) * $this->perPage;

		$this->subRange($start, $this->perPage);

		return $this;
	}

	private function subRange($start, $length)
	{
		// might need to make this smarter
		$this->query = preg_replace("/LIMIT.*/i", "", $this->query);

		$limit = "LIMIT {$start}, {$length}";
		$this->query .= " {$limit}";

		// Reset the map, just in case
		$this->map = null;

		// If there's no entries, start and end are 0
		// $start is 0 based for MySql, $this->start
		// is 1 based for the paging functions
		$this->page = (int)(($start + 1) / $length) + 1;
		$this->startPage = 1;

		if($this->total == 0) {
			$this->start = 0;
			$this->end = 0;
			$this->endPage = 1;
		} else {
			$this->start = $start + 1;
			$this->end = min($start + $length, $this->total());
			$this->endPage = ceil($this->total() / $length);
		}
	}

	public function getRange($start, $length)
	{
		$this->subRange($start, $length);
		$this->setMap();

		return $this->buildObjectArray();
	}

	public function start()
	{
		return $this->start;
	}

	public function end()
	{
		return $this->end;
	}

	public function page()
	{
		return $this->page;
	}

	public function perPage()
	{
		return $this->perPage;
	}

	// Counts the number of rows in the subset
	public function count()
	{
		if($this->total == 0)
			return 0;
		return $this->end - $this->start + 1;
	}

	// Total number of rows, subset or not
	public function total()
	{
		return $this->total;
	}

	public function getMax($column) {
		$this->setMap();
		$max = null;

		foreach($this->map AS $unused => $row) {
			if($max === null || $max < $row[$column]) {
				$max = $row[$column];
			}
		}

		return $max;
	}

	public function getAll()
	{
		return $this->buildObjectArray();
	}

	//builds an array of objects from the MySQL database, e.g. an array of items
	private function buildObjectArray()
	{
		$this->setMap();

		if (!is_array($this->map)) {
			return array();
		}

		//very simple load...  should be made more advanced.
		$data = array();

		//Recall: the format of the $map array is as follows:
		//  $map[0] = array('id' => '1', 'user_id' => '5', ...)
		//  $map[1] = array('id' => '2', 'user_id' => '6', ...)
		//  ...

		foreach ($this->map AS $key => $row) {
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
		}

		//returns the $data array: an array of objects, e.g. items
		return $data;
	}

}