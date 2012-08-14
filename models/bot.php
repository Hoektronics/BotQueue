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

	class Bot extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "bots");
		}
		
		public function getName()
		{
			return $this->get('name');
		}

		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getAPIData()
		{
			$r = array();
			$r['id'] = $this->id;
			$r['queue_id'] = $this->get('queue_id'); //todo: implement bot_to_queues and make this better.
			$r['identifier'] = $this->get('identifier');
			$r['name'] = $this->getName();
			$r['manufacturer'] = $this->get('manufacturer');
			$r['model'] = $this->get('model');
			$r['status'] = $this->get('status');
			$r['last_seen'] = $this->get('last_seen');

			return $r;
		}

		public function getStatusHTML()
		{
			return "<span class=\"label " . $this->getStatusHTMLClass() . "\">" . $this->get('status') . "</span>";
		}
		
		public function getStatusHTMLClass()
		{
			$s2c = array(
				'working' => 'label-info',
				'complete' => 'label-success',
				'failure' => 'label-important',
				'maintenance' => 'label-warning',
				'offline' => 'label-inverse',
			);
			
			return $s2c[$this->get('status')];
		}

		
		public function getUrl()
		{
			return "/bot:" . $this->id;
		}
		
		public function getCurrentJob()
		{
			return new Job($this->get('job_id'));
		}
		
		public function getJobs()
		{
			$sql = "
				SELECT id
				FROM jobs
				WHERE bot_id = {$this->id}
				ORDER BY user_sort ASC
			";
			return new Collection($sql, array('Job' => 'id'));
		}
		
		public function isMine()
		{
			return (User::$me->id == $this->get('user_id'));
		}	
		
		public function canGrab($job)
		{
			//todo: fix me once we have the bot_to_queues table
			if ($this->get('user_id') != $job->getQueue()->get('user_id'))
				return false;

			if ($job->get('status') != 'available')
				return false;
			
			if ($this->get('status') != 'idle')
				return false;

			if ($this->get('job_id'))
				return false;

			return true;
		}
		
		public function grabJob($job)
		{
			$job->set('status', 'taken');
			$job->set('bot_id', $this->id);
			$job->set('start', date('Y-m-d H:i:s'));
			$job->save();

			usleep(1000 + mt_rand(100,500));
			$job = new Job($job->id);
			if ($job->get('bot_id') != $this->id)
				throw new Exception("Unable to lock job #{$job->id}");
			
			$this->set('job_id', $job->id);
			$this->set('status', 'working');
			$this->save();
		}
		
		public function canDrop($job)
		{
			if ($job->get('bot_id') == $this->id && ($job->get('status') == 'working' || $job->get('status') == 'failure'))
				return true;
			else
				return false;
		}
		
		public function dropJob($job)
		{
			$job->set('status', 'available');
			$job->set('bot_id', 0);
			$job->set('start', 0);
			$job->save();
			
			$this->set('job_id', 0);
			$this->set('status', 'idle');
			$this->save();
		}

		public function canComplete($job)
		{
			if ($job->get('bot_id') == $this->id && $job->get('status') == 'taken')
				return true;
			else
				return false;
		}
		
		public function completeJob($job)
		{
			$job->set('status', 'complete');
			$job->set('progress', 100);
			$job->set('end', date('Y-m-d H:i:s'));
			$job->save();
			
			$this->set('job_id', 0);
			$this->set('status', 'idle');
			$this->save();
		}
		
		public function getQueue()
		{
			return new Queue($this->get('queue_id'));
		}
	}
?>
