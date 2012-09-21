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

	class SliceJob extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "slice_jobs");
		}
		
		public function getName()
		{
			return "#" . $this->get('id');
		}

		public function getAPIData()
		{
			$r = array();
			$r['id'] = $this->id;
			$r['name'] = $this->getName();
      $r['input_file'] = $this->getInputFile()->getAPIData();
      $r['output_file'] = $this->getOutputFile()->getAPIData();
      $r['output_log'] = $this->get('output_log');
      $r['slice_config'] = $this->getSliceConfig()->getAPIData();
      $r['slice_config_snapshot'] = $this->get('slice_config_snapshot');
      $r['worker_token'] = $this->get('worker_token');
      $r['worker_name'] = $this->get('worker_name');
      $r['status'] = $this->get('status');
      $r['progress'] = $this->get('progress');
      $r['add_date'] = $this->get('add_date');
      $r['taken_date'] = $this->get('taken_date');
      $r['finish_date'] = $this->get('finish_date');

			return $r;
		}

		public function getUrl()
		{
			return "/slicejob:" . $this->id;
		}

		public function delete()
		{
      //todo: support deleting a slicer.
			
			parent::delete();
		}

		public function getStatusHTML()
		{
			return "<span class=\"label " . self::getStatusHTMLClass($this->get('status')) . "\">" . $this->get('status') . "</span>";
		}
		
		public static function getStatusHTMLClass($status)
		{
			$s2c = array(
				'slicing' => 'label-info',
				'complete' => 'label-success',
				'failure' => 'label-important'
			);
			
			return $s2c[$status];
		}
	}
?>