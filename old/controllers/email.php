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

class EmailController extends Controller
{
    // Automatically handles HTML version of function
    public function __call($name, $arguments) {
        if(strlen($name) < 5)
            return;
        if (substr($name, -5) === "_html") {
            $fn = substr($name, 0, -5);
            $this->$fn();
        }
    }

    public function lost_pass()
    {
        $this->setArg('user');
        $this->setArg('link');
    }

	public function new_user()
	{
		$this->setArg('user');
	}
}