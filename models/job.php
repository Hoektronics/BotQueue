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

	class Job extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "jobs");
		}
		
		public function getName()
		{
			return basename($this->get('name'));	
		}

		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getUrl()
		{
			return "/job:" . $this->id;
		}
		
		public function getStatusHTML()
		{
			return "<span class=\"label " . self::getStatusHTMLClass($this->get('status')) . "\">" . $this->get('status') . "</span>";
		}
		
		public static function getStatusHTMLClass($status)
		{
			$s2c = array(
				'taken' => 'label-info',
				'qa' => 'label-warning',
				'slicing' => 'label-slicing',
				'complete' => 'label-success',
				'failure' => 'label-important'
			);
			
			return $s2c[$status];
		}

    public function getSourceFile()
    {
			return new S3File($this->get('source_file_id'));
    }
    
    public function getSliceJob()
    {
      return new SliceJob($this->get('slice_job_id'));
    }
		
		public function getFile()
		{
			return new S3File($this->get('file_id'));
		}		

		public function getQueue()
		{
			return new Queue($this->get('queue_id'));
		}		

		public function getBot()
		{
			return new Bot($this->get('bot_id'));
		}
		
		public function getComments()
		{
		  return Comment::byContentAndType($this->id, 'job');
		}
		
		public function getAPIData()
		{
			$d = array();
			$d['id'] = $this->id;
			$d['bot_id'] = $this->get('bot_id');
			$d['name'] = $this->getName();
			$d['queue'] = $this->get('queue_id');
			$d['source_file'] = $this->getSourceFile()->getAPIData();
			$d['file'] = $this->getFile()->getAPIData();
			$d['slicejob'] = $this->getSliceJob()->getAPIData();
			$d['status'] = $this->get('status');
			$d['created_time'] = $this->get('created_time');
			$d['taken_time'] = $this->get('taken_time');
			$d['downloaded_time'] = $this->get('downloaded_time');
			$d['finished_time'] = $this->get('finished_time');
			$d['verified_time'] = $this->get('verified_time');
			$d['progress'] = $this->get('progress');
			
			return $d;
		}

    public function canView()
    {
      if ($this->get('user_id') == User::$me->id)
        return true;
      return false;
    }

    public function canEdit()
    {
      return $this->canView();
    }
		
    public function canComment()
    {
      return $this->canView();
    }
		
		public function cancelJob()
		{
			$bot = $this->getBot();
			if ($bot->isHydrated())
			{
				$bot->set('job_id', 0);
				$bot->set('status', 'idle');
				$bot->save();
			}
			
			$this->set('status', 'cancelled');
			$this->set('bot_id', 0);
			$this->set('start', 0);
			$this->save();
		}
		
		public function pushToTop()
		{
		  //find our our current max
			$sql = "SELECT min(user_sort)-1 FROM jobs WHERE queue_id = " . (int)$this->get('queue_id');
			$min = (int)db()->getValue($sql);

      $this->set('user_sort', $min);
      $this->save();
		}
		
		public function getElapsedTime()
		{
			if ($this->get('status') == 'available')
			{
				$start = strtotime($this->get('created_time'));
				$end = time();
			}
			elseif ($this->get('status') == 'taken' || $this->get('status') == 'downloading' || $this->get('status') == 'slicing')
			{
				$start = strtotime($this->get('taken_time'));
				$end = time();
			}
			else
			{
				$start = strtotime($this->get('taken_time'));
				$end = strtotime($this->get('finished_time'));
			}
			
			return $end - $start;			
		}
		
		public function getElapsedText()
		{
			return Utility::getElapsed($this->getElapsedTime());
		}

		public function getEstimatedTime()
		{
			//okay, now estimate it for us.
			$elapsed = $this->getElapsedTime();
			if ($this->get('progress') > 0)
			{
				$total = (100 / $this->get('progress')) * $elapsed;
				return $total - $elapsed;
			}
			
			return 0;
		}

		public function getEstimatedText()
		{
			return Utility::getElapsed($this->getEstimatedTime());
		}
		
		public function logError($error)
		{
	    $log = new ErrorLog();
	    $log->set('user_id', User::$me->id);
	    $log->set('job_id', $this->id);
	    $log->set('queue_id', $this->get('queue_id'));
	    $log->set('bot_id', $this->get('bot_id'));
	    $log->set('reason', $error);
	    $log->set('error_date', date("Y-m-d H:i:s"));
	    $log->save();
	    
	    return $log;		  
		}

		public function getErrorLog()
		{
		  $sql = "
		    SELECT id
		    FROM error_log
		    WHERE job_id = '".db()->escape($this->id)."'
		    ORDER BY error_date DESC
		  ";
		  
		  return new Collection($sql, array('ErrorLog' => 'id'));
		}
		
		public static function getJobsRequiringAction()
		{
		  $sql = "
		    SELECT id, queue_id, bot_id
		    FROM jobs
		    WHERE status = 'qa'
		    ORDER BY finished_time ASC
		  ";
		  
		  return new Collection($sql, array('Job' => 'id', 'Queue' => 'queue_id', 'Bot' => 'bot_id'));
		}
		
		public function delete()
		{
		  //clean up our bot.
      $bot = $this->getBot();
			if ($bot->isHydrated())
			{
				$bot->set('job_id', 0);
				$bot->set('status', 'idle');
				$bot->save();
			}
			
      $sql = "DELETE FROM error_log WHERE job_id = {$this->id}";
      db()->execute($sql);		  

      $sql = "DELETE FROM slice_jobs WHERE job_id = {$this->id}";
      db()->execute($sql);		  

		  
		  parent::delete();
		}
		
	}
?>