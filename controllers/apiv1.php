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
		public $api_version = "1.0";
		
		public function home()
		{
			$this->set('apps', User::$me->getMyApps()->getAll());
			$this->set('authorized', User::$me->getAuthorizedApps()->getAll());
		}
		
		public function register_app()
		{
			$this->assertLoggedIn();
			
			try
			{
				if ($this->args('submit'))
				{
					if (!$this->args('name'))
						throw new Exception("You must enter a name.");
					
					$app = new OAuthConsumer();
					$app->set('name', $this->args('name'));
					$app->set('user_id', User::$me->id);
					$app->set('consumer_key', MyOAuthProvider::generateToken());
					$app->set('consumer_secret', MyOAuthProvider::generateToken());
					$app->set('active', 1);
					$app->save();
					
					$this->forwardToUrl($app->getUrl());
				}				
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}
		}
		
		public function edit_app()
		{
			$this->assertLoggedIn();
			
			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to edit this app.");

				$this->set('app', $app);

				if ($this->args('submit'))
				{
					if (!$this->args('name'))
					{
						$errors = array();
						$errors['name'] = "You must enter a name.";
						$this->set('errors', $errors);
					}
					else
					{
						$app->set('name', $this->args('name'));
						$app->save();
					
						$this->forwardToUrl($app->getUrl());
					}
				}				
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}
		}
		
		public function delete_app()
		{
			$this->assertLoggedIn();

			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to delete this app.");

				$this->set('app', $app);

				if ($this->args('submit'))
				{
					$app->delete();
					
					$this->forwardToUrl("/api/v1");
				}				
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}			
		}
		
		public function view_app()
		{
			$this->assertLoggedIn();

			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to view this app.");

				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}						
		}
		
		//todo: this needs to be made.
		public function authorize_app()
		{
			$this->assertLoggedIn();

			try
			{
				$token = OAuthToken::findByKey($this->args('oauth_token'));
				if (!$token->isHydrated())
					throw new Exception("That token does not exist.");
				if (!$token->isRequest())
					throw new Exception("This app has already been authorized.");

				$app = $token->getConsumer();
				if (!$app->isHydrated())
					throw new Exception("That application does not exist.");
				if (!$app->isActive())
					throw new Exception("That application is not active.");

				//okay, save it!
				$token->set('user_id', User::$me->id);
				$token->set('verifier', mt_rand(0, 99999));
				$token->save();
				
				$this->set('token', $token);
				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}	
		}
		
		//deletes an access token from an app.
		public function revoke_app()
		{
			$this->assertLoggedIn();

			try
			{
				$token = OAuthToken::findByKey($this->args('token'));
				if (!$token->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $token->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to delete this app.");

				$this->set('token', $token);
				$this->set('app', $token->getConsumer());

				if ($this->args('submit'))
				{
					$token->delete();
					$this->forwardToUrl("/api/v1");
				}				
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}				
		}
		
		//TODO: split everything below into its own controller?  app registration vs API
		
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
					'failjob',            //ok
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

			//eventually add more data outputs.  json is a good default.
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
				
			$bot->set('name', $this->args('name'));
			$bot->set('identifier', $this->args('identifier'));
			$bot->set('manufacturer', $this->args('manufacturer'));
			$bot->set('model', $this->args('model'));
			$bot->set('electronics', $this->args('electronics'));
			$bot->set('firmware', $this->args('firmware'));
			$bot->set('extruder', $this->args('extruder'));
			$bot->save();
			
			return $bot->getAPIData();
		}
		
		public function api_updatebotstatus()
		{
			$bot = new Bot($this->args('bot_id'));
			if (!$bot->isHydrated())
				throw new Exception("Bot does not exist.");
			
			if (!$bot->isMine())
				throw new Exception("This bot is not yours.");

			$bot->set('status', $this->args('status'));
			$bot->save();
			
			return $bot->getAPIData();
		}
	}
?>