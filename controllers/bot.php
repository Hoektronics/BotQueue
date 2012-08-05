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
		public function register()
		{
			$this->assertLoggedIn();
			
			$this->setTitle('Register a Bot');
			
			if ($this->args('submit'))
			{
				//did we get a name?
				if (!$this->args('name'))
					$errors['name'] = "You need to provide a name.";
					
				//okay, we good?
				if (empty($errors))
				{
					//woot!
					$bot = new Queue();
					$bot->set('name', $this->args('name'));
					$bot->set('model', $this->args('model'));
					$bot->set('status', 'offline');
					$bot->set('last_seen', date('Y-m-d H:i:s'));
					$bot->save();
					
					//todo: send a confirmation email.
					Activity::log("registered a new bot named " . $bot->getLink(), $bot);

					$this->forwardToUrl($bot->getUrl());
				}
				else
				{
					$this->set('errors', $errors);
					$this->setArg('name');
					$this->setArg('model');
				}
			}
		}
		
		public function view()
		{
			//how do we find them?
			if ($this->args('id'))
				$q = new Queue($this->args('id'));

			//did we really get someone?
			if (!$q->isHydrated())
				$this->set('megaerror', "Could not find that queue.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('queue', $q);
			}
		}
			
		public function draw_bots()
		{
			$this->setArg('bots');
		}
	}
?>
