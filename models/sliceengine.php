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

class SliceEngine extends Model
{
    public function __construct($id = null)
    {
        parent::__construct($id, "slice_engines");
    }

    /**
     * @return string the Name of the engine
     */
    public function getName()
    {
        return $this->get('engine_name');
    }

    public function getAPIData($deep = true)
    {
        $r = array();
        $r['id'] = $this->id;
        $r['name'] = $this->getName();
        $r['path'] = $this->get('engine_path');
        $r['is_featured'] = $this->get('is_featured');
        $r['is_public'] = $this->get('is_public');
        $r['add_date'] = $this->get('add_date');
        if ($deep)
            $r['default_config'] = $this->getDefaultConfig()->getAPIData(false);
        $r['type'] = 'slicer';

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
		//todo Is anything other than id required here?
        $sql = "SELECT id
		    	FROM slice_engines
		    	ORDER BY engine_name ASC";

		$engines = new Collection($sql);
		$engines->bindType('id', 'SliceEngine');

		return $engines;
    }

    public static function getPublicEngines()
    {
        $sql = "SELECT id
		    	FROM slice_engines
		    	WHERE is_public = 1
		    	ORDER BY engine_name ASC";

		$engines = new Collection($sql);
		$engines->bindType('id', 'SliceEngine');

		return $engines;
    }

    public static function engine_exists($engine_path)
    {
        $sql = "
        SELECT id
        FROM slice_engines
        WHERE is_public = 1 and engine_path = ?";
        if (count(db()->getArray($sql, array($engine_path))) > 0) {
            return true;
        }
        return false;
    }

    public function getAllConfigs()
    {
        $sql = "SELECT id
		    	FROM slice_configs
		    	WHERE engine_id = ?
		    	ORDER BY config_name";

		$configs = new Collection($sql, array($this->id));
		$configs->bindType('id', 'SliceConfig');

		return $configs;
    }

    public function getMyConfigs()
    {
        $sql = "SELECT id
		    	FROM slice_configs
		    	WHERE engine_id = ?
		      	AND (user_id = ? OR id = ?)
		    	ORDER BY config_name";

		$configs = new Collection($sql, array($this->id, User::$me->id, $this->get('default_config_id')));
		$configs->bindType('id', 'SliceConfig');

		return $configs;
    }

    public function delete()
    {
        $configs = $this->getAllConfigs()->getAll();
        if (!empty($configs))
            foreach ($configs AS $row) {
                /* @var $sliceConfig SliceConfig */
                $sliceConfig = $row['SliceConfig'];
                $sliceConfig->delete();
            }

        parent::delete();
    }

	/**
	 * @param $engine_path
	 * @param $os
	 */
	public static function validOS($engine_path, $os) {
		$engineSQL = "SELECT id FROM slice_engines WHERE engine_path = ?";
		$engine_id = db()->getValue($engineSQL, array($engine_path));

		$sql = "INSERT IGNORE INTO engine_os(engine_id, os) VALUES(?,?)";

		db()->execute($sql, array($engine_id, $os));
	}
}