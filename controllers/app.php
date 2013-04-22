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

	class AppController extends Controller
	{
		public function home()
		{
		  $this->set('area', 'app');
		  
			if (User::isLoggedIn())
			{
				$this->set('apps', User::$me->getMyApps()->getAll());
				$this->set('authorized', User::$me->getAuthorizedApps()->getAll());				
			}
		}
		
		public function register_app()
		{
			$this->assertLoggedIn();
			
			$this->setTitle("Register your App");
			$this->set('area', 'app');
		  
			if ($this->args('submit'))
			{
				if (!$this->args('name'))
				{
					$errors['name'] = 'You must enter a name.';
					$errorfields['name'] = 'error';
				}
				if (!$this->args('app_url'))
				{
					$errors['app_url'] = 'You must enter an app URL.';
					$errorfields['app_url'] = 'error';
				}
				
				if (empty($errors))
				{
				
					$app = new OAuthConsumer();
					$app->set('name', $this->args('name'));
					$app->set('app_url', $this->args('app_url'));
					$app->set('user_id', User::$me->id);
					$app->set('consumer_key', MyOAuthProvider::generateToken());
					$app->set('consumer_secret', MyOAuthProvider::generateToken());
					$app->set('active', 1);
					$app->save();
					
					Activity::log("registered a new app named " . $app->getLink() . ".");
				
					$this->forwardToUrl($app->getUrl());
				}
				else
				{
					$this->set('errors', $errors);
					$this->set('errorfields', $errorfields);
				}
			}				
		}
		
		public function edit_app()
		{
			$this->assertLoggedIn();
			$this->set('area', 'app');
		  
			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to edit this app.");

				$this->set('app', $app);
				$this->setTitle('Edit App - ' . $app->getName());

				if ($this->args('submit'))
				{
					if (!$this->args('name'))
					{
						$errors['name'] = "You must enter a name.";
						$errorfields['name'] = 'error';
					}
					
					if (!$this->args('app_url'))
					{
						$errors['app_url'] = "You must enter a url for the app.";
						$errorfields['app_url'] = 'error';
					}
					
					if (empty($errors))
					{
						$app->set('name', $this->args('name'));
						$app->set('app_url', $this->args('app_url'));
						$app->save();
					
						Activity::log("edited the app named " . $app->getLink() . ".");

						$this->forwardToUrl($app->getUrl());
					}
					else
					{
						$this->set('errors', $errors);
						$this->set('errorfields', $errorfields);
						$this->set('error', "There was an error editing your app.");
					}
				}				
			}
			catch (Exception $e)
			{
				$this->setTitle('Edit App - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}
		
		public function delete_app()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to delete this app.");

				$this->set('app', $app);
				$this->setTitle('Delete App - ' . $app->getName());

				if ($this->args('submit'))
				{
					Activity::log("deleted the app named <strong>" . $app->getName() . "</strong>.");

					$app->delete();
					
					$this->forwardToUrl("/apps");
				}				
			}
			catch (Exception $e)
			{
				$this->setTitle('Delete App - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}
		
		public function view_app()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

			try
			{
				$app = new OAuthConsumer($this->args('app_id'));
				if (!$app->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to view this app.");

				$this->setTitle("View App - " . $app->getName());

				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->setTitle('View App - Error');
				$this->set('megaerror', $e->getMessage());
			}						
		}
		
		public function authorize_app()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

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

				$this->setTitle("Authorize App - " . $app->getName());

				//okay, save it!
				$token->set('user_id', User::$me->id);
				$token->set('verifier', mt_rand(0, 99999));
				$token->save();
				
				Activity::log("installed the app named " . $app->getLink() . ".");

				$this->set('token', $token);
				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->setTitle('Authorize App - Error');
				$this->set('megaerror', $e->getMessage());
			}	
		}
		
		//deletes an access token from an app.
		public function revoke_app()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

			try
			{
				$token = OAuthToken::findByKey($this->args('token'));
				if (!$token->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $token->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to delete this app.");

				$app = $token->getConsumer();
				$this->setTitle('Revoke App Permissions - ' . $app->getName());

				$this->set('token', $token);
				$this->set('app', $app);

				if ($this->args('submit'))
				{
					Activity::log("removed the app named " . $app->getLink() . ".");

					$token->delete();
					$this->forwardToUrl("/apps");
				}				
			}
			catch (Exception $e)
			{
				$this->setTitle('Error');
				$this->set('megaerror', $e->getMessage());
			}				
		}
	}
?>
