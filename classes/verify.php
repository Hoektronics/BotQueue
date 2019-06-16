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
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}

?>
