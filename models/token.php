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

class Token extends Model
{
    public function __construct($id = null)
    {
        parent::__construct($id, "tokens");
    }

    public static function byToken($token)
    {
        //look up the token
        $sql = "SELECT id
				FROM tokens
				WHERE hash = ?";
        $id = db()->getValue($sql, array($token));

        //send it!
        return new Token($id);
    }

    public function setCookie()
    {
        $encoded = $this->getEncodedString();

        //one year is just long enough to forget your password.
        setcookie('token', $encoded, time() + 60 * 60 * 24 * 365, "/", SITE_HOSTNAME, FORCE_SSL, true);
    }

    public function getEncodedString()
    {
        $data = array(
            'id' => $this->get('user_id'),
            'token' => $this->get('hash')
        );

        return base64_encode(json_encode($data));
    }
}