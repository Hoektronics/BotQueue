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
				'slicing' => 'info',
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
				$statusSql = " AND status = '" . db()->escape($status) . "'";
				
			$sql = "
				SELECT id
				FROM jobs
				WHERE bot_id = " . db()->escape($this->id) ."
					{$statusSql}
				ORDER BY {$sortField} {$sortOrder}
			";
			return new Collection($sql, array('Job' => 'id'));
		}
		
		public function getErrorLog()
		{
		  $sql = "
		    SELECT id
		    FROM error_log
		    WHERE bot_id = '". db()->escape($this->id) ."'
		    ORDER BY error_date DESC
		  ";
		  
		  return new Collection($sql, array('ErrorLog' => 'id'));
		}
		
		public function isMine()
		{
			return (User::$me->id == $this->get('user_id'));
		}	
		
		public function canGrab($job)
		{
      //if we're already the owner, we can grab the job.
      //this is because sometimes the grab request times out and the bot doesn't know it hasn't grabbed the job.
			if ($job->get('status') == 'taken' && $job->get('bot_id') == $this->id)
			  return true;

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
		
		public function grabJob($job, $can_slice = true)
		{
			$job->set('status', 'taken');
			$job->set('bot_id', $this->id);
			$job->set('taken_time', date('Y-m-d H:i:s'));
			$job->save();

			usleep(1000 + mt_rand(100,500));
			$job = new Job($job->id);
			if ($job->get('bot_id') != $this->id)
				throw new Exception("Unable to lock job #{$job->id}");

      //do we need to slice this job?
      if (!$job->getFile()->isHydrated())
      {
        //pull in our config and make sure its legit.
        $config = $this->getSliceConfig();
        if (!$config->isHydrated())
        {
          $job->set('status', 'available');
          $job->set('bot_id', 0);
    			$job->set('taken_time', 0);
          $job->save();

  				throw new Exception("This bot does not have a slice engine + configuration set.");
        }
        
        //is there an existing slice job w/ this exact file and config?
        $sj = SliceJob::byConfigAndSource($config->id, $job->get('source_file_id'));
        if ($sj->isHydrated())
        {
          //update our job status.
          $job->set('slice_job_id', $sj->id);
          $job->set('slice_complete_time', $job->get('taken_time'));
          $job->set('file_id', $sj->get('output_id'));
          $job->save();
        }
        else
        {
          //nope, create our slice job for processing.
          $sj->set('user_id', User::$me->id);
          $sj->set('job_id', $job->id);
          $sj->set('input_id', $job->get('source_file_id'));
          $sj->set('slice_config_id', $config->id);
          $sj->set('slice_config_snapshot', $config->getSnapshot());
          $sj->set('add_date', date("Y-m-d H:i:s"));
          $sj->set('status', 'available');
          $sj->save();

          //update our job status.
          $job->set('status', 'slicing');
          $job->set('slice_job_id', $sj->id);
          $job->save();    
        }
      }
      
      //create our time log entry
      $log = new JobClockEntry();
      $log->set('bot_id', $this->id);
      $log->set('job_id', $job->id);
      $log->set('queue_id', $job->get('queue_id'));
      $log->set('start_date', date("Y-m-d H:i:s"));
      $log->set('status', 'working');
      $log->save();
			
			//okay, update our status.
			$this->set('job_id', $job->id);
			$this->set('status', 'working');
			$this->set('last_seen', date("Y-m-d H:i:s"));
			$this->save();
			
			return $job;
		}
		
		public function canDrop($job)
		{
			if ($job->get('bot_id') == $this->id && $this->get('job_id') == $job->id)
				return true;
			//if nobody has the job, we can safely drop it.  sometimes the web requests will time out and a bot will get stuck trying to drop a job.
      else if ($job->get('bot_id' == 0) && $job->get('bot_id') == 0)
        return true;
			else
				return false;
		}
		
		public function dropJob($job)
		{
		  //if its a sliced job, clear it for a potentially different bot.
		  if ($job->getSliceJob()->isHydrated())
		  {
		    $job->set('slice_job_id', 0);
		    $job->set('file_id', 0);
		  }
		  
		  //update our log status.
		  $log = $job->getLatestTimeLog();
		  $log->set('end_date', date("Y-m-d H:i:s"));
		  $log->set('status', 'dropped');
		  $log->save();
		  
		  //clear out our data for the next bot.
			$job->set('status', 'available');
			$job->set('bot_id', 0);
			$job->set('taken_time', 0);
			$job->set('downloaded_time', 0);
			$job->set('finished_time', 0);
			$job->set('verified_time', 0);
      $job->set('progress', 0);
			$job->set('temperature_data', '');
			$job->save();
			
			//update the bot to be idle.
			$this->set('job_id', 0);
			$this->set('status', 'idle');
			$this->set('temperature_data', '');
			$this->set('last_seen', date("Y-m-d H:i:s"));
			$this->save();
		}
		
		function pause()
		{
		  $this->set('status', 'paused');
		  $this->save();
		}
		
		function unpause()
		{
		  $this->set('status', 'working');
		  $this->save();
		}

		public function canComplete($job)
		{
			if ($job->get('bot_id') == $this->id && $job->get('status') == 'taken')
				return true;
			//sometimes the web requests will time out and a bot will get stuck trying to complete a job.
			else if ($job->get('bot_id') == $this->id && $job->get('status') == 'qa')
			  return true;
			else
				return false;
		}
		
		public function completeJob($job)
		{
		  $log = $job->getLatestTimeLog();
		  $log->set('end_date', date("Y-m-d H:i:s"));
		  $log->set('status', 'complete');
		  $log->save();
		  
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
				WHERE bot_id = ". db()->escape($this->id) ."
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
				SELECT sum(unix_timestamp(verified_time) - unix_timestamp(finished_time)) as wait, sum(unix_timestamp(finished_time) - unix_timestamp(taken_time)) as runtime, sum(unix_timestamp(verified_time) - unix_timestamp(taken_time)) as total
				FROM jobs
				WHERE status = 'complete'
					AND bot_id = ". db()->escape($this->id);

			$stats = db()->getArray($sql);
			
			$data['total_waittime'] = (int)$stats[0]['wait'];
			$data['total_runtime'] = (int)$stats[0]['runtime'];
			$data['total_time'] = (int)$stats[0]['total'];
			
			if ($data['total'])
			{
  			$data['avg_waittime'] = $stats[0]['wait'] / $data['total'];
  			$data['avg_runtime'] = $stats[0]['runtime'] / $data['total'];
  			$data['avg_time'] = $stats[0]['total'] / $data['total'];
			}
			else
			{
  			$data['avg_waittime'] = 0;
  			$data['avg_runtime'] = 0;
  			$data['avg_time'] = 0;
			}

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
		
		public function getSliceEngine()
		{
		  return new SliceEngine($this->get('slice_engine_id'));
		}
		
		public function getSliceConfig()
		{
		  return new SliceConfig($this->get('slice_config_id'));
		}
		
		public function getLastSeenHTML()
		{
		  $now = time();
		  $last = strtotime($this->get('last_seen'));
		  
		  $elapsed = $now - $last;
		  
		  if ($last < 0)
		   return "never";

		  $months = floor($elapsed / (60*60*24*30));
		  $elapsed = $elapsed - $months * 60 * 60 * 30;
		  
		  $days = floor($elapsed / (60*60*24));
		  $elapsed = $elapsed - $days * 60 * 60 * 24;
		  
		  $hours = floor($elapsed / (60*60));
		  $elapsed = $elapsed - $hours * 60 * 60;
		  
		  $minutes = floor($elapsed / (60));
      if ($minutes > 1)
		    $elapsed = $elapsed - $minutes * 60;
		  		  		  
		  if ($months)
		    return "{$months} months";
		  if ($days > 1)
		    return "{$days} days ago";
      if ($days)
        return "{$days} day ago";
		  if ($hours > 1)
		    return "{$hours} hours ago";
      if ($hours)
        return "{$hours}:{$minutes}:{$elapsed} ago";
		  if ($minutes > 1)
        return "{$minutes} mins ago";
		  return "{$elapsed}s ago";
		}
	}
?>