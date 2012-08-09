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
			return $this->get('name');	
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
				'complete' => 'label-success',
				'failure' => 'label-important'
			);
			
			return $s2c[$status];
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
		
		public function getAPIData()
		{
			$d = array();
			$d['id'] = $this->id;
			$d['name'] = $this->getName();
			$d['queue'] = $this->get('queue_id');
			$d['file'] = $this->getFile()->getRealUrl();
			$d['status'] = $this->get('status');
			$d['start'] = $this->get('start');
			$d['end'] = $this->get('end');
			
			return $d;
		}
		
		public function cancelJob()
		{
			$bot = $job->getBot();
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
		
		public function getElapsedText()
		{
			if ($this->get('status') == 'available')
			{
				$start = strtotime($this->get('created'));
				$end = time();
			}
			elseif ($this->get('status') == 'taken')
			{
				$start = strtotime($this->get('start'));
				$end = time();
			}
			else
			{
				$start = strtotime($this->get('start'));
				$end = strtotime($this->get('end'));
			}

			return Utility::getElapsed($end - $start);
		}
	}
?>