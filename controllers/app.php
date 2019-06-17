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

class AppController extends Controller
{
	public function home()
	{
		$this->set('area', 'app');

		if (User::isLoggedIn()) {
			$this->set('apps', User::$me->getMyApps()->getAll());
			$this->set('authorized', User::$me->getAuthorizedApps()->getAll());
			$this->set('requesting', OAuthToken::getRequestTokensByIP()->getAll());
		}
	}

	public function register_app()
	{
		try {
			$this->assertLoggedIn();

			$this->setTitle("Register your App");
			$this->set('area', 'app');

			$app = new OAuthConsumer();
			$form = $this->_AppConsumerForm($app);
			$form->action = "/app/register";
			$form->submitText = "Register App";

			$this->set('form', $form);

			if ($form->checkSubmitAndValidate($this->args())) {
				$app = OAuthConsumer::create($this->args('name'), $this->args('app_url'));

				Activity::log("registered a new app named " . $app->getLink() . ".");

				$this->forwardToUrl($app->getUrl());
			}
		} catch (Exception $e) {
			$this->setTitle('Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function edit_app()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$consumer = new OAuthConsumer($this->args('app_id'));
			if (!$consumer->isHydrated())
				throw new Exception("This app does not exist.");
			if (!User::$me->isAdmin() && $consumer->get('user_id') != User::$me->id)
				throw new Exception("You are not authorized to edit this app.");

			$this->setTitle('Edit App - ' . $consumer->getName());
			$form = $this->_AppConsumerForm($consumer);
			$form->action = $consumer->getUrl() . "/edit";
			$form->submitText = "Save";

			//did they submit it?
			if ($form->checkSubmitAndValidate($this->args())) {
				$consumer->set('name', $form->data('name'));
				$consumer->set('app_url', $form->data('url'));
				$consumer->save();

				$this->forwardToUrl("/app:" . $consumer->id);
			}

			$this->set('apps', $consumer->getApps()->getAll());
			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function delete_app()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$app = new OAuthConsumer($this->args('app_id'));
			if (!$app->isHydrated())
				throw new Exception("This app does not exist.");
			if (!User::$me->isAdmin() && $app->get('user_id') != User::$me->id)
				throw new Exception("You are not authorized to delete this app.");

			$this->set('app', $app);
			$this->setTitle('Delete App - ' . $app->getName());

			if ($this->args('submit')) {
				Activity::log("deleted the app named <strong>" . $app->getName() . "</strong>.");

				$app->delete();

				$this->forwardToUrl("/apps");
			}
		} catch (Exception $e) {
			$this->setTitle('Delete App - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function view_app()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$consumer = new OAuthConsumer($this->args('app_id'));
			if (!$consumer->isHydrated())
				throw new Exception("This app does not exist.");

			$this->setTitle("View App - " . $consumer->getName());

			$this->set('apps', $consumer->getApps()->getAll());
			$this->set('consumer', $consumer);
		} catch (Exception $e) {
			$this->setTitle('View App - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $token OAuthToken
	 * @param $app OAuthConsumer
	 * @return Form
	 */
	public function _createAuthorizationForm($token, $app)
	{
		$form = new Form();
		$form->action = "/app/authorize";
		$form->submitText = "Approve App";

		$form->add(
			HiddenField::name('oauth_token')
				->value($this->args('oauth_token'))
		);

		$form->add(
			HiddenField::name('verifier')
				->value($token->get('verifier'))
		);

		$form->add(
			TextField::name('name')
				->label('Name')
				->help("A nickname for this instance of " . $app->getName() . " such as the name of the computer it's running on or the machine it's intended to control.")
				->required(true)
				->value($app->getName())
		);

		return $form;
	}

	public function authorize_app()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
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
			if (is_null($token->get('user_id'))) {
				$token->set('user_id', User::$me->id);
				$token->save();
			} else if ($token->get('user_id') != User::$me->id)
				throw new Exception("Another user has already claimed this token.");

			//do we have a verifier yet?
			if (!$token->get('verifier')) {
				$token->set('verifier', sha1(mt_rand()));
				$token->save();
			}

			$form = $this->_createAuthorizationForm($token, $app);

			//did they submit it?
			if ($form->checkSubmitAndValidate($this->args())) {
				if ($form->data('verifier') != $token->get('verifier'))
					throw new Exception("Invalid verifier.");

				$token->set('name', $form->data('name'));
				$token->set('type', OauthToken::$VERIFIED);
				$token->save();

				Activity::log("installed the app named " . $app->getLink() . ".");

				$this->forwardToUrl("/");
			}

			$this->set('approve_form', $form);
			$this->set('token', $token);
			$this->set('app', $app);
		} catch (Exception $e) {
			$this->setTitle('Authorize App - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function view_token()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$token = new OAuthToken($this->args('id'));
			if (!$token->isHydrated())
				throw new Exception("This app does not exist.");
			if (!User::$me->isHydrated() && $token->get('user_id') != User::$me->id)
				throw new Exception("You are not authorized to edit this token.");

			$this->setTitle("View App Token - " . $token->getName());

			$this->set('bots', $token->getActiveBots()->getAll());
			$this->set('token', $token);
		} catch (Exception $e) {
			$this->setTitle('View App - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	//deletes an access token from an app.
	public function edit_token()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$token = new OAuthToken($this->args('id'));
			if (!$token->isHydrated())
				throw new Exception("This app does not exist.");
			if (!User::$me->isAdmin() && $token->get('user_id') != User::$me->id)
				throw new Exception("You are not authorized to edit this token.");

			$this->setTitle('Edit App Token - ' . $token->getName());
			$form = $this->_editAccessTokenForm($token);

			//did they submit it?
			if ($form->checkSubmitAndValidate($this->args())) {
				$token->set('name', $form->data('name'));
				$token->save();

				$this->forwardToUrl("/app/token:" . $token->id);
			}

			$this->set('bots', $token->getActiveBots()->getAll());
			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $token OAuthToken
	 * @return Form
	 */
	public function _editAccessTokenForm($token)
	{
		$form = new Form();
		$form->action = $token->getUrl() . "/edit";
		$form->submitText = "Save";

		$form->add(
			TextField::name('name')
				->label('Name')
				->value($token->getName())
				->help("A nickname for this token such as the name the computer it's running on or the machine it's intended to control.")
		);

		return $form;
	}

	//deletes an access token from an app.
	public function revoke_app()
	{
		$this->assertLoggedIn();
		$this->set('area', 'app');

		try {
			$token = new OAuthToken($this->args('id'));
			if (!$token->isHydrated())
				throw new Exception("This app does not exist.");
			/** @var User $user */
			$user = new User($token->get('user_id'));
			if ($user->isHydrated() && $user->id != User::$me->id)
				throw new Exception("You are not authorized to delete this app.");

			$form = new Form();

			$field = WarningField::name('warning');
			if ($token->isVerified()) {
				$this->setTitle('Revoke App Permissions - ' . $token->getName());
				$form->submitText = "Revoke App Permissions";
				$field->value("Are you sure you want to revoke access to this app? Any apps currently using these credentials to print will be broken");
			} else {
				$this->setTitle('Deny App - ' . $token->getName());
				$form->submitText = "Deny App";
				$field->value("Are you sure you want to deny access to this app?");
			}
			$form->add($field);

			$this->set('form', $form);

			if ($form->checkSubmitAndValidate($this->args())) {
				if ($token->isVerified())
					Activity::log("removed the app named " . $token->getLink() . ".");
				else
					Activity::log("denied the app named " . $token->getLink() . ".");

				$token->delete();
				$this->forwardToUrl("/apps");
			}
		} catch (Exception $e) {
			$this->setTitle('Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $consumer OAuthConsumer
	 * @return Form
	 */
	private function _AppConsumerForm($consumer)
	{
		$form = new Form();
		$form->action = $consumer->getUrl() . "/edit";
		$form->submitText = "Save";

		$form->add(
			TextField::name('name')
				->label('Name')
				->value($consumer->getName())
				->required(true)
				->help("What do you call your app?")
		);

		$form->add(
			TextField::name('url')
				->label('App URL / Website')
				->value($consumer->get('app_url'))
				->help("Homepage with more information about your app.")
		);

		return $form;
	}
}