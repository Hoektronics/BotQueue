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

class Notification extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "notifications");
	}

	public static function getCount()
	{
		if(User::isLoggedIn()) {
			$sql = "SELECT COUNT(id) FROM notifications WHERE id > ? AND (to_user_id = ? or to_user_id IS NULL)";
			$data = array(User::$me->get('last_notification'), User::$me->id);
			return db()->getValue($sql, $data);
		} else {
			return 0;
		}
	}

	public static function getMine($all = false)
	{
		if(User::isLoggedIn()) {
			$sql = "SELECT id FROM notifications WHERE id > ? AND (to_user_id = ? or to_user_id IS NULL)";
			$data = array();
			if($all === false) {
				$data[] = User::$me->get('last_notification');
			} else {
				$data[] = 0; // Get all of them
			}
			$data[] = User::$me->id;
			$notifications = new Collection($sql, $data);
			$notifications->bindType('id', 'Notification');
			return $notifications;
		} else {
			// Return empty collection
			return Collection::none();
		}
	}
}