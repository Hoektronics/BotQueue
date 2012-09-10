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

	class APIV1Controller extends Controller
	{
		public static $api_version = "1.0";
		
		public function endpoint()
		{
			$provider = new MyOAuthProvider();

			//we need to disable a check if it is our first call to requesttoken.
			$c = strtolower($this->args('api_call'));
			if ($c == 'requesttoken')
			{
				$provider->oauth->isRequestTokenEndpoint(true);
				$this->set('provider', $provider);
			}
			//accesstoken also needs the class.
			elseif ($c == 'accesstoken')
				$this->set('provider', $provider);

			try
			{
				$provider->oauth->checkOAuthRequest();

				$calls = array(
					'requesttoken',       //ok
					'accesstoken',        //ok
					'listqueues',         //ok
					'queueinfo',          //ok
					'createqueue',        //ok
					'listjobs',           //ok
					'jobinfo',            //ok
					'grabjob',            //ok
					'findnewjob',         //ok
					'dropjob',            //ok
					'canceljob',          //ok
					'completejob',        //ok
					'createjob',          //ok
					'updatejobprogress',  //ok
					'listbots',           //ok
					'botinfo',            //ok
					'registerbot',        //ok
					'updatebot',          //ok
					'updatebotstatus',    //ok
				);
				if (in_array($c, $calls))
				{
					$fname = "api_{$c}";
					$data = $this->$fname();
				}
				else
					throw new Exception("Specified api_call '{$c}' does not exist.");
					
				$result = array('status' => 'success', 'data' => $data);
			}
			catch(Exception $e)
			{
				$result = array('status' => 'error', 'error' => $e->getMessage());
			}

			//add in our version.
			$result['version'] = self::$api_version;
			
			echo JSON::encode($result);
				
			exit;
		}
		
		public function api_requesttoken()
		{
			//pull in our interface class.
			$provider = $this->get('provider');
			
			//this is where we generate our token.
			$token_key = MyOAuthProvider::generateToken();
			$token_secret = MyOAuthProvider::generateToken();

			//okay, save it to the db.
			$t = new OAuthToken();
			$t->set('type', 1);
			$t->set('consumer_id', $provider->consumer->id);
			$t->set('token', $token_key);
			$t->set('token_secret', $token_secret);
			$t->save();
	
			$data['oauth_token'] = $token_key;
			$data['oauth_token_secret'] = $token_secret;

			return $data;
		}

		public function api_accesstoken()
		{
			//pull in our interface class.
			$provider = $this->get('provider');
			
			$token = OAuthToken::findByKey($provider->oauth->token);
			$token->changeToAccessToken();
			
			$data['oauth_token'] = $token->get('token');
			$data['oauth_token_secret'] = $token->get('token_secret');

			return $data;
		}

		public function api_listqueues()
		{
			$data = array();
			$qs = User::$me->getQueues()->getRange(0, 100);
			if (!empty($qs))
				foreach ($qs AS $row)
					$data[] = $row['Queue']->getAPIData();

			return $data;
		}

		public function api_queueinfo()
		{
			if ($this->args('queue_id'))
				$queue = new Queue($this->args('queue_id'));
			else
				$queue = User::$me->getDefaultQueue();
				
			if (!$queue->isHydrated())
				throw new Exception("Could not find a queue.");
			
			$data = $queue->getAPIData();

			return $data;
		}
		
		public function api_createqueue()
		{
			if (!$this->args('name'))
				throw new Exception('Queue name is a required parameter.');

			$q = new Queue();
			$q->set('name', $this->args('name'));
			$q->set('user_id', User::$me->id);
			$q->save();

			Activity::log("created a queue named " . $q->getLink() . " via the API.");
			
			return $q->getAPIData();
		}

		public function api_createjob()
		{
			if ($this->args('queue_id'))
				$queue = new Queue($this->args('queue_id'));
			else
				$queue = User::$me->getDefaultQueue();
				
			if (!$queue->isHydrated())
				throw new Exception("Could not find a queue.");
			if (!$queue->isMine())
				throw new Exception("This is not your queue.");
				
			//get our quantity and make sure its at least 1.
			if ($this->args('quantity'))
				$quantity = (int)$this->args('quantity');
			$quantity = max(1, $quantity);
			$quantity = min(100, $quantity);
			
			// there are 3 ways to create a job:
			// #1 - existing job id
			if ($this->args('job_id'))
			{
				$oldjob = new Job($this->args('job_id'));

				if (!$oldjob->isHydrated())
					throw new Exception("Job does not exist.");
				if (!$job->getQueue()->isMine())
					throw new Exception("This job is not in your queue.");

				$file = $oldjob->getFile();
				if (!$file->isHydrated())
					throw new Exception("That job does not exist anymore.");
				
				$jobs = $queue->addGCodeFile($file, $quantity);
			}
			// #2 - send a file url and we'll grab it.
			else if ($this->args('job_url'))
			{
				throw new Exception("Job add via URL is not implemented yet.");
			}
			// #3 - post a file via http multipart form
			else if (!empty($_FILES['job_data']) && is_uploaded_file($_FILES['job_data']['tmp_name']))
			{
				throw new Exception("Job add via HTTP POST is not implemented yet.");
			}
			else
			{
				throw new Exception("Unknown job creation method.");
			}

			Activity::log("created " . count($jobs) . " new " . Utility::pluralizeWord('job', count($jobs)) . " via the API.");
			
			$data = array();
			if (!empty($jobs))
				foreach($jobs AS $job)
					$data[] = $job->getAPIData();
					
			return $data;
		}

		public function api_listjobs()
		{
			if ($this->args('queue_id'))
				$queue = new Queue($this->args('queue_id'));
			else
				$queue = User::$me->getDefaultQueue();
				
			if (!$queue->isHydrated())
				throw new Exception("Could not find a queue.");
			
			$data = array();
			
			if ($this->args('status'))
				$col = $queue->getJobs($this->args('status'));
			else
				$col = $queue->getJobs();
			
			$jobs = $col->getRange(0, 50);
			if (!empty($jobs))
				foreach ($jobs AS $row)
					$data[] = $row['Job']->getAPIData();

			return $data;
		}
		
		public function api_grabjob()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");

			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");
			
			$bot = new Bot($this->args('bot_id'));
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
				
			if (!$bot->isMine())
				throw new Exception("This is not your bot.");
			
			if (!$bot->canGrab($job))
				throw new Exception("You cannot grab this job.");
				
			$bot->grabJob($job);
			
			$data = array();
			$data['job'] = $job->getAPIData();
			$data['bot'] = $bot->getAPIData();

			Activity::log($bot->getLink() . " bot grabbed the " . $job->getLink() . " job via the API.");
			
			return $data;
		}
		
		public function api_dropjob()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");
			
			$bot = $job->getBot();
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
				
			if (!$bot->isMine())
				throw new Exception("This is not your bot.");
			
			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");
				
			if (!$bot->canDrop($job))
				throw new Exception("You cannot drop this job.");
				
			$bot->dropJob($job);

			Activity::log($bot->getLink() . " bot dropped the " . $job->getLink() . " job via the API.");
			
			$data['job'] = $job->getAPIData();
			$data['bot'] = $bot->getAPIData();
			
			return $data;
		}
		
		public function api_canceljob()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");

			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");

			if (!$job->canDelete($job))
				throw new Exception("You cannot delete this job.");

			Activity::log("cancelled the <strong>" . $job->getName() . "</strong> job via the API.");
				
			$job->cancelJob();

			return $job->getAPIData();
		}

		public function api_completejob()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");
			
			$bot = $job->getBot();
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
				
			if (!$bot->isMine())
				throw new Exception("This is not your bot.");
			
			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");
				
			if (!$bot->canComplete($job))
				throw new Exception("You cannot complete this job.");
				
			$bot->completeJob($job);

			Activity::log($bot->getLink() . " bot completed the " . $job->getLink() . " job via the API.");
			
			$data['job'] = $job->getAPIData();
			$data['bot'] = $bot->getAPIData();
			
			return $data;
		}
		
		public function api_updatejobprogress()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");
			
			$bot = $job->getBot();
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
				
			if (!$bot->isMine())
				throw new Exception("This is not your bot.");
			
			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");
				
			$job->set('progress', (float)$this->args('progress'));
			$job->save();
			
			$bot->set('last_seen', date("Y-m-d H:i:s"));
			$bot->save();
			
			return $job->getAPIData();
		}

		public function api_jobinfo()
		{
			$job = new Job($this->args('job_id'));
			if (!$job->isHydrated())
				throw new Exception("Job does not exist.");
			
			if (!$job->getQueue()->isMine())
				throw new Exception("This job is not in your queue.");
				
			return $job->getAPIData();
		}
		
		public function api_listbots()
		{
			$data = array();
			$bots = User::$me->getBots()->getRange(0, 100);
			if (!empty($bots))
				foreach ($bots AS $row)
					$data[] = $row['Bot']->getAPIData();

			return $data;
		}
		
		public function api_botinfo()
		{
			$bot = new Bot($this->args('bot_id'));
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
			
			if (!$bot->isMine())
				throw new Exception("This bot is not yours.");
				
			return $bot->getAPIData();
		}
		
		public function api_findnewjob()
		{
			$bot = new Bot($this->args('bot_id'));
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
			
			if (!$bot->isMine())
				throw new Exception("This bot is not yours.");

			//load up our data.
			$data = array();	
			$jobs = $bot->getQueue()->getJobs('available')->getRange(0, 1);
			if (!empty($jobs))
				$data = $jobs[0]['Job']->getAPIData();
			
			//record our bot as having checked in.
			$bot->set('last_seen', date("Y-m-d H:i:s"));
			$bot->save();
			
			return $data;			
		}

		public function api_registerbot()
		{
			if (!$this->args('name'))
				throw new Exception('Bot name is a required parameter.');
			if (!$this->args('identifier'))
				throw new Exception('Bot identifier is a required parameter.');
			#if (!$this->args('manufacturer'))
			#	throw new Exception('Bot manufacturer is a required parameter.');
			#if (!$this->args('model'))
			#	throw new Exception('Bot model is a required parameter.');
				
			$bot = new Bot();
			$bot->set('user_id', User::$me->id);
			$bot->set('name', $this->args('name'));
			$bot->set('identifier', $this->args('identifier'));
			$bot->set('manufacturer', $this->args('manufacturer'));
			$bot->set('model', $this->args('model'));
			$bot->set('electronics', $this->args('electronics'));
			$bot->set('firmware', $this->args('firmware'));
			$bot->set('extruder', $this->args('extruder'));
			$bot->set('status', 'idle');
			$bot->save();

			Activity::log("registered the new bot " . $bot->getLink() . " via the API.");
			
			return $bot->getAPIData();
		}
		
		public function api_updatebot()
		{
			$bot = new Bot($this->args('bot_id'));
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
			
			if (!$bot->isMine())
				throw new Exception("This bot is not yours.");

			if (!$this->args('name'))
				throw new Exception('Bot name is a required parameter.');
			if (!$this->args('identifier'))
				throw new Exception('Bot identifier is a required parameter.');
			//if (!$this->args('manufacturer'))
			//	throw new Exception('Bot manufacturer is a required parameter.');
			//if (!$this->args('model'))
			//	throw new Exception('Bot model is a required parameter.');
			
			if ($this->args('name'))	
  			$bot->set('name', $this->args('name'));
  		if ($this->args('name'))
  			$bot->set('identifier', $this->args('identifier'));
  		if ($this->args('manufacturer'))
  			$bot->set('manufacturer', $this->args('manufacturer'));
  		if ($this->args('model'))
  			$bot->set('model', $this->args('model'));
  		if ($this->args('electronics'))
  			$bot->set('electronics', $this->args('electronics'));
      if ($this->args('firmware'))
  			$bot->set('firmware', $this->args('firmware'));
  		if ($this->args('extruder'))
  			$bot->set('extruder', $this->args('extruder'));
  		if ($this->args('status'))
  		  $bot->set('status', $this->args('status'));
  		if ($this->args('error_text'))
  		  $bot->set('error_text', $this->args('error_text'));
			$bot->save();

			Activity::log("updated the bot " . $bot->getLink() . " via the API.");
			
			return $bot->getAPIData();
		}
	}
?>