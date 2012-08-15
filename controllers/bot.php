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
			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
				
				$this->setTitle("View Bot - " . $bot->getName());
				
				//errors?
				$this->set('bot', $bot);
				$this->set('queue', $bot->getQueue());
				$this->set('job', $bot->getCurrentJob());
				$this->set('jobs', $bot->getJobs(null, 'user_sort', 'DESC')->getRange(0, 50));
				$this->set('stats', $bot->getStats());
				$this->set('owner', $bot->getUser());
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("View Bot - Error");
			}
		}

		public function set_status()
		{
			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
				if ($bot->get('status') == 'working' && $this->args('status') == 'offline')
					throw new Exception("You cannot take a working bot offline through the web interface.  Use the client app instead.");
				
				$bot->set('status', $this->args('status'));
				$bot->save();
				
				$this->forwardToUrl($bot->getUrl());
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("Change Bot Status - Error");
			}
		}
			
		public function draw_bots()
		{
			$this->setArg('bots');
		}
		
		public function edit()
		{
			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");

				$this->setTitle('Edit Bot - ' . $bot->getName());

				//load up our form.
				$form = $this->_createBotForm($bot);
				$form->action = $bot->getUrl() . "/edit";

				//handle our form
				if ($form->checkSubmitAndValidate($this->args()))
				{
					$bot->set('queue_id', $form->data('queue_id'));
					$bot->set('name', $form->data('name'));
					$bot->set('manufacturer', $form->data('manufacturer'));
					$bot->set('model', $form->data('model'));
					$bot->set('electronics', $form->data('electronics'));
					$bot->set('firmware', $form->data('firmware'));
					$bot->set('extruder', $form->data('extruder'));
					$bot->set('status', 'idle');
					$bot->save();
				
					$this->forwardToUrl($bot->getUrl());						
				}
				
				$this->set('form', $form);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("Bot Edit - Error");
			}			
		}
		
		private function _createBotForm($bot)
		{
			//load up our queues.
			$queues = User::$me->getQueues()->getAll();
			foreach ($queues AS $row)
			{
				$q = $row['Queue'];
				$qs[$q->id] = $q->getName();
			}

			$form = new Form();
			
			$form->add(new TextField(array(
				'name' => 'name',
				'label' => 'Bot Name',
				'help' => 'What should humans call your bot?',
				'required' => true,
				'value' => $bot->get('name')
			)));
			
			$form->add(new SelectField(array(
				'name' => 'queue_id',
				'label' => 'Queue',
				'help' => 'Which queue does this bot pull jobs from?',
				'required' => true,
				'value' => $bot->get('queue_id'),
				'options' => $qs
			)));

			$form->add(new TextField(array(
				'name' => 'manufacturer',
				'label' => 'Manufacturer',
				'help' => 'Which company (or person) built your bot?',
				'required' => true,
				'value' => $bot->get('manufacturer')
			)));

			$form->add(new TextField(array(
				'name' => 'model',
				'label' => 'Model',
				'help' => 'What is the model or name of your bot design?',
				'required' => true,
				'value' => $bot->get('model')
			)));

			$form->add(new TextField(array(
				'name' => 'electronics',
				'label' => 'Electronics',
				'help' => 'What electronics are you using to control your bot?',
				'required' => true,
				'value' => $bot->get('electronics')
			)));

			$form->add(new TextField(array(
				'name' => 'firmware',
				'label' => 'Firmware',
				'help' => 'What firmware are you running on your electronics?',
				'required' => true,
				'value' => $bot->get('firmware')
			)));
			
			return $form;
		}
	}
?>
