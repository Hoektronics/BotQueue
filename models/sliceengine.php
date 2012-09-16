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

		public function delete()
		{
      //todo: support deleting a slicer.
			
			parent::delete();
		}
	}
?>