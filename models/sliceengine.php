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

	class SliceEngine extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "slice_engines");
		}
		
		public function getName()
		{
			return $this->get('engine_name');
		}

		public function getAPIData()
		{
			$r = array();
			$r['id'] = $this->id;
			$r['name'] = $this->getName();
			$r['path'] = $this->get('engine_path');
			$r['description'] = $this->get('engine_description');
      $r['is_featured'] = $this->get('is_featured');
      $r['is_public'] = $this->get('is_public');
      $r['add_date'] = $this->get('add_date');
      $r['default_config'] = $this->getDefaultConfig()->getAPIData();

			return $r;
		}

		public function getUrl()
		{
			return "/slicer:" . $this->id;
		}
		
		public function getDefaultConfig()
		{
		  return new SliceConfig($this->get('default_config_id'));
		}
		
		public static function getAllEngines()
		{
		  $sql = "
		    SELECT id
		    FROM slice_engines
		    ORDER BY engine_name ASC
		  ";
		  
		  return new Collection($sql, array('SliceEngine' => 'id'));
		}

		public static function getPublicEngines()
		{
		  $sql = "
		    SELECT id
		    FROM slice_engines
		    WHERE is_public = 1
		    ORDER BY engine_name ASC
		  ";
		  
		  return new Collection($sql, array('SliceEngine' => 'id'));
		}
		
		public function getAllConfigs()
		{
		  $sql = "
		    SELECT id
		    FROM slice_configs
		    WHERE engine_id = '{$this->id}'
		    ORDER BY config_name
		  ";
		  
		  return new Collection($sql, array('SliceConfig' => 'id'));
		}
		
		public function getMyConfigs()
		{
		  $sql = "
		    SELECT id
		    FROM slice_configs
		    WHERE engine_id = '{$this->id}'
		      AND (user_id = '" . User::$me->id . "' OR id = '" . $this->get('default_config_id') . "')
		    ORDER BY config_name
		  ";
		  
		  return new Collection($sql, array('SliceConfig' => 'id'));
		}
		
		public function delete()
		{
		  $configs = $this->getAllConfigs()->getAll();
		  if (!empty($configs))
		    foreach ($configs AS $row)
		      $row['SliceConfig']->delete();
		      
		  parent::delete();
		}
	}
?>