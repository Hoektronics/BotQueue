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

	class Queue extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "queues");
		}
		
		public function getAPIData()
		{
			$d = array();
			$d['id'] = $this->id;
			$d['name'] = $this->getName();
			
			return $d;
		}
			
		public function canAdd()
		{
			return $this->isMine();
		}
		
		public function isMine()
		{
			return (User::$me->id == $this->get('user_id'));
		}	

		public function getName()
		{
			return $this->get('name');
		}

		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getUrl()
		{
			return "/queue:" . $this->id;
		}
		
		public function getJobs($status = null, $order = null)
		{
			if ($status !== null)
				$statusSql = " AND status = '{$status}'";
				
			$sql = "
				SELECT id
				FROM jobs
				WHERE queue_id = '{$this->id}'
					{$statusSql}
				ORDER BY user_sort, id DESC
			";
			return new Collection($sql, array('Job' => 'id'));
		}
		
		public function addGCodeFile($file, $qty = 1)
		{
			$job = new Job();
			$job->set('user_id', User::$me->id);
			$job->set('queue_id', $this->id);
			$job->set('file_id', $file->id);
			$job->set('name', $file->get('path'));
			$job->set('status', 'available');
			$job->set('start', date("Y-m-d H:i:s"));
			$job->save();
			
			return $job;
		}
	}
?>
