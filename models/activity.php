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

class Activity extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "activities");
	}

	public static function getStream(User $user)
	{
		$sql = "SELECT id
				FROM activities
				WHERE user_id = ?
				ORDER BY id DESC
			";

		$stream = new Collection($sql, array($user->id));
		$stream->bindType('id', 'Activity');

		return $stream;
	}

	public static function log($activity, User $user = null)
	{
		if ($user === null)
			$user = User::$me;

		$a = new Activity();
		$a->set('user_id', $user->id);
		$a->set('action_date', date("Y-m-d H:i:s"));
		$a->set('activity', $activity);
		$a->save();

		return $a;
	}
}