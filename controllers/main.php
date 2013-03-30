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

	class MainController extends Controller
	{
		public function home()
		{
		}
		
		public function dashboard()
		{
		  if (User::isLoggedIn())
			{
				//$queues = User::$me->getQueues();
				//$this->set('queues', $queues->getRange(0, 10));
				//$this->set('queue_count', $queues->count());

				$bots = User::$me->getBots();
				$this->set('bots', $bots->getRange(0, 10));
				$this->set('bot_count', $bots->count());

				$on_deck = User::$me->getJobs('available', 'user_sort', 'ASC');
				$this->set('on_deck', $on_deck->getRange(0, 5));
				$this->set('on_deck_count', $on_deck->count());

				$finished = User::$me->getJobs('complete', 'verified_time', 'DESC');
				$this->set('finished', $finished->getRange(0, 5));
				$this->set('finished_count', $finished->count());
				
				//$activities = Activity::getStream();
      	//$this->set('activities', $activities->getRange(0, 10));
				//$this->set('activity_count', $activities->count());
				
				//$this->set('errors', User::$me->getErrorLog()->getRange(0, 50));
				
				//$this->set('action_jobs', Job::getJobsRequiringAction()->getRange(0, 50));
				//$this->set('action_slicejobs', SliceJob::getJobsRequiringAction()->getRange(0, 50));
			}
			else
			  die('argh');
		}
		
		public function activity()
		{
			$this->setTitle('Activity Log');
			
			$collection = Activity::getStream();
      $per_page = 20;
      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
      $this->set('per_page', $per_page);
      $this->set('total', $collection->count());
      $this->set('page', $page);
      $this->set('activities', $collection->getPage($page, $per_page));
		}
		
		public function draw_activities()
		{
			$this->setArg('activities');
		}
		
		public function draw_error_log()
		{
		  $this->setArg('errors');
		  $this->setArg('hide');
		}
		
		public function sidebar()
		{
		}
		
		public function viewmode()
		{
			$mode = $this->args('view_mode');
			setcookie('viewmode', $mode, time()+60*60*24*30, '/');			
			$this->forwardToUrl('/');
		}
		
		public function shortcode()
		{
			$code = ShortCode::byCode($this->args('code'));
			
			die($code->get('url'));
		}
		
		public function tos()
		{
		  $this->setTitle("Terms of Service");
		}
		
		public function privacy()
		{
		  $this->setTitle("Privacy Policy");
		}
		
		public function stats()
		{
		  $this->setTitle("Overall BotQueue.com Stats");
		  
		  //active bots
		  $sql = "SELECT count(id) AS total FROM bots WHERE last_seen > NOW() - 300";
		  $this->set('total_active_bots', db()->getValue($sql));

		  //total prints
		  $sql = "SELECT count(id) AS total FROM jobs WHERE status = 'available'";
		  $this->set('total_pending_jobs', db()->getValue($sql));
		  		  
		  //total prints
		  $sql = "SELECT count(id) AS total FROM jobs WHERE status = 'complete'";
		  $this->set('total_completed_jobs', db()->getValue($sql));
		  
		  //total printing hours
		  $sql = "SELECT CEIL(SUM(unix_timestamp(finished_time) - unix_timestamp(taken_time)) / 3600) AS total FROM jobs WHERE status = 'complete'";
		  $this->set('total_printing_time', db()->getValue($sql));
		  
		  if (User::isLoggedIn())
		  {
  		  //active bots
  		  $sql = "SELECT count(id) AS total FROM bots WHERE last_seen > NOW() - 300 AND user_id = " . (int)User::$me->id;
  		  $this->set('my_total_active_bots', db()->getValue($sql));

  		  //total prints
  		  $sql = "SELECT count(id) AS total FROM jobs WHERE status = 'available' AND user_id = " . (int)User::$me->id;
  		  $this->set('my_total_pending_jobs', db()->getValue($sql));

  		  //total prints
  		  $sql = "SELECT count(id) AS total FROM jobs WHERE status = 'complete' AND user_id = " . (int)User::$me->id;
  		  $this->set('my_total_completed_jobs', db()->getValue($sql));

  		  //total printing hours
  		  $sql = "SELECT CEIL(SUM(unix_timestamp(finished_time) - unix_timestamp(taken_time)) / 3600) AS total FROM jobs WHERE status = 'complete' AND user_id = " . (int)User::$me->id;
  		  $this->set('my_total_printing_time', db()->getValue($sql));		    
		    
		  }
		}
	}
?>