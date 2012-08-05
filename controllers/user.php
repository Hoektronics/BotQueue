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

	class UserController extends Controller
	{
		public function home()
		{
		}
		
		public function loginbox()
		{
			if (User::isLoggedIn())
				$this->set('user', User::$me);
		}
		
		public function profile()
		{
			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else
				$user = new User();

			//redirects!
			if ($_COOKIE['viewmode'] == 'iphone')
				$this->forwardToUrl($user->getiPhoneUrl());

			//did we really get someone?
			if (!$user->isHydrated())
				$this->set('megaerror', "Could not find that user.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('user', $user);
				$this->set('photo', $user->getProfileImage());

				//figure out our info.
				$collection = $user->getActivityStream();
				$this->set('activities', $collection->getRange(0, 25));
				$this->set('activity_total', $collection->count());
				
				$this->setSidebar(Controller::byName('user')->renderView('sidebar', array('user' => $user)));
			}
		}

		public function activity()
		{
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
				$this->set('megaerror', "Could not find that user.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('user', $user);

				$this->setTitle('Activity Log - ' . $user->getName());
			
				//figure out our info.
				$collection = $user->getActivityStream();
				$per_page = 25;
				$page = $collection->putWithinBounds($this->args('page'), $per_page);
			
				//all our meta stuff.
				$this->set('per_page', $per_page);
				$this->set('total', $collection->count());
				$this->set('page', $page);
				$this->set('activities', $collection->getPage($page, $per_page));
			}
		}
		
		public function profileimage()
		{
			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
			else if ($this->args('username'))
				$user = User::byUsername($this->args('username'));
			else if ($this->args('user') instanceOf User)
				$user = $this->args('user');
			else
				$user = User::$me;
				
			//are we cool?
			if (!$user->isHydrated())
				$this->set('megaerror', "Could not find that user.");
			//are we cool to edit
			else if ($user->canEdit())
			{
				$this->set('user', $user);
				$this->set('image', $user->getProfileImage());
			}
			else
				$this->set('megaerror', "You do not have permission to edit this user.");
		}
		
		public function sidebar()
		{
			if ($this->args('user'))
				$user = $this->args('user');
			else
				$user = new User($this->args('id'));

			if (!$user->isHydrated())
				die("Could not find that user.");
				
			$this->set('user', $user);
		}
		
		public function edit()
		{
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
				$this->set('megaerror', "Could not find that user.");
			//are we cool to edit
			else if ($user->isMe() || User::isAdmin())
			{
				if ($this->args('submit'))
				{
					// birthday boy?					
					if ($this->args('birthday'))
					{
						if (strtotime($this->args('birthday')))
							$user->set('birthday', date("Y-m-d H:i:s", strtotime($this->args('birthday'))));
						else
							$errors['birthday'] = "We couldn't understand your birthday.  Try using MM/DD/YYY.";
					}
					
					// email change?
					if (Verify::email($this->args('email')))
						$user->set('email', $this->args('email'));
					else
						$errors['email'] = "Your email address is invalid.";

					// password change?
					if ($this->args('changepass1') && $this->args('changepass2'))
					{
						if ($this->args('changepass1') == $this->args('changepass2'))
							$user->set('pass_hash', User::hashPass($this->args('changepass1')));
						else
							$errors['password'] = "Your passwords did not match.";
					}
					
					$user->set('first_name', stripslashes($this->args('first_name')));
					$user->set('last_name', stripslashes($this->args('last_name')));

					if (empty($errors))
					{
						if($user->isMe())
							Activity::log("edited their profile.");
						else
							Activity::log("edited " . $this->args('username') . "'s profile.");
						
						$user->save();
						$this->set('status', "Your " . $user->getLink("profile information") . " has been updated.");
					}
					else
					{
						$this->set('errors', $errors);
						$this->set('error', "Uh oh, there was an error!");
					}
				}
			
				$this->set('user', $user);
			}
			else
				$this->set('megaerror', "You do not have permission to edit this user.");
		}

		public function changepass()
		{
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
				$this->set('megaerror', "Could not find that user.");
			//are we cool to edit
			else if ($user->isMe() || User::isAdmin())
			{
				if ($this->args('submit'))
				{
					if (!$this->args('changepass1') || !$this->args('changepass2'))
						$error = "You must enter a password.";
					else if ($user->get('pass_hash') == User::hashPass($this->args('changepass1')))
						$error = "The new password must be different from your old password.";
					else if ($this->args('changepass1') != $this->args('changepass2'))
						$error = "The passwords did not match.";
					else if (strlen($this->args('changepass1')) < 8)
						$error = "The password must be at least 8 characters long.";

					if (!$error)
					{
						$user->set('pass_hash', User::hashPass($this->args('changepass1')));
						$user->set('force_password_change', 0); //pass updated.
						$user->save();
						$this->set('status', "The password has been updated.");
					}
					else
						$this->set('error', $error);
				}
			
				$this->set('user', $user);
			}
			else
				$this->set('megaerror', "You do not have permission to edit this user.");
		}
				
		public function resetpass()
		{
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
			if ($user->get('pass_reset_hash') == $this->args('hash'))
			{
				//one time use only.
				$user->set('pass_reset_hash', '');
				$user->set('force_password_change', 1);
				$user->save();
				
				User::createLogin($user);
				
				$this->forwardToUrl('/');
			}
			else
				$this->set('megaerror', "Invalid hash.  Die hacker scum.");
		}
		
		public function delete()
		{
			$this->setTitle("Delete User");

			//how do we find them?
			if ($this->args('id'))
				$user = new User($this->args('id'));
				
			//are we cool?
			if (!$user->isHydrated())
				$this->set('megaerror', "Could not find that user.");
			//are we cool to edit
			else if ($user->get('is_admin'))
				$this->set('megaerror', "You cannot delete admins.");
			else if (!User::isAdmin())
				$this->set('megaerror', "You are not an admin and cannot delete users.");
			else if ($this->args('submit'))
			{
				$user->delete();
				$this->set('status', "The user has been deleted!");
			}
			
			$this->set('user', $user);
		}
		
		public function register()
		{
			$this->setTitle('Register a new Account');
			
			if ($this->args('submit'))
			{
				//validate username
				$username = $this->args('username');
				if (!Verify::username($username, $reason))
					$errors['username'] = $reason;
									
				//validate email
				$email = $this->args('email');
				if (!Verify::email($email))
					$errors['email'] = "You must supply a valid email.";
				else
				{
					$testUser = User::byEmail($email);
					if ($testUser->isHydrated())
						$errors['email'] = "That email is already being used.";
				}
				
				//check passwords
				if ($this->args('pass1') != $this->args('pass2'))
					$errors['password'] = "Your passwords do not match.";
				else if (!strlen($this->args('pass1')))
					$errors['password'] = "You must enter a password.";
					
				//okay, we good?
				if (empty($errors))
				{
					//woot!
					$user = new User();
					$user->set('username', $username);
					$user->set('email', $email);
					$user->set('pass_hash', User::hashPass($this->args('pass1')));				
					$user->set('registered_on', date("Y-m-d H:i:s"));
					$user->save();
					
					//create them a default queue.
					$q = new Queue();
					$q->set("name", 'Default');
					$q->set("user_id", $user->id);
					$q->save();
					
					//todo: send a confirmation email.
					Activity::log("registered a new account on BotQueue.", $user);

					//todo: automatically log them in.

					$this->forwardToUrl('/');
				}
				else
				{
					$this->set('errors', $errors);
					$this->setArg('username');
					$this->setArg('email');
					$this->setArg('pass1');
					$this->setArg('pass2');
				}
			}
		}
		
		public function draw_users()
		{
			$this->setArg('users');
		}
	}
?>
