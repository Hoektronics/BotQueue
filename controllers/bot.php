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

	class BotController extends Controller
	{
		public function home()
		{
			$this->assertLoggedIn();
			
			$this->setTitle(User::$me->getName() . "'s Bots");
			$this->set('bots', User::$me->getBots()->getRange(0, 20));
		}

		public function register()
		{
			$this->assertLoggedIn();
			
			$this->setTitle('Register a new Bot');
			
			//load up our queues.
			$queues = User::$me->getQueues()->getAll();
			foreach ($queues AS $row)
			{
				$q = $row['Queue'];
				$data[$q->id] = $q->getName();
			}
			$this->set('queues', $data);

			//pull in our data.
			$bot = new Bot();
			$bot->set('user_id', User::$me->id);
			$bot->set('queue_id', $this->args('queue_id'));
			$bot->set('name', $this->args('name'));
			$bot->set('manufacturer', $this->args('manufacturer'));
			$bot->set('model', $this->args('model'));
			$bot->set('electronics', $this->args('electronics'));
			$bot->set('firmware', $this->args('firmware'));
			$bot->set('extruder', $this->args('extruder'));
			$bot->set('status', 'idle');
			$bot->set('last_seen', date('Y-m-d H:i:s'));
			$this->set('bot', $bot);

			//was it a form submit?
			if ($this->args('submit'))
			{
				//did we get a name?
				if (!$this->args('name'))
				{
					$errors['name'] = "You need to provide a name.";
					$errorfields['name'] = "error";
				}

				//did we get a name?
				if (!$this->args('queue_id'))
				{
					$errors['queue_id'] = "Your bot must have a designated queue.";
					$errorfields['queue_id'] = "error";
				}

				//okay, we good?
				if (empty($errors))
				{
					//woot!
					$bot->save();
					
					//todo: send a confirmation email.
					Activity::log("registered a new bot named " . $bot->getLink());

					$this->forwardToUrl($bot->getUrl());
				}
				else
				{
					$this->set('errors', $errors);
					$this->set('errorfields', $errorfields);
					$this->setArg('name');
					$this->setArg('model');
				}
			}
		}
		
		public function view()
		{
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));

			//did we really get someone?
			if (!$bot->isHydrated())
				$this->set('megaerror', "Could not find that bot.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('bot', $bot);
				$this->set('queue', $bot->getQueue());
				$this->set('job', $bot->getCurrentJob());
				$this->set('jobs', $bot->getJobs()->getRange(0, 50));
			}
		}
			
		public function draw_bots()
		{
			$this->setArg('bots');
		}
	}
?>
