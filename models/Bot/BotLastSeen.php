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

class BotLastSeen {
	/**
	 * @param Bot $bot
	 * @return string
	 */
	public static function getHTML($bot)
	{
		$now = time();
		$last = strtotime($bot->get('last_seen'));

		$elapsed = $now - $last;

		if ($last <= 0)
			return "never";

		$months = floor($elapsed / (60 * 60 * 24 * 30));
		$elapsed = $elapsed - ($months * 60 * 60 * 30);

		$days = floor($elapsed / (60 * 60 * 24));
		$elapsed = $elapsed - ($days * 60 * 60 * 24);

		$hours = floor($elapsed / (60 * 60));
		$elapsed = $elapsed - ($hours * 60 * 60);

		$minutes = floor($elapsed / 60);
		$seconds = $elapsed - $minutes * 60;

		if ($months)
			return "{$months} months";
		if ($days > 1)
			return "{$days} days ago";
		if ($days)
			return "{$days} day ago";
		if ($hours > 1)
			return "{$hours} hours ago";
		if ($hours)
			return "{$hours}:{$minutes}:{$seconds} ago";
		if ($minutes > 1)
			return "{$minutes} minutes ago";
		if ($minutes)
			return "{$minutes} minute ago";
		return "{$seconds}s ago";
	}
}