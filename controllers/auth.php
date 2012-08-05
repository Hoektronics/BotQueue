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

	class AuthController extends Controller
	{
		public function login()
		{
			$this->setTitle('Log in to BotQueue');

			//did we get a redirect payload or anything?
			if ($this->args('payload'))
			{
				$payload = unserialize(base64_decode($this->args('payload')));
				if (is_array($payload) && $payload['type'] && $payload['data'])
					$_SESSION['payload'] = $payload;
			}
			
			//did we get a token?
			if ($this->args('token'))
			{
				//try to login with it.
				User::loginWithToken($this->args('token'));
				if (User::isLoggedIn())
				{
					//fully log them in.
					$data = unserialize(base64_decode($this->args('token')));
					$token = Token::byToken($data['token']);
					$token->setCookie();
					
					//to our profile.
					$this->forwardToUrl(User::$me->getUrl());
				}
			}
			
			if ($this->args('submit'))
			{
				$username = $this->args('username');
				$pass = $this->args('password');
				$rememberme = $this->args('rememberme');

				//are we good?
				if (!$username)
					$errors['username'] = "You must supply a username.";
				else if (!$pass)
					$errors['password'] = "You must supply a password.";
				else
				{
					User::login($username, $pass);
					
					if (User::isLoggedIn())
					{
						//want a cookie?
						if ($rememberme)
						{
							$token = User::$me->createToken();
							$token->setCookie();
						}
						
						Activity::log("logged in.");
												
						//send us!
						//$this->forwardToUrl("/home");
						if (Controller::isiPhone())
							$this->forwardToUrl('/');
						else
							$this->forwardToUrl(User::$me->getUrl());
					}
					else
						$errors['login'] = "We could not find that username and password combination.";
				}
				
				if (!empty($errors))
				{
					$this->setArg('username');
					$this->set('errors', $errors);
				}
			}
		}
		
		public function logout()
		{
			if (User::isLoggedIn())
			{
				Activity::log("logged out.");

				//remove our token, if we got one.
				if ($_COOKIE['token'])
				{
					$data = base64_decode($_COOKIE['token']);
					$token = Token::byToken($data['token']);
					$token->delete();
				}
			
				//unset specific variables.
			    setcookie('token', '', time()-42000, '/', SITE_HOSTNAME);
				unset($_SESSION['userid']);

				//nuke the session.
				if (isset($_COOKIE[session_name()]))
				    setcookie(session_name(), '', time()-42000, '/');

				session_unset();
				session_destroy();
				
				$this->forwardToUrl("/");
			}
		}
		
		public function forgotpass()
		{
			$this->setTitle("Retrieve Forgotten Password");
			
			if ($this->args('submit'))
			{
				$user = User::byEmail($this->args('email'));
				if ($user->isHydrated())
				{
					//give them a pass hash.
					$user->set('pass_reset_hash', sha1(mt_rand() . mt_rand() . mt_rand()));
					$user->save();
					
					$link = "http://" . SITE_HOSTNAME . $user->getUrl() . "/resetpass:" . $user->get('pass_reset_hash');
					$text = Controller::byName('email')->renderView('lost_pass', array('user' => $user, 'link' => $link));
					$html = Controller::byName('email')->renderView('lost_pass_html', array('user' => $user, 'link' => $link));

					Activity::log("forgot his/her password. :P", $user);

					Email::queue($user, "Password Reset", $text, $html);
					
					$this->set('status', "We have sent a reset password confirmation email to '" . $this->args('email') . "'.");
				}
				else
					$this->set('error', "We could not find an account with that email address.");
					
				$this->setArg('email');
			}
		}
	}
?>
