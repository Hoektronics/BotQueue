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

class Collection {
	private $query;
	private $query_total;
	private $obj_types;
	private $expiration;
	private $key;
	private $map;
	private $total;

	public function __construct($query, $obj_types, $expiration = null, $key = null) {
		$this->query        = preg_replace("/\;/", '', $query);
    $this->query_total  = "SELECT count(*) FROM ({$this->query}) AS subq";
    $this->total        = db()->getValue($this->query_total);
		//set the object types for this object, e.g. array('InventoryLogEntry' => 'id')
		$this->obj_types    = $obj_types;
		$this->expiration   = $expiration;		
		$this->key          = $key;
	}

  private function setMap() {
    if (!$this->map) {
      //if no key is defined, then create a sha1 key from the query string
  		if ($this->key === null) {
  			$this->key = sha1($this->query);
  		}
    
  		if ($this->expiration > 0) {
  			$this->map = db()->getArray($this->query, "{$this->key}.map", $expiration);
  		} else {
  		  //Call the getArray function (within the subclass DatabaseSocket, which is itself derived from the Database class)
  			//Note: If no object of type DatabaseSocket exits, then a new one will be created
  			//The db()->getArray method returns an array which contains the results of the mysql query
  			//collection->map now contains the results array of the MySQL query  			
  			$this->map = db()->getArray($this->query);
  		}
		}
  }

	//This function makes sure the user doesn't ask for a page which doesn't exist (too big)
	public function putWithinBounds($page, $per_page = 15) {
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

	public function implodeMap($type) {
		if (is_array($this->map)) {
			return implode(', ', $this->getMap($type));
		}

		return '';
	}

	public function getMap($type = null) {
	  $this->setMap();
		return $this->map;
	}
	
	public function getPage($page, $per_page = 15) {
	  $start = ($page - 1) * $per_page;
    $start = $start < 0 ? 0 : $start;
    
	  return $this->getRange($start, $per_page);
	}

	public function getRange($start, $length) {
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
	public function count() {
	  return $this->total;
	}

	public function getAll() {
	  $this->setMap();
	  
		return $this->buildObjectArray();
	}

	//builds an array of objects from the MySQL database, e.g. an array of items
	private function buildObjectArray($map = null) {
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
			//An example of the collection->obj_types property is array("Item" => "id")
			foreach ($this->obj_types AS $type => $id) {
				//$data is an array of objects
				//for example: This creates a new object (of the type Item class) for each row in the MySQL results
				//example: $data[0]['Item'] = new Item($row('id'))
				$data[$key][$type] = new $type($row[$id]);
			}
		}	

		//returns the $data array: an array of objects, e.g. items
		return $data;
	}

	public function bustCache() {
		CacheBot::delete("{$this->key}.map");
	}

}
?>
