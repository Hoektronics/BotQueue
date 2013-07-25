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
        $this->set('requesting', OAuthToken::getRequestTokensByIP()->getAll());
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

				$this->setTitle("View App - " . $app->getName());

				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->setTitle('View App - Error');
				$this->set('megaerror', $e->getMessage());
			}						
		}
		
		public function _createAuthorizationForm($token, $app)
		{
			$form = new Form();
			$form->action = "/app/authorize";
			$form->submitText = "Approve App";
			
			$form->add(new HiddenField(array(
			 'name' => 'oauth_token',
			 'value' => $this->args('oauth_token')
			)));
			
			$form->add(new HiddenField(array(
			 'name' => 'verifier',
			 'value' => $token->get('verifier')
			)));
			
			$form->add(new TextField(array(
				'name' => 'name',
				'label' => 'Name',
				'help' => 'A nickname for this instance of ' . $app->getName() . " such as the name the computer its running on or the machine its intended to control.",
				'required' => true,
				'value' => $app->getName(),
			)));
			
			return $form;
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

				//okay, associate it with our user.
				if (!$token->get('user_id') )
				{
				  $token->set('user_id', User::$me->id);
  				$token->save();
				}
				else if ($token->get('user_id') != User::$me->id)
				  throw new Exception("Another user has already claimed this token.");
				
				//do we have a verifier yet?  
				if (!$token->get('verifier'))
				{
				  $token->set('verifier', sha1(mt_rand()));
  				$token->save();
				}

        $form = $this->_createAuthorizationForm($token, $app);

        //did they submit it?
				if ($form->checkSubmitAndValidate($this->args()))
				{
				  if ($form->data('verifier') != $token->get('verifier'))
				    throw new Exception("Invalid verifier.");
				    
  				$token->set('name', $form->data('name'));
  				$token->set('verified', 1);
  				$token->save();

  				Activity::log("installed the app named " . $app->getLink() . ".");
  				
  				$this->forwardToUrl("/");
				}

        $this->set('approve_form', $form);
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
		public function edit_token()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

			try
			{
				$token = new OAuthToken($this->args('id'));
				if (!$token->isHydrated())
					throw new Exception("This app does not exist.");
				if (!User::$me->isAdmin() && $token->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to edit this token.");

				$app = $token->getConsumer();
				$this->setTitle('Edit App Token - ' . $app->getName());
        $form = $this->_editAccessTokenForm($token);
        
        //did they submit it?
				if ($form->checkSubmitAndValidate($this->args()))
				{
  				$token->set('name', $form->data('name'));
  				$token->save();

  				$this->forwardToUrl("/apps");
				}
				
        $this->set('bots', $token->getBots()->getAll());
        $this->set('form', $form);
				$this->set('token', $token);
				$this->set('app', $app);
			}
			catch (Exception $e)
			{
				$this->setTitle('Error');
				$this->set('megaerror', $e->getMessage());
			}				
		}
		
		public function _editAccessTokenForm($token)
		{
			$form = new Form();
			$form->action = $token->getUrl() . "/edit";
			$form->submitText = "Manage App Token";

			$form->add(new TextField(array(
				'name' => 'name',
				'label' => 'Name',
				'help' => 'A nickname for this token such as the name the computer its running on or the machine its intended to control.',
				'required' => true,
				'value' => $token->getName(),
			)));

			return $form;
		}
		
		//deletes an access token from an app.
		public function revoke_app()
		{
			$this->assertLoggedIn();
		  $this->set('area', 'app');

			try
			{
				$token = new OAuthToken($this->args('id'));
				if (!$token->isHydrated())
					throw new Exception("This app does not exist.");
				if ($token->type == 2 && $token->get('user_id') != User::$me->id)
					throw new Exception("You are not authorized to delete this app.");

				$app = $token->getConsumer();
				
				if ($token->type == 2)
				  $this->setTitle('Revoke App Permissions - ' . $app->getName());
        else
          $this->setTitle('Deny App - ' . $app->getName());
        
				$this->set('token', $token);
				$this->set('app', $app);

				if ($this->args('submit'))
				{
  				if ($token->type == 2)
					  Activity::log("removed the app named " . $app->getLink() . ".");
					else
				    Activity::log("denied the app named " . $app->getLink() . ".");

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
