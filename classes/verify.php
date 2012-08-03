<?
	class Verify
	{
		public static function username($username, &$reason)
		{
			if (!preg_match("/^[-_a-zA-Z0-9]*$/", $username))
				$reason = "Your username must contain only letters and numbers.";
			else if (strlen($username) < 3)
				$reason = "Your username must be at least 3 letters long.";
			else if (strlen($username) > 32)
				$reason = "Your username cannot be longer than 32 letters long.";
			//check to see if its taken
			else if (User::byUsername($username)->isHydrated())
				$reason = "That username is already taken.";
			else
				return true;
				
			return false;
		}

		public static function email($email)
		{
			return eregi("^[_a-z0-9-]+((\+)?(\.)?[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email);
		}
	
	}
?>
