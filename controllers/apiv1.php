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
		
		//not sure what this stuff does.  this was in the oauth login page docs.
		public function request_token()
		{
			//this is where we validate the request
			$provider = new MyOAuthProvider();
			$provider->setRequestTokenQuery();
			$provider->checkRequest();
	
			if (!$provider->hasError())
			{
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
	
				echo "oauth_token={$token_key}&oauth_token_secret={$token_secret}";
			}
			exit;
		}

		public function access_token()
		{
			$provider = new MyOAuthProvider();
			$provider->checkRequest();

			if (!$provider->hasError())
			{
				$token = OAuthToken::findByKey($provider->oauth->token);
				$token->changeToAccessToken();
			
				echo "oauth_token=" . $token->get('token') . "&oauth_token_secret=" . $token->get('token_secret');
			}
			exit;
		}
		
		//todo: make this the entry point for the rest of the API calls.
		public function endpoint()
		{
			$provider = new MyOAuthProvider();
			$provider->checkRequest();
			try
			{
				if (!$provider->hasError())
				{
					switch ($this->args('api_call'))
					{
						case 'listjobs':
							$data = $this->api_listjobs();
							break;
					}
				}
			}
			catch(Exception $e)
			{
				echo $e;
			}
			
			echo JSON::encode($data);
			
			exit;
		}
		
		public function api_listjobs()
		{
			try
			{
				if ($this->args('queue_id'))
					$queue = new Queue($this->args('queue_id'));
#				else
#					$queue = User::$me->getDefaultQueue();
				
				$data = array();
				$jobs = $queue->getJobs()->getRange(0, 30);
				if (!empty($jobs))
					foreach ($jobs AS $row)
						$data[] = $row['Job']->getAPIData();
						
			}
			catch(Exception $e)
			{
				echo $e;
			}			

			return $data;
		}
	}
?>