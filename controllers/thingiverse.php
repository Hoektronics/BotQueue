<?php

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

class ThingiverseController extends Controller
{
	function main()
	{
		$this->assertLoggedIn();

		if($this->args('payload')) {
			$_SESSION['thing_url'] = unserialize(base64_decode($this->args('payload')));
		}

		if (User::$me->get('thingiverse_token')) {
			// We were uploading a file before we were interrupted
			if(isset($_SESSION['thing_url']))
				$this->forwardToURL('/upload/url');
			$this->setTitle("Thingiverse + BotQueue = :D");

			$api = new ThingiverseAPI(THINGIVERSE_API_CLIENT_ID, THINGIVERSE_API_CLIENT_SECRET, User::$me->getThingiverseToken());

			$this->set('my_info', $api->make_call('/users/me'));
		} else {
			$this->setTitle("Link Thingiverse to BotQueue");
		}
	}

	function thingiverse_callback()
	{
		$this->assertLoggedIn();

		if ($this->args('code')) {
			$api = new ThingiverseAPI(THINGIVERSE_API_CLIENT_ID, THINGIVERSE_API_CLIENT_SECRET);
			$token = $api->exchange_token($this->args('code'));

			if ($token) {
				//save it!
				User::$me->set('thingiverse_token', $token);
				User::$me->save();

				//send us to our thingiverse page.
				$this->forwardToUrl("/thingiverse");
			} else {
				die("Failed to exchange token.");
			}
		}
	}
}
