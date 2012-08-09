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

	class JobController extends Controller
	{
		public function home()
		{
			$this->assertLoggedIn();
			
			$this->setTitle(User::$me->getName() . "'s Jobs");
			$this->set('available', User::$me->getJobs('available')->getRange(0, 10));
			$this->set('taken', User::$me->getJobs('taken')->getRange(0, 10));
			$this->set('complete', User::$me->getJobs('complete')->getRange(0, 10));
			$this->set('failure', User::$me->getJobs('failure')->getRange(0, 10));
		}

		public function view()
		{
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));

			//did we really get someone?
			if (!$job->isHydrated())
				$this->set('megaerror', "Could not find that job.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('job', $job);
			}
		}
			
		public function draw_jobs()
		{
			$this->setArg('jobs');
		}

		public function draw_jobs_small()
		{
			$this->setArg('jobs');
		}
	}
?>
