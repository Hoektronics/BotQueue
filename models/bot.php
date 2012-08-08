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
		
		public function getUrl()
		{
			return "/bot:" . $this->id;
		}
		
		public function getJob()
		{
			return new Job($this->get('job_id'));
		}
		
		public function isMine()
		{
			return (User::$me->id == $this->get('user_id'));
		}	
		
		public function canGrab($job)
		{
			//todo: fix me once we have the bot_to_queues table
			if ($bot->get('user_id') != $job->getQueue()->get('user_id'))
				return false;
			
			if ($job->get('status') != 'available')
				return false;

			if ($this->get('job_id'))
				return false;

			return true;
		}
		
		public function grabJob($job)
		{
			$job->set('status', 'taken');
			$job->set('bot_id', $this->id);
			$job->save();
			
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
			$job->save();
			
			$this->set('job_id', 0);
			$this->set('status', 'idle');
			$this->save();
		}

		public function canComplete($job)
		{
			if ($job->get('bot_id') == $this->id && $job->get('status') == 'working')
				return true;
			else
				return false;
		}
		
		public function completeJob($job)
		{
			$job->set('status', 'complete');
			$job->set('bot_id', 0);
			$job->save();
			
			$this->set('job_id', 0);
			$this->set('status', 'finished');
			$this->save();
		}
	}
?>
