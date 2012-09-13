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
			$r['error_text'] = $this->get('error_text');
			
			$job = $this->getCurrentJob();
			if ($job->isHydrated())
				$r['job'] = $job->getAPIData();
			else
			  $r['job'] = array();

			return $r;
		}

		public function getStatusHTML()
		{
		  return Controller::byName('bot')->renderView('statusbutton', array('bot' => $this));
		}
		
		public function getStatusHTMLClass()
		{
			$s2c = array(
			  'idle' => 'success',
				'working' => 'info',
				'waiting' => 'warning',
				'error' => 'danger',
				'offline' => 'inverse',
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
		
		public function getJobs($status = null, $sortField = 'user_sort', $sortOrder = 'ASC')
		{
			if ($status !== null)
				$statusSql = " AND status = '{$status}'";
				
			$sql = "
				SELECT id
				FROM jobs
				WHERE bot_id = {$this->id}
					{$statusSql}
				ORDER BY {$sortField} {$sortOrder}
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
			$job->set('taken_time', date('Y-m-d H:i:s'));
			$job->save();

			usleep(1000 + mt_rand(100,500));
			$job = new Job($job->id);
			if ($job->get('bot_id') != $this->id)
				throw new Exception("Unable to lock job #{$job->id}");
			
			$this->set('job_id', $job->id);
			$this->set('status', 'working');
			$this->set('last_seen', date("Y-m-d H:i:s"));
			$this->save();
		}
		
		public function canDrop($job)
		{
			if ($job->get('bot_id') == $this->id && $this->get('job_id') == $job->id)
				return true;
			else
				return false;
		}
		
		public function dropJob($job)
		{
			$job->set('status', 'available');
			$job->set('bot_id', 0);
			$job->set('start', 0);
			$job->set('taken_time', 0);
			$job->set('downloaded_time', 0);
			$job->set('finished_time', 0);
			$job->set('verified_time', 0);
			$job->save();
			
			$this->set('job_id', 0);
			$this->set('status', 'idle');
			$this->set('last_seen', date("Y-m-d H:i:s"));
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
			$job->set('status', 'qa');
			$job->set('progress', 100);
			$job->set('finished_time', date('Y-m-d H:i:s'));
			$job->save();
			
			$this->set('status', 'waiting');
			$this->set('last_seen', date("Y-m-d H:i:s"));
			$this->save();
		}
		
		public function getQueue()
		{
			return new Queue($this->get('queue_id'));
		}

		public function getStats()
		{
			$sql = "
				SELECT status, count(status) as cnt
				FROM jobs
				WHERE bot_id = {$this->id}
				GROUP BY status
			";

			$data = array();
			$stats = db()->getArray($sql);
			if (!empty($stats))
			{
				//load up our stats
				foreach ($stats AS $row)
				{
					$data[$row['status']] = $row['cnt'];
					$data['total'] += $row['cnt'];
				}
				
				//calculate percentages
				foreach ($stats AS $row)
					$data[$row['status'] . '_pct'] = ($row['cnt'] / $data['total']) * 100;
			}
			
			//pull in our time based stats.
			$sql = "
				SELECT sum(verified_time - finished_time) as wait, sum(finished_time - taken_time) as runtime, sum(verified_time - taken_time) as total
				FROM jobs
				WHERE status = 'complete'
					AND bot_id = {$this->id}
			";

			$stats = db()->getArray($sql);
			
			$data['total_waittime'] = (int)$stats[0]['wait'];
			$data['total_runtime'] = (int)$stats[0]['runtime'];
			$data['total_time'] = (int)$stats[0]['total'];
			$data['avg_waittime'] = $stats[0]['wait'] / $data['total'];
			$data['avg_runtime'] = $stats[0]['runtime'] / $data['total'];
			$data['avg_time'] = $stats[0]['total'] / $data['total'];

			return $data;
		}
		
		public function delete()
		{
			//delete our jobs.
			$jobs = $this->getJobs()->getAll();
			foreach ($jobs AS $row)
			{
				$row['Job']->delete();
			}
			
			parent::delete();
		}
	}
?>