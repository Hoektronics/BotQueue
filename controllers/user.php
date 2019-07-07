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

class UserController extends Controller
{
	public function home()
	{
		$this->assertLoggedIn();
	}

	public function profile()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = new User();

			//did we really get someone?
			if (!$user->isHydrated())
				throw new Exception("Could not find that user.");

			//set our title.
			if ($user->isMe())
				$this->setTitle("Welcome, " . $user->getName());
			else
				$this->setTitle("About " . $user->getName());

			$this->set('user', $user);
			//$this->set('photo', $user->getProfileImage());

			//figure out our info.
			/** @var Collection $collection */
			$collection = $user->getActivityStream()->getPage(1, 25);
			$this->set('activities', $collection);
		} catch (Exception $e) {
			$this->setTitle('View User - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function activity()
	{
		$this->assertLoggedIn();

		try {
			$this->setTitle('Activity Log');

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = new User();

			//did we really get someone?
			if (!$user->isHydrated())
				throw new Exception("Could not find that user.");

			$this->set('user', $user);

			$this->setTitle('Activity Log - ' . $user->getName());

			//figure out our info.
			$collection = $user->getActivityStream();

			$this->set('activities',
				$collection->getPage(
					$this->args('page'),
					20
				)
			);
		} catch (Exception $e) {
			$this->setTitle('View User - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function edit()
	{
		$this->assertLoggedIn();

		try {
			$this->setTitle("Edit Profile");

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = User::$me;

			//are we cool?
			if (!$user->isHydrated())
				throw new Exception("Could not find that user.");
			//are we cool to edit
			else if (!$user->isMe() && !User::isAdmin())
				throw new Exception("You do not have permission to edit this user.");

			/** @var Form $form */
			$form = $this->_createProfileEditForm($user);

			//did we get a form submission?
			if ($form->checkSubmitAndValidate($this->args())) {
				if ($this->args('birthday')) {
					if (strtotime($this->args('birthday')))
						$user->set('birthday', date("Y-m-d H:i:s", strtotime($this->args('birthday'))));
					else {
						/** @var FormField $birthday_field */
						$birthday_field = $form->get('birthday');
						$birthday_field->error("We couldn't understand your birthday.  Try using MM/DD/YYY");
					}
				}

				// email change?
				if (Verify::email($this->args('email')))
					$user->set('email', $this->args('email'));
				else {
					/** @var EmailField $email_field */
					$email_field = $form->get('email');
					$email_field->error("Your email address is invalid");
				}

				// WARNING Be careful with this, it's perfect for
				// privilege elevation if done incorrectly.
				if(User::$me->isAdmin()) {
					$user->set('is_admin', $form->data('admin'));
				}

				if (!$form->hasError()) {
					if ($user->isMe())
						Activity::log("edited their profile.");
					else
						Activity::log("edited " . $this->args('username') . "'s profile.");

					$user->save();
					$this->forwardToURL($user->getUrl());
				}
			}

			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Edit User - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function changepass()
	{
		$this->assertLoggedIn();

		try {
			$this->setTitle("Edit Password");

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = User::$me;

			//are we cool?
			if (!$user->isHydrated())
				throw new Exception("Could not find that user.");
			//are we cool to edit
			if (!$user->isMe() && !User::isAdmin())
				throw new Exception("You do not have permission to edit this user.");

			$form = $this->_createChangePasswordForm();

			if ($form->checkSubmitAndValidate($this->args())) {
				if($user->get('pass_hash') != User::hashPass($this->args('current'))) {
					/** @var PasswordField $field */
					$field = $form->get('current');
					$field->error("This does not match your old password");
				}
				if ($this->args('changepass1') != $this->args('changepass2')) {
					/** @var PasswordField $field */
					$field = $form->get('changepass2');
					$field->error("The passwords did not match");
				}

				if (!$form->hasError()) {
					$user->set('pass_hash', User::hashPass($this->args('changepass1')));
					$user->save();

					$this->forwardToURL($user->getUrl());
				}
			}

			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Edit User - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function resetpass()
	{
		try {
            $this->setTitle("Reset Password");

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = User::$me;

			//are we cool?
			if (!$user->isHydrated())
				$this->set('megaerror', "Could not find that user.");

			//is that hash good?  pass it bro!
			if ($user->get('pass_reset_hash') != $this->args('hash'))
				throw new Exception("Invalid hash.  Die hacker scum.");

            $form = $this->_createResetPasswordForm();

            if ($form->checkSubmitAndValidate($this->args())) {
                if ($this->args('changepass1') != $this->args('changepass2')) {
                    /** @var PasswordField $field */
                    $field = $form->get('changepass2');
                    $field->error("The passwords did not match");
                }

                if (!$form->hasError()) {
                    $user->set('pass_hash', User::hashPass($this->args('changepass1')));
                    $user->set('pass_reset_hash', '');
                    $user->save();

                    User::createLogin($user);

                    $this->forwardToURL($user->getUrl());
                }
            }

            $this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Reset Pass - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function delete()
	{
		$this->assertLoggedIn();

		try {
			$this->setTitle("Delete User");

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				throw new Exception("Could not find that user.");

			if (!$user->isHydrated())
				throw new Exception("Could not find that user.");

			if($user->id != User::$me->id) {
				if (User::isAdmin()) {
					if ($user->get('is_admin'))
						throw new Exception("You cannot delete admins.");
				} else {
					throw new Exception("You are not an admin and cannot delete other users.");
				}
			}

			if ($this->args('submit')) {
				$user->delete();
				$this->set('status', "The user has been deleted!");
			}

			$this->set('user', $user);
		} catch (Exception $e) {
			$this->setTitle('Delete User - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function loginandregister()
	{
		$this->setTitle('Login or register a new account.');

		//did we get a redirect payload or anything?
		if ($this->args('payload')) {
			$payload = json_decode(base64_decode($this->args('payload')));
			if (is_array($payload) && $payload['type'] && $payload['data'])
				$_SESSION['payload'] = $payload;
		}

		//did we get a token?
		if ($this->args('token')) {
			//try to login with it.
			User::loginWithToken($this->args('token'));
			if (User::isLoggedIn()) {
				//fully log them in.
				$data = json_decode(base64_decode($this->args('token')));
				$token = Token::byToken($data['token']);
				$token->setCookie();

				//to our dashboard
				$this->forwardToUrl("/");
			}
		}
	}

	public function register()
	{
		$registerForm = $this->_createRegisterForm();
		$this->set('register_form', $registerForm);

		if ($registerForm->checkSubmitAndValidate($this->args())) {
			$username = $this->args('username');
			if (!Verify::username($username, $reason)) {
				/** @var FormField $field */
				$field = $registerForm->get('username');
				$field->error($reason);
			}

			$email = $this->args('email');
			$testUser = User::byEmail($email);
			if ($testUser->isHydrated()) {
				/** @var FormField $emailField */
				$emailField = $registerForm->get('email');
				$emailField->error("That email is already being used");
			}

			if ($this->args('pass1') != $this->args('pass2')) {
				/** @var FormField $field */
				$field = $registerForm->get('pass2');
				$field->error("Your passwords do not match");
			}

			if (!$registerForm->hasError()) {
				//woot!
				$user = new User();
				$user->set('username', $username);
				$user->set('email', $email);
				$user->set('pass_hash', User::hashPass($this->args('pass1')));
				$user->set('registered_on', date("Y-m-d H:i:s"));
				$user->save();

				//create a default queue for them
				$q = new Queue();
				$q->set("name", 'Default');
				$q->set("user_id", $user->id);
				$q->save();

				Activity::log("registered a new account on BotQueue.", $user);

				$text = Controller::byName('email')->renderView('new_user', array('user' => $user));
				$html = Controller::byName('email')->renderView('new_user_html', array('user' => $user));
				Email::queue($user, "Welcome to ".RR_PROJECT_NAME."!", $text, $html);

				//automatically log them in.
				$token = $user->createToken();
				$token->setCookie();

				$this->forwardToURL("/");
			}
		}
	}

	public function _createRegisterForm()
	{
		$form = new Form('register');

		$form->add(
			TextField::name('username')
				->label("Username")
				->value($this->args('username'))
				->required(true)
		);

		$form->add(
			EmailField::name('email')
				->label("Email address")
				->value($this->args('email'))
				->required(true)
		);

		$form->add(
			PasswordField::name('pass1')
				->label("Password")
				->required(true)
		);

		$form->add(
			PasswordField::name('pass2')
				->label("Password Confirmation")
				->required(true)
		);

		if(defined('GOOGLE_CAPTCHA_SECRET_KEY') && defined('GOOGLE_CAPTCHA_SITE_KEY')) {
			$form->add(
				GoogleCaptchaField::name('ignored')
			);
		}

		$tos = "By clicking on the \"Create your account\" button below, you certify that you have read and agree to our ";
		$tos .= "<a href=\"/tos\">Terms of use</a>";
		$tos .= " and ";
		$tos .= "<a href=\"/privacy\">Privacy Policy</a>.";

		$form->add(
			DisplayField::name('tos')
				->value($tos)
		);

		$form->setSubmitText("Create your account");
		$form->setSubmitClass("btn btn-success btn-large");

		return $form;
	}

	public function login()
	{
		$loginForm = $this->_createLoginForm();
		$this->set('login_form', $loginForm);

		if ($loginForm->checkSubmitAndValidate($this->args())) {
			$username = $this->args('username');
			$password = $this->args('password');
			$rememberMe = $this->args('remember_me');

			User::login($username, $password);

			if (User::isLoggedIn()) {
				//Want a cookie?
				if ($rememberMe) {
					$token = User::$me->createToken();
					$token->setCookie();
				}

				Activity::log("logged in.");

				//send us!

				$this->forwardToURL('/');
			} else {
				$this->set('error', "We could not find that username/password combination");
			}
		}
	}

	public function _createLoginForm()
	{
		$form = new Form("login");

		$form->action = "/login";

		if (!$this->args('username'))
			$username = '';
		else
			$username = $this->args('username');

		$form->add(
			HiddenField::name('action')
				->value('login')
				->required(true)
		);

		$form->add(
			TextField::name('username')
				->label('Username')
				->value($username)
				->required(true)
		);

		$form->add(
			PasswordField::name('password')
				->label('Password')
				->required(true)
		);

		$form->add(
			CheckboxField::name('remember_me')
				->label("Remember me on this computer.")
				->checked(true)
		);

		$form->setSubmitText("Sign into your account");
		$form->setSubmitClass("btn btn-primary btn-large");

		return $form;
	}

	public function draw_users()
	{
		$this->setArg('users');
	}

	/**
	 * @param User $user
	 * @return Form
	 */
	private function _createProfileEditForm($user)
	{
		$form = new Form();

		$form->add(
			DisplayField::name('username')
				->label("Username")
				->value($user->get('username'))
		);

		$form->add(
			EmailField::name('email')
				->label("Email")
				->value($user->get('email'))
				->required(true)
		);

		if(User::$me->isAdmin()) {
			$form->add(
				CheckboxField::name('admin')
					->label("Is admin?")
					->help("Is this user an admin?")
					->checked($user->isAdmin())
			);
		}

		// todo add DateField
		/**
		$form->add(
			DateField::name('birthday')
				->label("Birthday")
				->value($user->get('birthday'))
		);
		*/

		$password_page = $user->getUrl() . "/changepass";
		$form->add(
			DisplayField::name('changepass')
				->label("Change Password")
				->value("Please visit the <a href=\"$password_page\">change password</a> page.")
		);

		return $form;
	}

    /**
     * @return Form
     */
    private function _createResetPasswordForm()
    {
        $form = new Form();
        $form->submitText = "Reset password";

        $form->add(
            PasswordField::name('changepass1')
                ->label("New Password")
                ->help("Your new password")
                ->required(true)
        );

        $form->add(
            PasswordField::name('changepass2')
                ->label("Password again")
                ->help("Your new password again")
                ->required(true)
        );

        return $form;
    }

	/**
	 * @return Form
	 */
	private function _createChangePasswordForm()
	{
		$form = new Form();
		$form->submitText = "Update password";

		$form->add(
			PasswordField::name('current')
				->label("Enter Password")
				->help("Your current password")
				->required(true)
		);

		$form->add(
			PasswordField::name('changepass1')
				->label("New Password")
				->help("Your new password")
				->required(true)
		);

		$form->add(
			PasswordField::name('changepass2')
				->label("Password again")
				->help("Your new password again")
				->required(true)
		);

		return $form;
	}
}