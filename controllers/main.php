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
			if (User::isLoggedIn())
			{
				$queues = User::$me->getQueues();
				$this->set('queues', $queues->getRange(0, 10));
				$this->set('queue_count', $queues->count());

				$bots = User::$me->getBots();
				$this->set('bots', $bots->getRange(0, 10));
				$this->set('bot_count', $bots->count());

				$jobs = User::$me->getJobs(null, 'user_sort', 'DESC');
				$this->set('jobs', $jobs->getRange(0, 10));
				$this->set('job_count', $jobs->count());
				
				$activities = Activity::getStream();
      	$this->set('activities', $activities->getRange(0, 10));
				$this->set('activity_count', $activities->count());
			}
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
	}
?>